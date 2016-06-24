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

class vpmController
{

    /** @var        array $cptSettings Array containing various settings/CPT functions */
    protected $cptSettings = array();

    public function __construct()
    {

        if (WP_DEBUG) {
            error_log("Constructing the vpmController class");
        }

        // Configure for the vpm_segments Custom Post Type
        $this->cptSettings['sfwd-topic'] = new vpm_cptController('sfwd-topic');

        add_filter("vpm_sfwd-topic_settings", array($this, 'load_segment_settings'));
        add_action('plugins_loaded', array($this, 'controller_init'), 11);

        register_activation_hook(VPM_PLUGIN_FILE, array($this, 'activation_hook'));
        register_deactivation_hook(VPM_PLUGIN_FILE, array($this, 'deactivation_hook'));
    }

    /**
     * Configure the controller class & establish required actions/filters
     */
    public function controller_init()
    {

        add_action("init", array($this, "menu_settings"));
        add_action("init", array($this, "load_textdomain"), 1);
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin'));

        add_filter('vpm_coaching_guide_doc_filetypes', array( $this, 'document_types'));
        add_filter('vpm_facilitators_guide_link_filetypes', array( $this, 'document_types'));
        add_filter('vpm_facilitators_guide_link_filetypes', array( $this, 'document_types'));
        add_filter('vpm_consultants_guide_link_filetypes', array( $this, 'document_types'));
        add_filter('vpm_presentation_doc_filetypes', array( $this, 'presentation_types'));
    }

    /**
     * Specify the MIME Doc types to allow as "Documents"
     *
     * @param   array       $doctypes       - List of MIME file types to upload as "Documents"
     * @return  array
     */
    public function document_types( $doctypes) {

        $doctypes = array(
            'application/pdf',
            'application/msword'
        );

        return $doctypes;
    }

    /**
     * Specify the MIME doc types to allow as "Presentations"
     * @param   array       $doctypes       - List of MIME types to upload as "Presentations"
     * @return  array
     */
    public function presentation_types( $doctypes ) {

        $doctypes = array(
            'application/pdf',
            'application/vnd.ms-powerpoint'
        );

        return $doctypes;
    }

    public function enqueue_admin() {

        if (is_admin()) {

            wp_enqueue_style('vpm-admin', VPM_PLUGIN_URL . "/css/vpm-admin.css", null, VPM_VERSION);
        }
    }
    /**
     * Load internationalization module
     */
    public function load_textdomain()
    {

        $locale = apply_filters("plugin_locale", get_locale(), 'vialang');

        $mofile = "vialang-{$locale}.mo";

        $mofile_local = dirname(__FILE__) . "/../languages/" . $mofile;
        $mofile_global = WP_LANG_DIR . "/via-portal-manager/" . $mofile;

        load_textdomain("vialang", $mofile_global);
        load_textdomain("vialang", $mofile_local);

    }

    /**
     * Configure & load the menu entry for the VIA Portal Manager
     */
    public function menu_settings()
    {

        /*
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
        */
    }

    /**
     * Create a default menu item
     */
    public function default_menu()
    {

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
    public function load_segment_settings($settings = array())
    {

        if (WP_DEBUG) {
            error_log("Loading the setting name(s) for the segment CPT");
        }

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
            'default_playback_order' => array('type' => 'int', 'label' => __("Playback order", "vpmlang")),
            'current_playback_order' => array(
                'type' => 'int',
                'label' => __("Current playback order", "vpmlang"),
                'disabled' => true
            ),
            'live_session_video' => array('type' => 'url', 'label' => __("Session video", "vpmlang")),
            'facilitator_video' => array('type' => 'url', 'label' => __("Facilitator video", "vpmlang")),
            'coaching_guide_doc' => array(
                'type' => 'file',
                'filetype' => implode(
                    ",",
                    apply_filters( 'vpm_coaching_guide_doc_filetypes', array( 'application/pdf', 'application/msword') )
                ),
                'label' => __("Coaching guide", "vpmlang")
            ),
            'facilitators_guide_link' => array(
                'type' => 'file',
                'filetype' => implode(
                    ",",
                    apply_filters( 'vpm_facilitators_guide_link_filetypes', array( 'application/pdf', 'application/msword' ) )
                ),
                'label' => __("Facilitator guide", "vpmlang"),
            ),
            'consultants_guide_link' => array(
                'type' => 'file',
                'filetype' => implode(
                    ",",
                    apply_filters( 'vpm_consultants_guide_link_filetypes', array( 'application/pdf', 'application/msword' ) )
                ),
                'label' => __("Consultant guide", "vpmlang")
            ),
            'presentation_doc' => array(
                'type' => 'file',
                'filetype' => implode(
                    ",",
                    apply_filters( 'vpm_presentation_doc_filetypes', array( 'application/pdf', 'application/vnd.ms-powerpoint' ) )
                ),
                'label' => __("Presentation", "vpmlang")
            ),
            'workbook_link' => array('type' => 'url', 'label' => __("Workbook", "vpmlang")),
            'other_assets' => array('type' => 'array', 'label' => __("Other assets (link)", "vpmlang")),
            'third_party_assets' => array('type' => 'array', 'label' => __("3rd party assets (link)", "vpmlang"))
        );

        $new_settings = $settings + $new;

        // Merge (applies implicit array_unique())
        return $new_settings;
    }

    public function activation_hook()
    {

        if (WP_DEBUG) {
            error_log("Activating the VIA Portal Manager plugin");
        }
    }

    public function deactivation_hook()
    {

        if (WP_DEBUG) {
            error_log("Deactivating the VIA Portal Manager plugin");
        }
    }

}