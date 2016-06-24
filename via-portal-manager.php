<?php

/*
Plugin Name: VIA Portal Manager
Plugin URI: https://eighty20results.com/custom-programming/
Description: Manage Custom Post Types & Integrations for the VIA Portal
Version: 0.1
Author: Thomas Sjolshagen <thomas@eighty20results.com>
Author URI: https://eighty20results.com/thomas-sjolshagen
License: GPL2
Text Domain: e20rsequence
Domain Path: /languages
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

define( 'VPM_VERSION', '0.1' );

define( 'VPM_PLUGIN_FILE', plugin_dir_path( __FILE__ ) . 'via-portal-manager.php' );
define( 'VPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function vpm_autoloader( $class ) {

	if ( false === stripos( $class, 'vpm' ) ) {
		return;
	}

	$parts     = explode( '\\', $class );
	$base_path = plugin_dir_path( __FILE__ ) . "classes";
	$name      = strtolower( $parts[ ( count( $parts ) - 1 ) ] );
	$types     = array( '', 'model', 'view' );

	foreach ( $types as $type ) {

		if ( false !== stripos( $name, 'model' ) || false !== stripos( $name, 'view' ) ) {
			$dir = "{$base_path}/{$type}";
		} else {
			$dir = "${base_path}";
		}

		if ( file_exists( "{$dir}/class.{$name}.php" ) ) {

			require_once "{$dir}/class.{$name}.php";
		}
	}
}

spl_autoload_register( "\\vpm_autoloader" );

if ( WP_DEBUG ) {
	error_log( "Attempting to load the vpmController class" );
}

/*
if (!class_exists('\\vpmController') && empty($_GLOBAL['vpmController'])) {
	$_GLOBAL['vpmController'] = new vpmController();
}
*/

add_action( 'plugins_loaded', array( new vpmController(), 'controller_init' ), 11 );
