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

	public function getSettingDefs() {
		return $this->settings;
	}

	public function load( $cpt_id = null ) {

		if (empty($cpt_id)) {

			global $post;
			$cpt_id = $post->ID;
		}

		foreach( $this->settings as $setting => $definition ) {

			$value = get_post_meta( $cpt_id, $setting );
			// grab post meta (settings) as array(s)
			switch( $definition['type'] ) {
				case 'array':
					
					// Explode & trim the array values
					$value = ( empty($value) ? array() : array_map('trim',explode(";",$value[0])) );

					foreach( $value as $k => $v ) {
						
						if ( ! empty($v)) {
							$value[$k] = $v;
						}
					}
					break;

				case 'int':
					$value = (empty($value) ? 0 : $value[0]);
					break;

				case 'string':
				case 'url':
					$value = (empty($value) ? '' : $value[0]);
					break;

				case 'file':
					$value = (empty($value) ? array() : $value[0]);
					break;

				default:
					$value = (empty($value) ? null : $value[0]);
			}

			$this->options[$setting] = $value;
		}

		return $this->options;
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

	/**
	 * Sanitize supplied field value(s) depending on data type
	 *
	 * @param $field - The data to sanitize
	 * @return array|int|string
	 */
	public function sanitize( $field ) {

		if ( ! is_numeric( $field ) ) {

			if ( is_array( $field ) ) {

				foreach( $field as $key => $val ) {
					$field[$key] = $this->sanitize( $val );
				}
			}

			if ( is_object( $field ) ) {

				foreach( $field as $key => $val ) {
					$field->{$key} = $this->sanitize( $val );
				}
			}

			if ( (! is_array( $field ) ) && ctype_alpha( $field ) ||
				( (! is_array( $field ) ) && strtotime( $field ) ) ||
				( (! is_array( $field ) ) && is_string( $field ) ) ) {

				$field = sanitize_text_field( $field ) ;
			}

		}
		else {

			if ( is_float( $field + 1 ) ) {

				$field = sanitize_text_field( $field );
			}

			if ( is_int( $field + 1 ) ) {

				$field = intval( $field );
			}
		}

		return $field;
	}
}