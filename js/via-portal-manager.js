/**
 * Created by sjolshag on 7/18/16.
 */

var vpm = {
    init: function() {
        "use strict";

        this.vpm_facilitator_hide = jQuery('a#vpm-toggle-facilitator-video-lnk');
        this.vpm_user_hide = jQuery('a#vpm-toggle-enduser-video-lnk');

        this.show_videos = jQuery('div.vpm-show-videos');
        var self = this;

        self.vpm_user_hide.on('click', function() {
            event.preventDefault();
            var video = jQuery('div.vpm-user-video');
            video.fadeToggle();
        });

        self.vpm_facilitator_hide.on('click', function() {
            event.preventDefault();
            var video = jQuery('div.vpm-facilitator-video');
            video.fadeToggle();
        });
    }
};

jQuery(document).ready(function() {
    "use strict";

    vpm.init();
});
