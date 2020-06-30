=== Product Sales Report for WooCommerce ===
Contributors: hearken, aspengrovestudios
Donate link: https://potentplugins.com/donate/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-plugin-readme-donate-link
Tags: woocommerce, sales, report, reporting, export, csv, excel, spreadsheet
Requires at least: 3.5
Tested up to: 5.4.1
Stable tag: 1.4.10
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Generates a report on individual WooCommerce products sold during a specified time period.

== Description ==

The Product Sales Report plugin generates reports on the quantity and gross sales of individual WooCommerce products sold over a specified date range. Reports can be downloaded in CSV (Comma-Separated Values) format for further analysis in your spreadsheet software, or for import into other software that supports CSV-formatted data files.

Features:

* Use a date range preset, or specify custom start and end dates.
* Report on all products in your store, or limit the report to only include products within certain categories or only specific product IDs.
* Limit the report to orders with certain statuses (e.g. Processing, Complete, or Refunded).
* Customize the report sorting order (sort by Product ID, Quantity Sold, or Gross Sales).
* Schedule the report to be sent automatically by email on a recurring basis with the [Scheduled Email Reports for WooCommerce](https://potentplugins.com/downloads/scheduled-email-reports-woocommerce-plugin/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-repo-upgrade-link) addon.
* Embed the report or a download link in posts and pages with the [Frontend Reports for WooCommerce](https://potentplugins.com/downloads/frontend-reports-woocommerce-plugin/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-repo-upgrade-link) addon.

A [pro version](https://potentplugins.com/downloads/product-sales-report-pro-wordpress-plugin/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-repo-upgrade-link) with the following additional features is also available:

* Report on product variations individually.
* Optionally include products with no sales (note: does not report on individual product variations with no sales).
* Report on shipping methods used (Product ID, Product Name, Quantity Sold, and Gross Sales fields only).
* Limit the report to orders with a matching custom meta field (e.g. delivery date).
* Change the names of fields in the report.
* Change the order of the fields/columns in the report.
* Include any custom field defined by WooCommerce or another plugin and associated with a product (note: custom fields associated with individual product variations are not supported at this time).
* Save multiple report presets to save time when generating different reports.
* Export in Excel (XLSX or XLS) format.
* Send the report as an email attachment.

If you like this free plugin, please consider [making a donation](https://potentplugins.com/donate/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-plugin-repo-donate-link).

== Installation ==

1. Click "Plugins" > "Add New" in the WordPress admin menu.
1. Search for "Product Sales Report".
1. Click "Install Now".
1. Click "Activate Plugin".

Alternatively, you can manually upload the plugin to your wp-content/plugins directory.

== Frequently Asked Questions ==

== Screenshots ==

1. Report options
2. Sample output (simulated)

== Changelog ==

= 1.4.10 =
* Added messaging on admin page about line item refunds

= 1.4.9 =
* Updated license (GPLv3+)
* Removed social media embeds from admin page

= 1.4.8 =
* Fixed review/donate notification not being hidden

= 1.4.7 =
* Fixed incorrect date ranges when using the "Last 7 days", "Last 30 days", "Next 7 days", or "Next 30 days" options
* Added calendar month date range options
* Fixed conflict with Product Sales Report Pro

= 1.4.6 =
* Fixed potential incompatibility with order status plugin(s)

= 1.4.5 =
* Fixed potential incompatibility with custom order statuses

= 1.4 =
* Added the ability to select multiple product categories
* Added an option to limit the report to specified product IDs
* Added an option to limit the report to orders with specified statuses

= 1.3.2 =
* Added an option to exclude free products

= 1.3 =
* Added a View Report option

= 1.2.4 =
* Added a date picker for browsers without support for the HTML5 date input

= 1.2.2 =
* Removed anonymous function to improve compatibility with old versions of PHP

= 1.2.1 =
* Fixed bug affecting products with no categories

= 1.2 =
* Added Product Categories field

= 1.1.7 =
* Added Variation SKU field

= 1.1.6 =
* Added Gross Sales (After Discounts) as sort field

= 1.1.5 =
* Added field for gross sales after discounts

= 1.1.4 =
* Added Pro version info

= 1.1.2 =
* Made report settings persistent (options are saved when a report is generated)

= 1.1.1 =
* Fixed timezone issue affecting the report period

= 1.1 =
* Added checkboxes to select which fields to include in the report
* Added the Product SKU field

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.4.7 =
When using the "Last 7 days", "Last 30 days", "Next 7 days", or "Next 30 days" options in previous versions of the plugin, the computed date range included one too many days. We recommend updating immediately to ensure data accuracy.