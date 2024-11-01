=== Simple Dropdown Filter by Category for WooCommerce ===
Contributors: inboundhorizons
Plugin Name: Simple Dropdown Filter by Category for WooCommerce
Tags: woocommerce, category, filter, dropdown
Requires at least: 3.3
Tested up to: 6.5.2
Stable tag: 0.0.1.6
Requires PHP: 5.4
License: MIT
License URI: https://opensource.org/licenses/MIT

Add a dropdown on the catalog page to filter products by WooCommerce categories.

== Description ==

Simple Dropdown Filter by Category for Woocommerce automatically adds a dropdown on your shop catalog page that allows you to instantly filter products by WooCommerce product categories. The dropdown will show up on the shop page, product archive page and product tag page. It shows a product count per each category by default. The plugin currently supports only selecting a single category to filter.

The dropdown filter buttons shows up to the right of the native WooCommerce “sort by” dropdown and uses native WooCommerce component code.

= Features and Extras =

You can choose to manually exclude certain categories to show up in the dropdown filter if you do not want users to filter by those categories.

You can toggle off or on showing product counts by category or showing empty categories in your shop.

You can set custom CSS classes for styling the button to match your theme, and even apply custom CSS directly to the plugin from the back end settings panel.

It will work with nested parent/child categories you create in WooCommerce and display the child categories underneath the parent in your dropdown menu when users select a category.


== Installation ==

1. Unzip the plugin file and extract the folder.
2. Upload the category-filter-for-woocommerce/ folder to the /wp-content/plugins/ directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.


== Screenshots ==

1. Settings menu to configure plugin.
2. Dropdown filter before activation.
3. Dropdown filter showing available categories to filter by.



== Changelog ==

= 0.0.1.6 - 2024-04-12 =
* UPDATE - Cleaned up code.
* UPDATE - Improved plugin description.
* UPDATE - Added screenshots.

= 0.0.1.5 - 2023-07-31 =
* UPDATE - Implemented improvements suggested by the WordPress plugin review.
* UPDATE - Added a security nonce check when saving settings.
* UPDATE - Improved the way POST data is sanitized.
* UPDATE - Improved the way echoed HTML is escaped.
* UPDATE - Improved the class and settings prefixes to be entirely unique to this plugin.

= 0.0.1.4 - 2023-05-05 =
* UPDATE - Added internal header image on settings page.
* UPDATE - Clarified instructions and info on settings page.

= 0.0.1.3 - 2023-04-24 =
* UPDATE - Changed name to "Simple Dropdown Filter by Category for Woocommerce".
* UPDATE - Added backend CSS styling to match other plugins by Inbound Horizons.

= 0.0.1.2 - 2023-01-27 =
* FIX - CSS not always styling the WooSelect dropdown on the backend settings page.
* FIX - WooSelect dropdown sometimes broke on the frontend if no categories were excluded.

= 0.0.1.1 - 2022-11-14 =
* ADD - Hide categories option in setting to prevent some categories from being shown to customers.
* ADD - Parent CSS Class option to let the user set a CSS class at the top level of the select dropdown container.
* UPDATE - Changed the way the dropdown is generated on the frontend to use less WooCommerce code. (Increases future flexibility.)

= 0.0.1 - 2022-06-22 =
* First public release

