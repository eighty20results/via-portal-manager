<?php
/*
License:

	Copyright 2014-2016 Eighty / 20 Results by Wicked Strong Chicks, LLC (info@eighty20results.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
class vpmDownloads {

	/** @var string - Taxonomy name (based on download manager plugin */
	private $taxonomy;

	/** @var string - Custom Post Type name */
	private $post_type;

	/**
	 * vpmDownloads constructor.
	 */
	public function __construct() {

		// Load this integration during the init event
		add_action('init', array( $this, 'init'));

		if (function_exists('__download_monitor_main')) {

			$this->taxonomy = 'dlm_download_category';
			$this->post_type = 'dlm_download';
		}
	}

	/**
	 * Check that the download manager is installed & active
	 */
	public function init() {

		// Only load if WPDM is installed & loaded
		if ( !class_exists('WPDM\\WordPressDownloadManager') && !function_exists('__download_monitor_main')) {
			if (WP_DEBUG) {
				error_log( __("Error: No supported download manager activated on this system", "vpmlang" ) );
				
			}
		}
	}

	/**
	 * Fetch or save/add a segment category for download links (category == name of segment) 
	 * 
	 * @param       string      $name           Name of the segment
	 * @param       int         $parent_id      ID for parent category
	 *
	 * @return      int                         The category ID.
	 */
	public function get_segment_category( $name, $parent_id = null ) {

		$slug = sanitize_title($name);
		$cat_id = null;

		$eName = esc_html($name);
		$exists = get_term_by('name', $eName, $this->taxonomy);

		if (WP_DEBUG) {
			error_log("WPMDL: Does {$eName} exist? " . print_r($exists, true));
		}

		$wpe_obj = null;

		if (empty($exists)) {
			$new_cat = array(
				'cat_name' => $eName,
				'cat_nicename' => $slug,
				'taxonomy' => $this->taxonomy,
			);

			if (!is_null( $parent_id ) ) {

				if (WP_DEBUG) {
					error_log("VPMDL: {$slug} has a parent category ({$parent_id})");
				}

				$new_cat['category_parent'] = $parent_id;
			}

			if ( true != ( $cat_id = wp_insert_category( $new_cat, $wpe_obj ) ) ) {

				if (WP_DEBUG) {
					error_log("VPMDL: Error inserting new category for {$slug}");
				}

				return false;
			}

		} else {
			$cat_id = $exists->term_id;
		}

		if (WP_DEBUG) {
			error_log("VPMDL: {$slug} has been added ({$cat_id})");
		}

		return $cat_id;
	}

	/**
	 * Saves the uploaded file info as a Download Monitor CPT
	 *
	 * @param string            $download_url       URL to uploaded file
	 * @param string            $download_title     Title of downloadable file
	 * @param null|string       $download_version   Version information for the downloadable file
	 * @param null|int          $post_id            Post ID for the dlm_download file
	 * @param array|int         $category_id        The Category ID to assign this download file to
	 *
	 * @return array            Two element array:  'status' => int|false (int is post ID for download),
	 *                                              'message' => null | string (string if status is false)
	 */
	public function save_upload( $download_url, $download_title, $download_version = null, $post_id = null, $category_id = null) {

		if (WP_DEBUG) {
			error_log("VPMDL: In save_upload() function");
		}

		$download = null;

		if ( ! function_exists( '__download_monitor_main') ) {

			if (WP_DEBUG) {
				error_log("VPMDL: Download Monitor is NOT active!!!");
			}

			return array( 'status' => false, 'message' => __('Error: The Download Monitor plugin is either not installed, or it is currently deactivated', 'vpmlang') );
		}
		
		if ( !is_null( $post_id ) ) {

			if (WP_DEBUG) {
				error_log("VPMDL: Checking whether download exists already...");
			}

			$download = new DLM_Download( $post_id );
		}
		
		// do we need to insert this post/download?
		if ( is_null($download) || (! is_null($download) && ! $download->exists()) ) {

			if (WP_DEBUG) {
				error_log("VPMDL: Processing new Download Monitor entry");
			}

			$url     = $download_url;
			$title   = $download_title;
			$version = $download_version;

			try {

				$download = array(
					'post_title'    => $title,
					'post_content'  => '',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_type'     => 'dlm_download',
				);

				$download_id = wp_insert_post( $download );

				if ( $download_id ) {

					// Meta
					update_post_meta( $download_id, '_featured', 'no' );
					update_post_meta( $download_id, '_members_only', 'yes' );
					update_post_meta( $download_id, '_redirect_only', 'no' );
					update_post_meta( $download_id, '_download_count', 0 );

					// assign to the correct download category
					if ( ! is_null( $category_id )) {

						if (! is_array($category_id)) {
							$category_id = array( $category_id );
						}

						$category_id = array_map( 'intval', $category_id );
						$category_id = array_unique( $category_id );

						if (WP_DEBUG) {
							error_log("VPMDL: Adding to categories: " . print_r($category_id, true));
						}

						if ( is_wp_error( $retval = wp_set_object_terms( $download_id, $category_id, 'dlm_download_category' ) ) ) {
							
							if (WP_DEBUG) {
								error_log("VPMDL: Error while adding to category/ies: " . $retval->get_error_message());
							}

							throw new Exception( sprintf( __("Error: Unable to add file to category %d", "wpmlang"), $category_id) );
						}
					}

					// File
					$file = array(
						'post_title'   => 'Download #' . $download_id . ' File Version',
						'post_content' => '',
						'post_status'  => 'publish',
						'post_author'  => get_current_user_id(),
						'post_parent'  => $download_id,
						'post_type'    => 'dlm_download_version'
					);

					$file_id = wp_insert_post( $file );

					if ( ! $file_id ) {
						throw new Exception( __( 'Error: File was not created.', 'download-monitor' ) );
					}

					// File Manager
					$file_manager = new DLM_File_Manager();

					// Meta
					update_post_meta( $file_id, '_version', $version );
					update_post_meta( $file_id, '_filesize', $file_manager->get_file_size( $url ) );
					update_post_meta( $file_id, '_files', $file_manager->json_encode_files( array( $url ) ) );

					// Hashes
					$hashes = $file_manager->get_file_hashes( $url );

					// Set hashes
					update_post_meta( $file_id, '_md5', $hashes['md5'] );
					update_post_meta( $file_id, '_sha1', $hashes['sha1'] );
					update_post_meta( $file_id, '_crc32', $hashes['crc32'] );

					// Success message
					return array( 'status' => $download_id, 'message' => null );

				} else {
					throw new Exception( __( 'Error: Download was not created.', 'download-monitor' ) );
				}

			} catch ( Exception $e ) {
				return array( 'status' => false, 'message' => $e->getMessage() );
			}

		} else {
			return array( 'status' => $download->id, 'message' => null );
		}
		
		return array('status' => false, 'message' => __('Error: Unknown error while attempting to save downloadable file', 'vpmlang'));
	}
}