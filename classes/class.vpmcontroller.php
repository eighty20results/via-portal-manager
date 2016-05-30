<?php

/**
 * Created by PhpStorm.
 * User: sjolshag
 * Date: 5/29/16
 * Time: 2:59 PM
 */
class vpmController {

	/** @var        array $cptSettings Array containing various settings/CPT functions */
	protected $cptSettings = array();

	public function __construct() {

		if (WP_DEBUG) {
			error_log("Constructing the vpmController class");
		}

		// Configure for the vpm_segments Custom Post Type
		$this->cptSettings['segments'] = new vpm_cptSetup( 'segments' );

		add_filter( "vpm_segments_settings", array( $this, 'load_segment_settings' ) );
		add_action( 'plugins_loaded', array( $this, 'controller_init' ), 11 );

		register_activation_hook( VPM_PLUGIN_FILE, array( $this, 'activation_hook' ) );
		register_deactivation_hook( VPM_PLUGIN_FILE, array( $this, 'deactivation_hook' ) );
	}

	/**
	 * Configure the controller class & establish required actions/filters
	 */
	public function controller_init() {

		add_action( "init", array( $this, "menu_settings" ) );
		add_action( "init", array( $this, "load_textdomain" ), 1 );
	}

	/**
	 * Load internationalization module
	 */
	public function load_textdomain() {

		$locale = apply_filters( "plugin_locale", get_locale(), 'vialang' );

		$mofile = "vialang-{$locale}.mo";

		$mofile_local  = dirname( __FILE__ ) . "/../languages/" . $mofile;
		$mofile_global = WP_LANG_DIR . "/via-portal-manager/" . $mofile;

		load_textdomain( "vialang", $mofile_global );
		load_textdomain( "vialang", $mofile_local );

	}

	/**
	 * Configure & load the menu entry for the VIA Portal Manager
	 */
	public function menu_settings() {

		// Default (top level) menu for the VIA portal
		$this->menu_hook = add_menu_page(
			__( "VIA Portal", "vialang" ),
			__( "VIA Portal", "vialang" ),
			apply_filters( 'via_min_management_capability', 'manage_categories' ),
			'via-portal',
			array( $this, 'default_menu', ),
			'dashicons-welcome-widgets-menus',
			6
		);

	}

	/**
	 * Create a default menu item
	 */
	public function default_menu() {

		ob_start(); ?>
		<div class="via-manager-default-admin">

		</div>
		<?php
		$html = ob_get_clean();

		echo $html;
	}

	/**
	 * Configure the settings for the Segment CPT (Custom Post Type)
	 *
	 * @param array $settings
	 *
	 * @return array        Array of settings & their field type(s).
	 */
	public function load_segment_settings( $settings = array() ) {

		/**
		 * Format of array: "setting name" => data type
		 *
		 * Valid formats:
		 *  int
		 *  string
		 *  url
		 *  array
		 *  file
		 *
		 */
		$new = array(
			'default_playback_order'  => 'int',
			'current_playback_order'  => 'int',
			'live_session_video'      => 'url',
			'facilitator_video'       => 'url',
			'coaching_guide_doc'      => 'file',
			'facilitators_guide_link' => 'file',
			'consultants_guide_link'  => 'file',
			'presentation_doc'        => 'file',
			'workbook_link'           => 'url',
			'other_assets'            => 'array',
			'thirt_party_assets'      => 'array',
		);

		// Merge (applies implicit array_unique())
		return $settings + $new;
	}

	public function activation_hook() {

		if ( WP_DEBUG ) {
			error_log( "Activating the VIA Portal Manager plugin" );
		}
	}

	public function deactivation_hook() {

		if ( WP_DEBUG ) {
			error_log( "Deactivating the VIA Portal Manager plugin" );
		}
	}

}