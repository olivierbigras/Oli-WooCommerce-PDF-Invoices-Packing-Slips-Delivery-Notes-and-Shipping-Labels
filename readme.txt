=== Oli PDF Invoices & Packing Slips for WooCommerce ===
Contributors: bigrat95
Tags: woocommerce, pdf, invoice, packing slip, shipping label
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 9.0
WC tested up to: 11.0

Generate professional PDF invoices, packing slips, delivery notes, and shipping labels for your WooCommerce orders.

== Description ==

**Oli PDF Invoices & Packing Slips for WooCommerce** is a complete PDF document solution that helps you create professional invoices, packing slips, delivery notes, and shipping labels.

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

== External services ==

This plugin does not call remote APIs during normal use.

= Local PDF generation =

Invoices, packing slips, delivery notes, shipping labels, and credit notes are generated on your WordPress server with the bundled Dompdf library. Remote HTTP fetching is disabled. Company logos and product images are embedded from local Media Library files as data URIs. A logo URL that is not a local file is omitted instead of being fetched from a third-party host.

= Author website =

The plugin header Plugin URI and Author URI point to https://olivierbigras.com/. The plugin does not contact that site at runtime.

No requests are made to PayPal, Google, Cloudflare Turnstile, Carto, Leaflet, Google Sheets, Google Fonts, or Adobe Typekit.

== Screenshots ==

1. Settings page - Invoice configuration
2. Settings page - Company information
3. Settings page - Template customization
4. Order edit screen with PDF buttons
5. Sample invoice PDF
6. Bulk actions in orders list

== Changelog ==

= 0.1.4 - 2026-10-23 =
* Distribution zip includes production vendor/ (Dompdf 3.1.6), LICENSE.txt (GPLv2 or later), and languages/ (.pot 0.1.4, fr_FR .mo and .l10n.php). Translations load on init only before WordPress 6.7.
* Invoice logos use explicit width/height (PNG and SVG). Admin actions use full translatable labels per document type.

= 0.1.3 - 2026-10-21 =
* Align Tested up to 7.1 in the plugin header and readme.txt
* Scope PDF template variables so they are not unprefixed globals
* Exclude development files from the release zip via .distignore and .gitattributes

= 0.1.2 - 2026-10-21 =
* Display name: Oli PDF Invoices & Packing Slips for WooCommerce (slug and text domain remain oli-pdf-invoices-packing-slips)
* Extracted PDF and admin CSS/JS to enqueued files; no inline script/style tags
* Escaped remaining admin and template output
* Upgrade copies leftover olipdf_* options to oli_pdf_* then deletes the old keys
* Documented that the plugin does not use external services

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

= 0.1.4 =
Distribution zip ships Dompdf via Composer --no-dev, LICENSE.txt, and language files.

= 0.1.3 =
Plugin Check header alignment (Tested up to 7.1), prefixed template scope, and a cleaner release zip.

= 0.1.2 =
WordPress.org readiness: renamed display name, extracted CSS/JS, output escaping, and a lossless option-key migration.

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
* `credit-note.php`

== Credits ==

Developed by Olivier Bigras

Source: https://github.com/olivierbigras/Oli-WooCommerce-PDF-Invoices-Packing-Slips-Delivery-Notes-and-Shipping-Labels
