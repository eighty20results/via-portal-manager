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

class vpmModel {

	/** @var        array $settings Array of setting definitions for the CPT */
	protected $settings;

	/** @var        array $options  Array of actual settings for this CPT ID */
	protected $options;

	/** @var        string $cpt_name The full name of the CPT */
	protected $cpt_name;

	/** @var        string $type The type of CPT being managed */
	protected $type;

	public function __construct( $cptn, $type ) {

		$this->type     = $type;
		$this->cpt_name = $cptn;

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		$this->settings = apply_filters( "vpm_{$this->type}_settings", array() );
	}

	public function load( $cpt_id = null ) {

		if (is_null($cpt_id)) {

			global $post;
			$cpt_id = $post->ID;
		}

		foreach( $this->settings as $setting => $type ) {

			// grab post meta (settings) as array(s)
			$this->options[$setting] = get_post_meta( $cpt_id, $setting );
		}
	}

	public function save( $cpt_id, $options = null ) {

		if (!empty($options)) {
			$this->options = $options;
		}

		foreach( $this->options as $setting => $value ) {

			$tmp = get_post_meta( $cpt_id, $setting, $value );

			if ( empty($tmp) ) {
				add_post_meta( $cpt_id, $setting, $value );
			} else {
				update_post_meta( $cpt_id, $setting, $value );
			}

			if ( false === ($tmp = get_post_meta( $cpt_id, $setting )) ) {
				if (WP_DEBUG) {
					error_log("Unable to save CPT setting for {$this->cpt_name}: {$setting} => {$value}");
				}
				return false;
			}
		}

		return true;
	}
}