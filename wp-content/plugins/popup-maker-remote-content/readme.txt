=== Popup Maker - Remote Content ===
Contributors: wppopupmaker, danieliser
Author URI: https://wppopupmaker.com/
Plugin URI: https://wppopupmaker.com/extensions/remote-content/
Tags: 
Requires at least: 3.6
Tested up to: 4.9.6
Stable tag: 1.1.2

The remote content extension allows you to easily fill your popup with a remote content source.

== Description ==

The remote content extension allows you to easily fill your popup with a remote content source. This allows you to have many links open in popups, or create an AJAX call to customize the content of the popup based on what the user clicked.

== Changelog ==
= v1.1.2 - 06/05/2018 =
* Fix: Issue with automatic iframe resize when minimum size is too low.

= v1.1.1 - 06/05/2018 =
* Tweak: Changed default icon color to work on more background colors.
* Fix: Issue with automatic iframe resize when scrolled down the page before opening.

= v1.1.0 - 06/04/2018 =
* Feature: Added extra click selector presets.
* Feature: Added 1 click post type click trigger targeting.
* Feature: Added new Remote Content Area shortcode.
* Feature: Added default url for iframes to show when using non click triggers.
* Feature: Added experimental post type method with customizable content templates for quick start when dealing with post type content in a popup.
* Feature: Added ability to add your own custom loading icons.
* Improvement: CSS styles for position and showing loading icons, as well as how content sizes by default.
* Improvement: Added minimum height option.
* Improvement: Added support for Iframe Resizer to make iframes not look like iframes (requires ability to run code on the remote site being loaded, we even have a helper plugin you can use).
* Improvement: Added better error handling when content is not returned correctly.
* Improvement: Updated for full Popup Maker v1.7 support.
  * Leveraged the new AssetCache reducing the need to load an extra JS file for this extension.
  * Autoloader
  * Upgrade routines.
* Dev: Removed the old metabox as the settings are now controlled in the shortcode editor.

= v1.0.0 =
* Initial Release
* Loading Icons by: @lukehaas - http://projects.lukehaas.me/css-loaders/