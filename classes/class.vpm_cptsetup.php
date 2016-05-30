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

class vpm_cptSetup {

	/** @var        vpm_cptSetup $instance An instance of this class (protected) */
	protected static $instance = null;

	/** @var        string $type The type of CPT being managed */
	protected $type;

	/** @var        string $cpt_name The full name of the CPT */
	protected $cpt_name;

	/** @var    vpmModel $model Instance of the Model class */
	protected $model;

	/** @var    vpmView $view Instance of the view class */
	protected $view;

	/**
	 * vpm_cptSetup constructor.
	 */
	public function __construct( $type = 'segment' ) {

		if ( is_null( self::$instance ) ) {
			self::$instance = $this;
		}

		$this->type     = $type;
		$this->cpt_name = "via_{$this->type}";
		$this->model    = new vpmModel( $this->cpt_name, $this->type );
		$this->view     = new vpmView( $this->cpt_name, $this->type );

		// Configure this class
		add_action( 'plugins_loaded', array( $this, 'init' ), 10 );
	}

	/**
	 * Magic setter method
	 *
	 * @param       string $name Name of the variable
	 * @param       mixed $value The content of the class variable
	 */
	public function __set( $name, $value ) {

		$this->{$name} = $value;
	}

	/**
	 * Magic getter method
	 *
	 * @param       string $name The name of the variable
	 *
	 * @return      mixed        The value stored in the variable $name
	 */
	public function __get( $name ) {

		return $this->{$name};
	}

	/**
	 * Magic isset() method
	 *
	 * @param       string $name The name of the variable to test
	 *
	 * @return bool
	 */
	public function __isset( $name ) {

		return ! empty( $this->{$name} );
	}

	/**
	 * Configure any/all filters/hooks
	 * This function is loaded when class is instantiated
	 */
	public function init() {

		add_action( "init", array( $this, 'load_actions' ), 10 );

	}

	/**
	 * Load all actions for this class
	 */
	public function load_actions() {

		$this->create_custom_post_type( $this->type );

		add_action( "add_meta_boxes_{$this->cpt_name}", array( $this, 'create_metabox' ) );
	}

	/**
	 * Load the metabox definition for the CPT
	 *
	 * @param   WP_Post $post - The WordPress Post object
	 */
	public function create_metabox( $post ) {

		add_meta_box(
			"via-{$this->type}-settings",
			sprintf( __( "%s Settings", "vialang" ), ucfirst( $this->type ) ),
			array( $this, "add_meta_settings" ),
			$this->cpt_name,
			'normal',
			'high'
		);

	}

	public function add_meta_settings() {

		global $post;


	}

	/**
	 * Grab an existing (or new) instance of this class
	 *
	 * @return      vpm_cptSetup    - The current instance of the class
	 */
	public static function get_instance() {

		if ( ! is_null( self::$instance ) ) {
			return self::$instance;
		}

		return new vpm_cptSetup();
	}

	/**
	 * Create/load the CPT during the early stages of when WordPress is loading into memory
	 *
	 * @param string $post_type - The Custom Post Type
	 * @param null $labels - Any labels needed/used by the CPT
	 *
	 * @return bool
	 */
	public function create_custom_post_type( $post_type = 'segment', $labels = null ) {

		$defaultSlug = get_option( "vpm_{$post_type}_slug", "vpm_{$post_type}" );

		if ( is_null( $labels ) && 'segment' === $post_type ) {
			$labels = array(
				'name'               => __( 'Segments', "vpmlang" ),
				'singular_name'      => __( 'Segment', "vpmlang" ),
				'slug'               => 'vpm_segment',
				'add_new'            => __( 'New Segments', "vpmlang" ),
				'add_new_item'       => __( 'New Segment', "vpmlang" ),
				'edit'               => __( 'Edit Segments', "vpmlang" ),
				'edit_item'          => __( 'Edit Segment', "vpmlang" ),
				'new_item'           => __( 'Add New', "vpmlang" ),
				'view'               => __( 'View Segments', "vpmlang" ),
				'view_item'          => __( 'View This Segment', "vpmlang" ),
				'search_items'       => __( 'Search Segments', "vpmlang" ),
				'not_found'          => __( 'No Segments Found', "vpmlang" ),
				'not_found_in_trash' => __( 'No Segments Found In Trash', "vpmlang" )
			);


			$labels = apply_filters( "vpm_{$post_type}_labels", $labels, $post_type );
		}

		$error = register_post_type( "vpm_{$post_type}",
			array(
				'labels'             => $labels,
				'public'             => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'publicly_queryable' => true,
				'hierarchical'       => true,
				'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author', 'excerpt' ),
				'can_export'         => true,
				'show_in_nav_menus'  => true,
				'rewrite'            => array(
					'slug'       => apply_filters( "vpm-{$post_type}-cpt-slug", $defaultSlug ),
					'with_front' => false
				),
				'has_archive'        => apply_filters( "vpm-{$post_type}-cpt-archive-slug", "vpm_{$post_type}s" )
			)
		);

		if ( ! is_wp_error( $error ) ) {
			return true;
		} else {

			if ( WP_DEBUG ) {
				error_log( 'Error creating post type: ' . $error->get_error_message() );
			}
			wp_die( $error->get_error_message() );

			return false;
		}
	}
}