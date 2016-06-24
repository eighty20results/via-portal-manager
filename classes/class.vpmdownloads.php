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

	public function __construct() {

		// Load this integration during the init event
		add_action('init', array( $this, 'init'));
	}

	public function init() {

		// Only load if WPDM is installed & loaded
		if ( !class_exists('WPDM\\WordPressDownloadManager')) {
			if (WP_DEBUG) {
				error_log( __("Error: WP Download Manager is not activated on this system", "vpmlang" ) );
				
			}
		}
	}
}