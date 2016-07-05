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