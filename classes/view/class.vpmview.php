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

class vpmView
{
    /** @var        string $cpt_name The full name of the CPT */
    protected $cpt_name;

    /** @var        string $type The type of CPT being managed */
    protected $type;

    public function __construct($cpt_name, $type)
    {

        $this->cpt_name = $cpt_name;
        $this->type = $type;

        // Load any actions or filters for the class
        add_action('plugins_loaded', array( $this, 'init'), 11 );
        add_filter( "vpm-{$this->type}-view-class-instance", array( $this, 'get_instance' ), 1, 3 );
    }

    /**
     * @param mixed     $class      Class instance (or null)
     *
     * @return vpmView  - Existing class instance
     */
    public function get_instance( $class ) {

        if ( 'vpmView' !== get_class( $class ) ) {
            $class = $this->instance;
        }

        return $class;
    }

    public function init() {

        if (class_exists('SFWD_LMS')) {
            add_filter('learndash_template', array( $this, 'load_learndash_template'), 10, 5);
        }

    }

    /**
     * Show a video section
     *
     * @param string        $video_url      Link to video
     *
     * @return  string      - HTML containing video playback
     */
    public function show_topic_video( $video_url ) {

    	$attr = array(
    		'src' => esc_url( $video_url ),
		    'width' => '1024',
		    'preload'   => 'metadata',
		    'class' => 'vpm-video-embed',
	    );

        ob_start(); ?>
        <div class="vpm-view-video-playback">
	        <?php echo wp_video_shortcode( $attr ); ?>
        </div>
        <?php

        $html = ob_get_clean();
        return $html;
    }

    /**
     * @param array $topic  -   Array of metadata for the topic to display
     *
     * @return string   - HTML for the topic meta layout
     */
    public function show_topic_meta( $topic ) {

    	if (WP_DEBUG) {
    		error_log("VPMMeta: Loaded topic: " . print_r( $topic, true));
	    }

        ob_start(); ?>
	    <div class="vpm-separator"></div>
	    <h3 class="vpm-metadata-header"><?php _e("Links & Information", "vpmlang"); ?></h3>
        <div class="vpm-table vpm-topic-meta-view clearfix">
	        <div class="vpm-thead">
		        <div class="vpm-table-row clearfix">
			        <div class="vpm-table-cell vpm-2-col vpm-uneven-col-1">
				        <?php _e("What", "vpmlang"); ?>
			        </div>
			        <div class="vpm-table-cell vpm-2-col vpm-uneven-col-2">
				        <?php _e("Type", "vpmlang"); ?>
			        </div>
		        </div>
	        </div>
	        <div class="vpm-separator clearfix"></div>
	        <div class="vpm-tbody"><?php

		        $fields  = apply_filters( "vpm_{$this->type}_settings", null );

		        foreach( $topic as $key => $value ) {

		        	$html = '';
			        $value = '';
			        $label = '';
			        $meta_icon_css = '';

		        	// process type
			        switch ( $fields[$key]['type'] ) {
				        case 'file':
				        	$value = esc_url( $topic[$key]['url'] );
							$label = esc_attr( $fields[$key]['label'] );
							$html = "<span class='vpm-file'><a href='{$value}' title='{$label}'>{$label}</a></span>";

					        echo $this->add_metadata_row( $html, $value, 'file', $topic[$key]['file'] );
				        	break;

				        case 'url':

				        	if ( false === strpos( $topic[$key], '_video' ) ) {
						        $value         = esc_url( $topic[ $key ] );
						        $label         = esc_attr( $fields[ $key ]['label'] );
						        $html          = "<span class='vpm-link'><a href='{$value}' title='{$label}'>{$label}</a></span>";

						        echo $this->add_metadata_row( $html, $value, 'link', null );
					        }
				        	break;


				        case 'array':

					        $label = esc_attr( $fields[ $key ][ 'label' ] );
							$data = explode(';', $topic[$key]);

					        foreach( $data as $lnk ) {

					        	if ( empty( $lnk ) ) {
							        continue;
						        }

						        $value = esc_url( $lnk );
						        $html          = "<span class='vpm-link'><a href='{$value}' title='{$label}'>{$value}</a></span>";
						        echo $this->add_metadata_row( $html, $lnk, 'link', null );

					        }

				        	break;

				        default:

			        }
		        }
	        ?>
	        </div>
        </div>
        <?php
        $html = ob_get_clean();

	    return $html;
    }

    private function get_icon_type( $type, $value = null ) {

    	$class = null;

    	switch( $type ) {

    		case 'link':
    			$class = 'link';
			    break;

		    case 'file':

		    	$ext = 'default';

		    	// extract the file type to use icon for.
			    if ( !empty( $value ) ) {
				    $file = wp_check_filetype( $value );
				    $ext= $file['ext'];
			    }

			    $class = $ext;
		    	break;

		    default:
		    	$class = "default";
	    }

	    return $class;
    }

	/**
	 * Generate a <div></div> table row for new metadata entry
	 *
	 * @param   string      $content        HTML containing the link itself
	 * @param   string      $link           URL to the content/link
	 * @param   string|null $type           Type of link (link, file)
	 * @param   string|null $file           File being linked (when $type == 'file')
	 *
	 * @return string                       HTML for a table row.
	 */
    private function add_metadata_row( $content, $link, $type = 'link', $file = null ) {

	    $icon_type =  $this->get_icon_type( $type, $file );
    	ob_start();
	    ?>
	    <div class="vpm-table-row clearfix">
		    <div class="vpm-table-cell vpm-2-col vpm-content-col vpm-uneven-col-1">
			    <?php echo $content; ?>
		    </div>
		    <div class="vpm-table-cell vpm-2-col vpm-uneven-col-2 vpm-icon">
			    <a href="<?php echo esc_url( $link ); ?>">
				    <img class="vpm-icon-<?php echo $type; ?>" src="<?php echo plugins_url( "css/icons/{$icon_type}-icon.png", VPM_PLUGIN_FILE ); ?>"/>
			    </a>
		    </div>
	    </div>
	    <?php
	    return ob_get_clean();
    }
    /**
     * Load custom template(s) for LearnDash LMS
     *
     * @param       string      $filepath           - Path to existing template file
     * @param       string      $name               - Name of template
     * @param       array       $args               - Array of arguments for the template
     * @param       boolean     $echo               - Whether to echo the template right away
     * @param       boolean      $return_file_path  - Return the template path
     *
     * @return      string      - Path to the template file
     */
    public function load_learndash_template( $filepath, $name, $args, $echo, $return_file_path ) {

        if (WP_DEBUG) {
            error_log("VPMLD: {$filepath}, {$name}, " . ( !empty( $args ) ? print_r($args, true) : 'none' ) . ", {$echo}, {$return_file_path}.");
        }

        if ('topic' === $name) {
            if ( file_exists( get_template_directory() . "/ld-templates/topic.php") ) {
                $filepath = get_template_directory() . "/ld-templates/topic.php";
            }

            if ( file_exists( get_stylesheet_directory() . "/ld-templates/topic.php") ) {
                $filepath = get_stylesheet_directory() . "/ld-templates/topic.php";
            }

            if ( file_exists( VPM_PLUGIN_DIR . 'ld-templates/topic.php' ) ) {
            	$filepath = VPM_PLUGIN_DIR . 'ld-templates/topic.php';
            }

            if ( file_exists( dirname(__FILE__) . 'ld-templates/topic.php') ) {
                $filepath = dirname(__FILE__) . 'ld-templates/topic.php';
            }

            if (WP_DEBUG) {
                error_log("VPMLD: Going to load: {$filepath}");
            }

        }

        return $filepath;
    }

    /**
     * Allow file upload(s) in metabox form
     */
    public function update_edit_form() {
        echo ' enctype="multipart/form-data"';
    } // end update_edit_form

    /**
     * Display the metabox for the VPM custom post type(s).
     * @param $options
     * @param $definition
     * @return string
     */
    public function showMetabox( $options, $definition )
    {
        ob_start(); ?>
        <div class="vpm-table wp-list-table widefat">
            <?php
            $naction = str_replace("-", '_', "{$this->type}_savesettings");
            $nonce = str_replace('-', '_', "{$this->type}_nonce");
            
            wp_nonce_field( $naction, $nonce, true, true ); ?>
            <div class="vpm-table-body"><?php

                foreach( $options as $name => $value ) { ?>
                <div class="vpm-row">
                    <?php

                    if (WP_DEBUG) {
                        error_log("Setting {$name} type to {$definition[$name]['type']} - {$definition[$name]['label']}");
                    }

                    do_action("vpm_show_{$this->type}_setting_{$definition[$name]['type']}", $name, $value, $definition ); ?>
                </div><?php
                } ?>
            </div>
        </div> <?php
        $metabox = ob_get_clean();

        return $metabox;
    }

    /**
     * Create input for an array (setting) (input + multi-select + add/remove buttons)
     *
     * @param string        $name           - The option (setting) name
     * @param array         $value          - The option value
     * @param array         $definition     - The definition array for the settings
     *
     * @return string       - HTML for the input field
     */
    public function showSetting_array( $name, $value, $definition ) {

	    error_log( "VPM View: Array value before filter: " . print_r($value, true) );

        // filter / set a default value if needed.
        $value = apply_filters('vpm_setting_array_default_value', $value, $name, $definition[$name] );

        ob_start();?>
        <div class="vpm-column vpm-label-col vpm-settings-array">
            <input type="hidden" class="vpm-setting-name" value="<?php echo $name; ?>">
            <label for="<?php echo "{$name}_id" ?>" class="vpm-admin-setting"><?php echo $definition[$name]['label']; ?>:</label>
            <input type="text" class="<?php echo "{$name}_input"; ?>" id="<?php echo "{$name}_id"; ?>" name="vpm-array-<?php echo "{$name}"; ?>" value="" placeholder="Add..." />
            <input type="button" class="vpm-add-button button-secondary button-small" id="<?php echo "{$name}_add_btn"; ?>" value="<?php _e("Add", "vpmlang"); ?>">
        </div>
        <div class="vpm-column vpm-setting-col vpm-settings-array">
            <select size="4" id="<?php echo "{$name}_select_id"; ?>" class="<?php echo "{$name}_select"; ?>" multiple="multiple"><?php

                error_log( "VPM View: Array value: " . print_r($value, true) );

                if (empty($value)) { ?>
                <option value="-1"> --- </option><?php
                }

                foreach( $value as $key => $entry ) {
                    if (!empty($entry)) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $entry; ?></option>
                        <?php
                    }
                } ?>
            </select>
            <input type="hidden" class="vpm-select-values" name="<?php echo "vpm-array-hidden-{$name}"; ?>" id="vpm-array-hidden-<?php echo "{$name}_id" ?>" <?php echo (!empty($value) ? 'value="' . implode( ';', $value) . '" ' : null); ?>>
            <input type="button" class="vpm-rm-button button-secondary button-small" id="<?php echo "{$name}_rm_btn"; ?>" value="<?php _e("Remove", "vpmlang"); ?>">
        </div><?php
        return ob_get_contents();
    }

    /**
     * Create input field for an integer value in the settings
     *
     * @param   string        $name           - The option (setting) name
     * @param   integer       $value          - The option value
     * @param   array         $definition     - The definition array for the settings
     *
     * @return  string                        - HTML for the input field
     *
     * @since               0.1               - Added
     */
    public function showSetting_int( $name, $value, $definition ) {
        if (WP_DEBUG) {
            error_log("Running the int Setting view action");
        }

        ob_start(); ?>
        <div class="vpm-column vpm-label-col">
            <input type="hidden" class="vpm-setting-name" value="<?php echo $name; ?>">
            <label for="<?php echo "{$name}_id"; ?>" class="vpm-admin-setting"><?php echo $definition[$name]['label']; ?>:</label>
        </div>
        <div class="vpm-column vpm-setting-col">
            <input type="number" name="vpm-int-<?php echo "{$name}"; ?>" id="<?php echo "{$name}_id"; ?>" value="<?php echo ( $value == 0 ? null : apply_filters('vpm_setting_int_default_value', $value, $name, $definition[$name] ) ); ?>" class="vpm-admin-setting vpm-input-numeric" <?php echo ($definition[$name]['disabled'] ? 'disabled="disabled"' : null); ?>/>
        </div><?php

        return ob_get_contents();
    }

    /**
     * Create input field to select & list a file in the settings (string)
     *
     * @param   string        $name           - The option (setting) name
     * @param   string        $value          - The option value
     * @param   array         $definition     - The definition array for the settings
     *
     * @return  string                        - HTML for the input field
     *
     * @since   0.1                           - Added
     */
    public function showSetting_file( $name, $value, $definition ) {

        /**
         * Filters the file type(s)/media type(s) allowed for inclusion/selection
         *
         * @return      string      Comma separated list of valid media type notations (for files)
         *
         * @since       0.1         Added
         */
        $file_type = apply_filters("vpm_settings_filetype_{$definition[$name]}", $definition[$name]['filetype'] );

        ob_start(); ?>
        <div class="vpm-column vpm-label-col">
            <input type="hidden" class="vpm-setting-name" value="<?php echo $name; ?>">
            <label for="<?php echo "{$name}_id"; ?>" class="vpm-admin-setting"><?php echo $definition[$name]['label']; ?>:</label>
        </div>
        <div class="vpm-column vpm-setting-col">
            <input type="file" <?php apply_filters('vpm_allow_multiple_file_selects', false, $name, $definition) ? 'multiple="multiple"' : null ;?> accept="<?php echo $file_type; ?>" title="<?php echo basename($value['file']); ?>" name="vpm-file-<?php echo "{$name}"; ?>" id="<?php echo "{$name}_id"; ?>" value="<?php echo ( empty($value['file']) ? null : apply_filters('vpm_setting_file_default_value', $value['file'], $name, $definition[$name] ) ); ?>" class="vpm-admin-setting vpm-input-file" />
            <label id="<?php echo "{$name}_label_id"; ?>"><?php echo ( empty($value['file']) ? __("No file selected", "vpmlang") : basename($value['file']) ); ?></label>
            <span class="vpm-delete-lnk">
                <input type="hidden" class="vpm-file-delete" name="vpm-file-delete-<?php echo "{$name}"; ?>" value>
                <a id=id="<?php echo "{$name}_delete_id"; ?>"><?php _e("Clear file", "vpmlang"); ?></a>
            </span>
        </div>
        <?php

        return ob_get_contents();
    }

    public function showSetting_url( $name, $value, $definition ) {
        
        ob_start(); ?>
        <div class="vpm-column vpm-label-col">
            <input type="hidden" class="vpm-setting-name" value="<?php echo $name; ?>">
            <label for="<?php echo "{$name}_id"; ?>" class="vpm-admin-setting"><?php echo $definition[$name]['label']; ?>:</label>
        </div>
        <div class="vpm-column vpm-setting-col">
            <input type="url" name="vpm-url-<?php echo "{$name}"; ?>" id="<?php echo "{$name}_id"; ?>" value="<?php echo ( empty($value) ? null : apply_filters('vpm_setting_url_default_value', esc_url($value), $name, $definition[$name] ) ); ?>" class="vpm-admin-setting vpm-input-string" placeholder="<?php _e("Insert web address/URL", "vpmlang"); ?>"/>
        </div>
        <?php

        return ob_get_contents();
    }

    public function showSetting_textbox( $name, $value, $definition ) {

        ob_start(); ?>
        <div class="vpm-column vpm-label-col">
            <input type="hidden" class="vpm-setting-name" value="<?php echo $name; ?>">
        </div>
        <div class="vpm-column vpm-setting-col">
            <label for="<?php echo "${name}_id}"; ?>"><?php echo $definition[$name]['label']; ?></label>
            <textarea name="vpm-textbox-<?php echo $name; ?>" placeholder="<?php echo $definition[$name]['placeholder'] ?>"
        </div>
        <?php

        return ob_get_contents();
    }

    /**
     * Error handler for admin errors while editing a VPM related post.
     */
    public function show_admin_errors() {
        $errors = get_option('vpm_admin_errors');

        if (!empty($errors)) { ?>
            <div class="error"><?php
                foreach( $errors as $error ) { ?>
                    <p><?php echo $error; ?></p><?php
                } ?>
            </div><?php
        }
    }
}