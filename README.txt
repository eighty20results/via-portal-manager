=== VIA Portal Manager ===
Contributors: eighty20results
Tags: content management, VIA Platform
Requires at least: 4.5.3
Requires PHP 5.4 or later.
Tested up to: 4.5.3
Stable tag: 0.3
License: GPLv2

Manage training (session) content for the VIA Portal.

== Description ==


== ChangeLog ==

== 0.3 ==

* FIX: Use correct text domain (vpmlang)
* FIX: Renamed to properly handle case sensitive operating systems (Linux)

== 0.2 ==

* FIX: Ensure text doesn't encroach on visibility buttons.
* FIX: Path to one-click updater
* FIX: Didn't always load the correct metadata definition
* FIX: Clean up get_instance() code.
* FIX: Didn't correctly explode and process array in vmmodel
* FIX: Didn't correctly explode and process array in showSetting_array()
* ENHANCEMENT: One-click update support for VPM
* ENHANCEMENT: Add user privilege test (is_enduser()  and is_facilitator())
* ENHANCEMENT: Use filters to access class instances for controller & view
* ENHANCEMENT: Add support for VIA Portal Manager Role definitions
* ENHANCEMENT: Create default roles: Facilitator & Participant
* ENHANCEMENT: Configure roles during plugin activation
* ENHANCEMENT: Add PHPDoc for add_metdata_row() function
* ENHANCEMENT: Add front-end styling (CSS) & JavaScript support to VPM
* ENHANCEMENT: And Front-end JavaScript support for VIA Portal Manager
* ENHANCEMENT: Add VPM_PLUGIN_DIR constant
* ENHANCEMENT: Add PowerPoint & Word icon styling support
* ENHANCEMENT: Add PowerPoint & Word icons
* ENHANCEMENT: Added icons for VIA meta data (links, docs, etc)
* ENHANCEMENT: Display session meta information
* ENHANCEMENT: Conditionally allow loading LearnDash template(s) if LD is present
* ENHANCEMENT: Load the LearnDash session/topic template (if available in plugin or theme)
* ENHANCEMENT: Embed video links for VIA Portal Manager (End user & Facilitator video)
* ENHANCEMENT: Basic styles for Via Portal Manager front-end
* ENHANCEMENT: LearnDash topic template w/session information & embedded video playback
* ENHANCEMENT: Integration with Download Monitor for VPM files
* ENHANCEMENT: Integration with Download Monitor for VPM files
* ENHANCEMENT: Add framework for sort function
* ENHANCEMENT/FIX: Process lists of URLs/arrays during save operation
* ENHANCEMENT: Add debug output
* ENHANCEMENT: Various debug output for troubleshooting
* ENHANCEMENT: Correctly save & display back-end edit meta box

== 0.1 ==

* Initial commit