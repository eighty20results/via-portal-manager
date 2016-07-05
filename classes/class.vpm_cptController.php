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

class vpm_cptController
{
    /** @var        vpm_cptController $instance An instance of this class (protected) */
    protected static $instance = null;

    /** @var        string $type The type of CPT being managed */
    private $type;

    /** @var        string $cpt_name The full name of the CPT */
    private $cpt_name;

    /** @var    vpmModel $model Instance of the Model class */
    private $model;

    /** @var    vpmView $view Instance of the view class */
    private $view;

    /**
     * vpm_cptController constructor.
     * @param string $type Type oF CPT
     */
    public function __construct($type = 'segment')
    {
        if (is_null(self::$instance)) {
            self::$instance = $this;
        }

        $this->type = $type;

        if (WP_DEBUG) {
            error_log("Constructing CTP for {$this->type} or {$type}");
        }

        // $this->cpt_name = "vpm_{$this->type}";
        $this->cpt_name = $this->type;
        $this->model = new vpmModel($this->cpt_name, $this->type);
        $this->view = new vpmView($this->cpt_name, $this->type);

        // Configure this class
        add_action('plugins_loaded', array($this, 'init'), 10);
    }

    /**
     * Magic setter method
     *
     * @param       string $name Name of the variable
     * @param       mixed $value The content of the class variable
     */
    public function __set($name, $value)
    {

        $this->{$name} = $value;
    }

    /**
     * Magic getter method
     *
     * @param       string $name The name of the variable
     *
     * @return      mixed        The value stored in the variable $name
     */
    public function __get($name)
    {

        return $this->{$name};
    }

    /**
     * Magic isset() method
     *
     * @param       string $name The name of the variable to test
     *
     * @return bool
     */
    public function __isset($name)
    {

        return !empty($this->{$name});
    }

    /**
     * Configure any/all filters/hooks
     * This function is loaded when class is instantiated
     */
    public function init()
    {
        $this->load_actions();
    }

    /**
     * Load all actions for this class
     */
    public function load_actions()
    {
        if (WP_DEBUG) {
            error_log("Processing actions for {$this->cpt_name}");
        }
        // $this->create_custom_post_type($this->type);
        // add_action('init', array($this, 'create_custom_post_type'));
        add_action('post_edit_form_tag', array( $this->view, 'update_edit_form') );
        add_action('save_post', array($this, 'save_settings'), 10, 2);
        add_action("add_meta_boxes_{$this->cpt_name}", array($this, 'create_metabox'));

        add_action('admin_notices', array( $this->view, 'show_admin_errors') );
        add_action('admin_footer', array( $this, 'clear_admin_errors'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin'));
    }

    /**
     * Clear any previously displayed error messages
     */
    public function clear_admin_errors() {

        update_option('vpm_admin_errors', array());
    }

    /**
     * Load admin side Javascript & localization (messages).
     */
    public function enqueue_admin()
    {
        if (is_admin()) {

            wp_register_script('vpm_cpt_management', VPM_PLUGIN_URL . "js/vpm-cpt-management.js", array('jquery'), VPM_VERSION);
            wp_localize_script('vpm_cpt_management', 'vpmm',
                array(
                    'messages' => array(
                        'empty_input' => __("Error: Unable to add an empty value to the resource list", 'vpmlang'),
                        'no_http' => __("Error: Please provide valid link, including 'http://' or 'https://'.", 'vpmlang'),
                        'no_file_selected' => __("No file selected", "vpmlang"),
                    )
                )
            );
            wp_enqueue_script('vpm_cpt_management');
        }
    }

    /**
     * Save action for this Custom Post Type
     * @param        int $post_id ID of post being saved
     * @param        WP_Post $post Post object being saved
     * @return        int|void|boolean
     */
    public function save_settings($post_id, WP_Post $post = null)
    {
        /** @var        string      $naction        Valid request variable name */
        /** @var        string      $nonce        Valid request variable name */
        $naction = str_replace("-", '_', "{$this->type}_savesettings");
        $nonce = str_replace('-', '_', "{$this->type}_nonce");

        /** Make sure there's a defined post type (so we know what to process) */
        if (!isset($post->post_type)) {
            if (WP_DEBUG) {
                error_log("VPM: No valid post_type defined");
            }

            return;
        }

        /** Don't save settings for auto-saves */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            if (WP_DEBUG) {
                error_log("VPM: Exit during auto-save");
            }

            return $post_id;
        }

        /** Don't save a revision */
        if (wp_is_post_revision($post_id) !== false) {
            if (WP_DEBUG) {
                error_log("VPM: Exit while attempting to save a revision");
            }

            return $post_id;
        }

        /** Don't try to save when placing this post into the trash */
        if ('trash' == get_post_status($post_id)) {
            if (WP_DEBUG) {
                error_log("VPM: Not updating for trash bucket victims");
            }

            return $post_id;
        }

        /** Make sure we're authorized to save on this request */
        if (WP_DEBUG) {
            error_log("VPM: Testing nonce for: {$nonce}, {$naction}");
        }

        if ( ! isset( $_POST[$nonce]) || ! wp_verify_nonce( $_POST[$nonce], $naction) ) {

            if (WP_DEBUG) {
                error_log("VPM: Failing during NONCE check");
            }

            return $post->ID;
        }

        /** Make sure the user is allowed to save/update this post type */
        if (!current_user_can('edit_post', $post->ID)) {

            if (WP_DEBUG) {
                error_log("VPM: User not permitted to edit posts");
            }

            return $post->ID;
        }

        /** Make sure we're attempting to save the current CPT type */
        if ($post->post_type !== $this->cpt_name) {

            if (WP_DEBUG) {
                error_log("VPM: Incorrect post type: {$post->post_type} vs {$this->cpt_name}");
            }

            return $post->ID;
        }


        if (WP_DEBUG) {
            error_log("Request: " . print_r($_REQUEST, true));
            // error_log("File list: " . print_r($_FILES, true));
        }

        $errors = array();

        $required_settings = apply_filters('vpm_required_sequence_settings', array(
                'default_playback_order',
                'live_session_video',
                'workbook_link',
            )
        );

        $required_files = apply_filters('vpm_required_sequence_files', array(
                'coaching_guide_doc',
                'facilitators_guide_link',
                'presentation_doc',
            )
        );

        $setting_defs = apply_filters("vpm_sfwd-topic_settings", array());

        // Extract all meta values we'll use
        preg_match_all("/vpm-(int|url|file|array)-([a-z|\-|_]*)/", implode(',', array_keys($_REQUEST)), $vpm_setting_matches);

        if (WP_DEBUG) {
            error_log("VPM: Setting array: " . print_r($vpm_setting_matches, true));
        }

        foreach($vpm_setting_matches[0] as $pvar_name ) {

            $new_val = isset($_REQUEST[$pvar_name]) ? $this->model->sanitize($_REQUEST[$pvar_name]) : null;
            $pvn_array = explode('-', $pvar_name);

            if (WP_DEBUG) {
                error_log("VPM: Processing request key {$pvar_name}: {$new_val}");
            }

            // grab the last element (the actual post meta key)
            $var_name = $pvn_array[(count($pvn_array) - 1)];

            if ( !empty($new_val) && false !== strpos($pvar_name, 'vpm-file-delete-') ) {

                if (WP_DEBUG) {
                    error_log("VPM: User requested removal of file: {$new_val}");
                }

                // $new_val contains the setting name in this case.
                $file = get_post_meta( $post_id, $new_val, true);

                if (!empty($file)) {

                    if (WP_DEBUG) {
                        error_log("VPM: Attempting to remove/unlink: " . $file['file']);
                    }

                    if ( false === unlink( $file['file'])) {
                        $errors[] = sprintf(__("ERROR: Could not delete %s", "vpmlang"), basename($file['file']));
                    }

                    if (WP_DEBUG) {
                        error_log("VPM: Attempting to clean up post meta for " . basename( $file['file']));
                    }

                    if (false === delete_post_meta($post_id, $var_name ) ) {
                        $errors[] = sprintf( __("ERROR: Unable to remove settings for %s", "vpmlang"), basename($file['file']));
                    }
                } else {
                    $error[] = sprintf( __("WARNING: No settings found for %s. Previously deleted?", "vpmlang"), $setting_defs[$pvar_name]['label']);
                }
                
            } elseif (is_null($new_val) && !in_array( $var_name, $required_settings )) {
            
                $errors[] = __("ERROR: Must specify a value for the required {$var_name} setting!", "vpmlang");
            
            } elseif ( ! is_null($new_val) && false === strpos($pvar_name, 'vpm-file-delete-') ) {

                if (WP_DEBUG) {
                    error_log("Updating post meta for {$post_id}, key: {$var_name} to {$new_val}.");
                }

                $array_exists = false;
                $old_pvar = $var_name;

                // Check if we're processing the current playback order (TODO: Update the menu order too! )
                if ( false !== strpos( $pvar_name, 'current_playback_order') && empty($new_val) ) {

	                if (WP_DEBUG) {
		                error_log("current_playback order is empty, so looking up the default order");
	                }

	                $new_val = get_post_meta( $post_id, 'default_playback_order', true);

	                if ( empty( $new_val ) ) {
		                $new_val = $this->model->sanitize( $_REQUEST["vpm-int-default_playback_order"]);
	                }
                }


                // check if we've got the hidden (select) for an array
                if ( false !== strpos( $pvar_name, "vpm-array-hidden-{$var_name}" ) ) {

	                if (WP_DEBUG) {
		                error_log("VPM: Processing hidden array field");
	                }

                    $array_exists = get_post_meta( $post_id, $var_name, true);
                    // $pvar_name = "vpm-array-{$var_name}";

                    if ( !empty($array_exists) && ( empty( $new_val ) ) ) {

                        if (WP_DEBUG) {
                            error_log("Have a saved value and an empty 'new' one...: " . print_r($array_exists, true) );
                        }

                        // saving the existing value.
                        $new_val = $array_exists;
                    }
                }

                $pvar_name = $old_pvar;
                update_post_meta($post_id, $var_name, $new_val );

                if ( ( false === ( $c_val = get_post_meta( $post_id, $var_name, true ) ) ) || ( $c_val != $new_val ) ){
                    $errors[] = __("ERROR: Unable to update setting {$setting_defs[$var_name]['label']} to {$new_val}", "vpmlang");
                }
            }
        }

        $errors = array_unique( array_merge( $errors, $this->check_required( $required_settings, $vpm_setting_matches[1], $post_id ) ) );

        // extract all VPM related file names
        preg_match_all( '/vpm-file-([a-z|_]*)/', implode(',', array_keys($_FILES)), $vpm_file_matches );

        // make sure we've got files to add/upload
        if (empty($vpm_file_matches[0])) {
            $errors[] = sprintf( __("Error: None of the required files were added: %s", "vpmlang"), implode(", ", $required_files ) );
        } else {

            add_filter('upload_dir', array( $this, 'set_upload_dir' ) );

/*            if (WP_DEBUG) {
                error_log("VPM: File setting array: " . print_r($vpm_file_matches, true));
            }
*/
            foreach ($vpm_file_matches[0] as $key => $file_setting ) {

                if (WP_DEBUG) {
                    error_log("VPM: Processing _FILES field '{$file_setting}'");
                    error_log("VPM: Checking file key ({$key}) for post {$post_id}: {$vpm_file_matches[1][$key]}'");
                }

                $is_loaded = get_post_meta($post_id, $vpm_file_matches[1][$key], true);

                if (WP_DEBUG) {
                    error_log( "VPM: Found pre-existing entry: " . print_r( (empty($is_loaded) ? 'Nope' : $is_loaded), true ) );
                }

                if ( ! empty($_FILES[$file_setting]['name']) ) {

                    if (WP_DEBUG) {
                        error_log("VPM: Processing {$_FILES[$file_setting]['name']}");
                    }

                    $supported_types = apply_filters(
                        "vpm_{$vpm_file_matches[1][$key]}_filetypes",
                        array( 'application/pdf', 'application/msword', 'application/vnd.ms-powerpoint' )
                    );

                    // Get the file type of the upload
                    $arr_file_type = wp_check_filetype(basename($_FILES[$file_setting]['name']));
                    $uploaded_type = $arr_file_type['type'];

                    if (in_array($uploaded_type, $supported_types)) {

                        $upload = wp_upload_bits($_FILES[$file_setting]['name'], null, file_get_contents($_FILES[$file_setting]['tmp_name']));

                        if (WP_DEBUG) {
                            error_log("VPM: Upload result: " . print_r($upload, true));
                        }

                        if (isset($upload['error']) && !empty($upload['error'])) {

                            $errors[] = sprintf(__("ERROR: Unable to upload %s. Message is '%s'", "vpmlang"), $_FILES[$file_setting]['name'], $upload['error']);
                        } else {

                            if (WP_DEBUG) {
                                error_log("VPM: Saving file meta for {$setting_defs[$vpm_file_matches[1][$key]]['label']}");
                            }

                            // add_post_meta($post_id, $vpm_file_matches[1][$key], $upload);
                            update_post_meta($post_id, $vpm_file_matches[1][$key], $upload);
                        }
                    } else {
                        $errors[] = sprintf(
                            __("ERROR: %s is not a supported file type to upload for '%s'", "vpmlang" ),
                            $_FILES[$file_setting]['name'],
                            $setting_defs[$vpm_file_matches[1][$key]]['label']
                        );
                    }
                }
                elseif (empty($_FILES[$file_setting]['name']) && in_array($vpm_file_matches[1][$key], $required_files && empty($is_loaded))) {

                    // File not chosen in input, it's a required file & we don't have it saved anywhere.
                    $errors[] = sprintf(
                        __("ERROR: Required file for the '%s' is missing", "vpmlang"),
                        $setting_defs[$vpm_file_matches[1][$key]]['label']
                    );
                }
            }

            $errors = array_unique( array_merge( $errors, $this->check_required( $required_files, $vpm_file_matches[0], $post_id ) ) );

            // undo the custom upload path
            remove_filter('upload_dir', array( $this, 'set_upload_dir' ) );
        }

        if (! empty($errors)) {
            
            update_option('vpm_admin_errors', $errors);
            return;
        }
    }

    /**
     * Check the status of the required vs returned fields for any or a specific post_id.
     *
     * @param       array           $required       list of fields that are required
     * @param       array           $returned       List of returned values from save operation
     * @param       integer|null    $post_id        ID of post to check for.
     *
     * @return      array           Array of error message (all fields that failed validation).
     */
    public function check_required( $required, $returned, $post_id = null ) {

        $errors = array();
        $setting_defs = apply_filters("vpm_sfwd-topic_settings", array());

        foreach( $required as $field ) {

            if (! in_array( $field, $returned )) {

                if (false === ($oldval = get_post_meta( $post_id, $field, true)) || empty($oldval) ) {
                    $errors[] = sprintf(__("ERROR: '<strong>%s</strong>' is a required field and did not contain a value you tried to save data", "vpmlang"), $setting_defs[$field]['label']);
                }
            }
        }

        return $errors;
    }

    /**
     * Configure the upload path for VPM docs.
     *
     * @param       array       $upload
     *
     * @return      array       Updated $upload array (settings for upload paths).
     */
    public function set_upload_dir( $upload ) {

        $post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : null;

        if (! is_null($post_id)) {

            $parent = get_post($post_id)->post_parent;

            if ($this->cpt_name === get_post_type($post_id) || $this->cpt_name === get_post_type($parent)) {
                $upload['subdir'] = "/vpm-docs" . $upload['subdir'];
            }

            $upload['path'] = $upload['basedir'] . $upload['subdir'];
            $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        }

        return $upload;
    }

    /**
     * Configure the CPT metabox (for settings, etc)
     */
    public function create_metabox()
    {
        if (WP_DEBUG) {
            error_log("Loading the metabox for {$this->type} and {$this->cpt_name}");
        }

        add_meta_box(
            "via-{$this->type}-settings",
            sprintf(__("Settings", "vialang"), ucfirst($this->type)),
            array($this, "add_meta_settings"),
            $this->cpt_name,
            'normal',
            'high'
        );

    }

    public function add_meta_settings($post)
    {
        $options = $this->model->load($post->ID);
        $defs = $this->model->getSettingDefs();

        foreach ($defs as $name => $conf) {
            if (!has_action("vpm_show_setting_{$conf['type']}")) {

                if (WP_DEBUG) {
                    error_log("Adding action: vpm_show_setting_{$conf['type']}");
                }

                add_action("vpm_show_{$this->type}_setting_{$conf['type']}", array($this->view, "showSetting_{$conf['type']}"), 10, 3);
            }
        }

        if (WP_DEBUG) {
            error_log("CPT Settings for {$this->cpt_name}: " . print_r($options, true));
        }

        echo $this->view->showMetabox($options, $defs);
    }

    /**
     * Grab an existing (or new) instance of this class
     *
     * @return      vpm_cptController    - The current instance of the class
     */
    public static function get_instance()
    {

        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        // return new vpm_cptController();
    }

    /**
     * Create/load the CPT during the early stages of when WordPress is loading into memory
     *
     * @param string $post_type - The Custom Post Type
     * @param null $labels - Any labels needed/used by the CPT
     *
     * @return bool
     */
    public function create_custom_post_type($post_type = 'segment', $labels = null)
    {
        if (empty($post_type)) {
            $post_type = $this->type;
        }

        if (WP_DEBUG) {
            error_log("Attempting to define the {$post_type} Custom Post Type");
        }

        $defaultSlug = get_option("vpm_{$post_type}_slug", "vpm_{$post_type}");

        if (is_null($labels) && 'segment' === $post_type) {
            $labels = array(
                'name' => __('Segments', "vpmlang"),
                'singular_name' => __('Segment', "vpmlang"),
                'slug' => 'vpm_segment',
                'add_new' => __('New Segments', "vpmlang"),
                'add_new_item' => __('New Segment', "vpmlang"),
                'edit' => __('Edit Segments', "vpmlang"),
                'edit_item' => __('Edit Segment', "vpmlang"),
                'new_item' => __('Add New', "vpmlang"),
                'view' => __('View Segments', "vpmlang"),
                'view_item' => __('View This Segment', "vpmlang"),
                'search_items' => __('Search Segments', "vpmlang"),
                'not_found' => __('No Segments Found', "vpmlang"),
                'not_found_in_trash' => __('No Segments Found In Trash', "vpmlang")
            );
        }

        $labels = apply_filters("vpm_{$post_type}_labels", $labels, $post_type);

        $error = register_post_type("vpm_{$post_type}",
            array(
                'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_position' => 110,
                'menu_icon' => 'dashicons-welcome-widgets-menus',
                'publicly_queryable' => true,
                'hierarchical' => true,
                'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'author', 'excerpt', 'revisions', 'page-attributes', 'post-formats'),
                'can_export' => true,
                'show_in_nav_menus' => true,
                'rewrite' => array(
                    'slug' => apply_filters("vpm-{$post_type}-cpt-slug", $defaultSlug),
                    'with_front' => false
                ),
                'has_archive' => apply_filters("vpm-{$post_type}-cpt-archive-slug", "vpm_{$post_type}s")
            )
        );

        if (!is_wp_error($error)) {
            if (WP_DEBUG) {
                error_log("Loaded Custom Post Type definition for {$post_type}");
            }
            return true;
        } else {

            if (WP_DEBUG) {
                error_log("Error creating post type {$post_type}: " . $error->get_error_message());
            }
            wp_die($error->get_error_message());

            return false;
        }
    }

    /**
     * Define the wp-admin icon for this post type
     */
    public function admin_icon()
    {
        ?>
        <style>
            #adminmenu .menu-icon-<?php echo $this->cpt_name;?> div.wp-menu-image:before {
                content: <?php echo apply_filters("vpm_{$this->type}_dashicons_admin", "\f116"); ?>;
            }
        </style>
        <?php
    }

}