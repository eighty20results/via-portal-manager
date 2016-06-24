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

jQuery.noConflict();

var configure_cpt_settings = {
    init: function() {
        "use strict";

        this.add_buttons = jQuery('input.vpm-add-button');
        this.remove_buttons = jQuery('input.vpm-rm-button');
        this.file_selected = jQuery('input.vpm-input-file:file');
        this.delete_file = jQuery('span.vpm-delete-lnk > a');

        var self = this;

        self.add_buttons.unbind('click').on('click', function() {
            var element = this;
            var setting_name = jQuery(element).closest('.vpm-column.vpm-settings-array').find('input.vpm-setting-name').val();
            window.console.log("Add Button clicked for setting: ", setting_name);

            if ( '' !== jQuery(element).val()  ) {
                self.add_to_select(element, setting_name);
            }
        });

        self.remove_buttons.unbind('click').on('click', function() {
            var element = this;
            var setting_name = jQuery(this).closest('.vpm-row').find('input.vpm-setting-name').val();
            window.console.log("Remove Button clicked for setting: ", setting_name);

            self.remove_from_select( element, setting_name );
        });

        self.delete_file.unbind('click').on('click', function() {

            event.preventDefault();

            var element = this;
            var $setting_name = jQuery(element).closest('div.vpm-row').find('input.vpm-setting-name').val();

            self.remove_file( element, $setting_name );

        });

        // Force all drop-down options to get selected on submit/save of post/page.
        jQuery('#publish').on('click', function() {
            
            jQuery('div.vpm-table select option').each(function() {
                jQuery(this).prop('selected', true);
            });
        });

        self.file_selected.unbind('click').on('click', function() {

            var file = jQuery(this);
            var setting_name = file.closest('.vpm-row').find('input.vpm-setting-name').val();

            self.uploaded_file( setting_name, file );
        });

        self.file_selected.unbind('change').on('change', function() {
            var file = jQuery(this);
            var setting_name = file.closest('.vpm-row').find('input.vpm-setting-name').val();
            window.console.log("User selected the file name to upload for setting: ", setting_name, file.val());
            self.uploaded_file( setting_name, file );
        });

    },
    remove_file: function( element, $setting ) {
        "use strict";

        var lnk = jQuery(element);
        var input = lnk.closest("div.vpm-setting-col").find('input[type="hidden"].vpm-file-delete');
        var label = jQuery('label#' + $setting + "_label_id");

        label.html(vpmm.messages.no_file_selected);
        input.val($setting);
        window.console.log("File to delete: ", input.val());
    },
    remove_from_select: function( element, $var_name ) {
        "use strict";

        var select = jQuery('select#' + $var_name + '_select_id');
        select.find(':selected').remove();
    },
    add_to_select: function( element, $var_name ) {
        "use strict";

        var self = this;

        var add_from = jQuery('input#' + $var_name + "_id");
        var hidden = jQuery('input#vpm-array-hidden-' + $var_name + "_id");
        var select = jQuery('select#' + $var_name + "_select_id");
        
        var $new_value = add_from.val();
        var $to_hidden = '';
        var counter = jQuery('select#' + $var_name + "_select_id option").size();
        var resource_list = [];


        if ( false === self.is_url($new_value) ) {
            window.alert(vpmm.messages.no_http);
            return;
        }

        // Grab all existing entries (except ---)
        select.find('option').each(function() {

            var opt = jQuery(this);
            var $res = opt.text();
            
            // The option contains text and it's not the default --- 
            if ( $res.length > 0 && $res.indexOf('---') === -1) {
                window.console.log("Adding new resource to list: " + $res);
                resource_list.push($res);
            }

            if ( 1 === counter && $res.indexOf('---') > -1) {
                select.find('option').remove();
            }
        });
        
        // The new resource contains information
        if ( $new_value.length > 0 && self.is_url($new_value) ) {

            // $resource_list += ( $resource_list.length === 0 ? '' : ';' ) + $new_value;
            resource_list.push($new_value);

            // add to list
            select.append('<option value="url_' + counter + '">' + $new_value + '</option>');

            // clear input
            add_from.val(null);

        } else if ( 0 === $new_value.length) {

            window.alert(vpmm.messages.empty_input);
        }

        window.console.log("List of added resources: ", resource_list);

        // concatenate list & separate w/;
        for (var i in resource_list) {

            if (resource_list[i].length) {
                $to_hidden += resource_list[i] + ";";
            }
        }

        hidden.val($to_hidden);
        window.console.log("List of added resources: " + $to_hidden);
    },
    is_url: function( $str ) {
        "use strict";

        // Borrowed/inspired by credit: Tom Gullen via Stack Overflow
        // http://stackoverflow.com/questions/5717093/check-if-a-javascript-string-is-an-url/5717133#5717133
        var urlPattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\.){3}\\d{1,3}))'+ // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
            '(\#[-a-z\\d_]*)?$','i'); // fragment locater

        return urlPattern.test($str);
    },
    uploaded_file: function( $setting, file) {
        "use strict";

        var label = jQuery('label#' + $setting + "_label_id");
        if ( "" === file.val()) {
            label.html(vpmm.messages.no_file_selected);
        } else {
            var fileSplit = file.val().split('\\');
            label.html( fileSplit[ (fileSplit.length - 1) ] );
        }
    }
};


jQuery(document).ready(function() {
    "use strict";

    configure_cpt_settings.init();
});