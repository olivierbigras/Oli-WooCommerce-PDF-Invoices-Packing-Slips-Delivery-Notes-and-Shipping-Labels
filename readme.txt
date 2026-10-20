=== Oli PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels for WooCommerce ===
Contributors: bigrat95
Tags: woocommerce, pdf, invoice, packing slip, shipping label
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 9.0
WC tested up to: 11.0

Generate professional PDF invoices, packing slips, delivery notes, and shipping labels for your WooCommerce orders.

== Description ==

**Oli PDF Invoices** is a complete PDF document solution for WooCommerce that helps you create professional invoices, packing slips, delivery notes, and shipping labels.

= Key Features =

* **Sequential Invoice Numbering** - Automatic invoice numbering with customizable prefix, suffix, and padding
* **Email Attachments** - Automatically attach invoices to customer emails
* **Multiple Document Types** - Generate invoices, packing slips, delivery notes, shipping labels, and credit notes
* **Bulk Actions** - Download multiple PDFs at once from the orders list
* **Customer Access** - Customers can download their invoices from My Account
* **Customizable Templates** - Add your logo, company info, VAT/tax numbers, and footer text
* **Template Overrides** - Override templates in your theme folder
* **Show/Hide Fields** - Control which fields appear: SKU, weight, tax breakdown, shipping, payment method, customer notes, product images, and meta
* **Multi-Currency Support** - Displays order currency correctly
* **RTL Support** - Full RTL language support
* **WPML/TranslatePress Compatible** - Works with multilingual plugins
* **HPOS Compatible** - Full support for WooCommerce High-Performance Order Storage
* **Migration Tool** - Import settings and invoice numbers from WebToffee Print Invoices plugin

= Document Types =

1. **Invoices** - Professional invoices with sequential numbering
2. **Packing Slips** - Simple packing lists for fulfillment
3. **Delivery Notes** - Delivery notes with signature field
4. **Shipping Labels** - Printable shipping labels with A4, Letter, 4x6, 4x4, custom sizes, and multiple labels per sheet
5. **Credit Notes** - Generated when an order is refunded

= Easy to Use =

* Print/download buttons in order list (row actions + bulk actions)
* PDF meta box on order edit screen
* Download invoice button in My Account for customers
* Customizable settings page under WooCommerce > PDF Documents

= Filters & Hooks =

Developers can customize the plugin using WordPress filters and action hooks. Templates can be overridden by copying them to `yourtheme/oli-pdf-templates/`.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/oli-pdf-invoices-packing-slips/` or install through WordPress plugins screen
2. Activate the plugin through 'Plugins' screen in WordPress
3. Go to WooCommerce > PDF Documents to configure settings
4. Add your company information, logo, and customize invoice numbering
5. That's it! PDFs will be generated automatically

== Frequently Asked Questions ==

= How do I customize the PDF templates? =

Copy the template files from `wp-content/plugins/oli-pdf-invoices-packing-slips/templates/` to `wp-content/themes/your-theme/oli-pdf-templates/` and edit them.

= Can customers download their invoices? =

Yes, customers can download invoices from the My Account > Orders page if an invoice has been generated for their order.

= How do I migrate from WebToffee Print Invoices plugin? =

Go to WooCommerce > PDF Documents > Migration tab. Run a dry-run first to preview changes, then run the full migration. Your settings and existing invoice numbers will be imported.

= What PDF library does this use? =

The plugin uses Dompdf, a GPL-compatible PDF generation library. All dependencies are included in the plugin.

= Is this plugin GDPR compliant? =

Yes, the plugin does not collect or transmit any data. All PDFs are generated on your server.

= Does it work with High-Performance Order Storage (HPOS)? =

Yes, the plugin is fully compatible with WooCommerce HPOS.

== Screenshots ==

1. Settings page - Invoice configuration
2. Settings page - Company information
3. Settings page - Template customization
4. Order edit screen with PDF buttons
5. Sample invoice PDF
6. Bulk actions in orders list

== Changelog ==

= 0.1.1 - 2026-10-20 =
* Shipping label size presets (A4, Letter, 4x6, 4x4, custom) and multiple labels per sheet
* Color pickers for template primary and secondary colors
* Credit notes for refunds
* Optimized local product images in PDFs
* Migration verified against WebToffee 5.x option names and order meta
* Release zip includes vendor/ so Composer is not required on the server

= 0.1.0 - 2026-10-19 =
* Initial release
* Sequential invoice numbering with prefix/suffix/padding
* PDF generation for invoices, packing slips, delivery notes, and shipping labels
* Email attachment support
* Customer download from My Account
* Bulk actions for multiple orders
* Template customization
* Migration from WebToffee plugin
* HPOS compatibility
* RTL and multilingual support

== Upgrade Notice ==

= 0.1.1 =
Adds credit notes, label presets, colors, and a corrected WebToffee migration.

= 0.1.0 =
Initial release.

== Developer Notes ==

= Hooks =

**Actions:**
* `oli_pdf_invoice_generated` - Fired when an invoice is generated
* `oli_pdf_credit_note_generated` - Fired when a credit note is generated

**Filters:**
* All WordPress standard filters apply to content

= Template Override =

Copy templates to: `yourtheme/oli-pdf-templates/`

Available templates:
* `invoice.php`
* `packing-slip.php`
* `delivery-note.php`
* `shipping-label.php`

== Credits ==

Developed by Olivier Bigras
