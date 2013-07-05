=== Plugin Name ===
Contributors: tatemz
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ES8NWDUF8XUU
Tags: locations, business, google, maps, foundation, zurb
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and edit your business's locations, contact information, and hours of operation. Display them on a custom Google map with a shortcode.

== Description ==
This is a nice way to add your business locations to your WordPress site. You have options to add your locations' contact information and display special messages (like holiday hours), as well as automatically put your locations on a Google Map.

* Displays location contact info.
* Displays location hours of operation.
* Displays a Google Map with locations.
* Provides a button to Google Maps that users can then get directions to your business.
* Add your locations with a simple shortcode `[cbl-map]`.
* A Google API key is REQUIRED before a custom map can be displayed. <https://developers.google.com/maps/documentation/javascript/tutorial#api_key>

Support will be at the [GitHub repo] (https://github.com/anchorstudios/custom-business-locations/issues). [Documentation] (https://github.com/anchorstudios/custom-business-locations/wiki) as well.

== Installation ==

1. Upload the `custom-business-locations` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add your Google Maps API key on your WordPress site (Settings->Custom Business Locations).
1. Create and edit your locations.
1. Place the `[cbl-map]` shortcode in your pages or posts

== Screenshots ==

1. Custom Google map display with custom notices, hours of operation, and contact information.

== Changelog ==

= 1.1.1 & 1.1.2 =
* Minor bug fix with phone numbers not being displayed
* Minor bug fix with old plugin options taking over new ones
* Compressed CSS

= 1.1 =
* Many speed fixes (Javascript was drastically simplified)
* No more jQuery problems
* Removed custom post types
* New settings UI
* Google API Key validation
* CSS fixes for frontend

