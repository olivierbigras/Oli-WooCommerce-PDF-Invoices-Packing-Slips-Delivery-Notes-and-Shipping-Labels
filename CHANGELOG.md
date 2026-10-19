# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-10-19

### Added
- Initial release of Oli PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels
- Sequential invoice numbering system with customizable prefix, suffix, padding, and start number
- Generate invoices on configurable order statuses (processing, completed by default)
- Invoice date tracking
- PDF generation for four document types:
  - Invoices with full order details and totals
  - Packing slips for warehouse fulfillment
  - Delivery notes with signature field
  - Shipping labels
- Auto-attach invoices to WooCommerce emails (configurable which email types)
- Print and download buttons in:
  - Order list (row actions)
  - Order edit screen (meta box)
  - My Account page (customer downloads)
- Bulk actions to download multiple PDFs as ZIP file
- Template customization options:
  - Company logo upload
  - Company name and address
  - VAT and tax numbers
  - Footer text
  - Show/hide fields: SKU, weight, tax breakdown, shipping, payment method, customer note, product images, product meta
- Theme-overridable PHP templates (place in `yourtheme/oli-pdf-templates/`)
- Migration tool from WebToffee Print Invoices plugin:
  - Import settings (company info, logo, numbering configuration)
  - Import existing invoice numbers from orders
  - Continue existing invoice number sequence
  - Dry-run mode to preview changes
- Multi-currency support (displays order currency)
- RTL language support
- French translation included
- WPML and TranslatePress compatibility
- WooCommerce High-Performance Order Storage (HPOS) compatibility
- Secure PDF access with capability checks and token verification
- GPL-licensed PDF library (Dompdf) bundled via Composer
- WordPress coding standards compliance
- Sanitization, escaping, nonces, and capability checks throughout
- Action hook: `oli_pdf_invoice_generated`
- GitHub Actions workflow for PHP linting and PHPCS checks

### Security
- All user inputs sanitized and validated
- Nonce verification on all actions
- Capability checks (`manage_woocommerce` for admin, order ownership for customers)
- Token-based authentication for customer PDF downloads
- No external dependencies or CDN usage
- No tracking or data collection

### Developer Features
- PSR-4 autoloading
- Namespaced classes
- Overridable templates
- Action and filter hooks
- Clean, documented code
- WordPress coding standards

## [Unreleased]

### Known Gaps vs WebToffee Free
- Credit notes: Not yet implemented (planned for future release)
- Multiple labels per sheet: Basic implementation (future enhancement for label size presets)
- Advanced template color customization: Basic implementation (future enhancement)
- Product images in PDFs: Option exists but requires optimization for large catalogs

### Planned Features
- Credit notes support
- More shipping label size presets (4x6, etc.)
- Multiple labels per sheet with configurable layouts
- Advanced color picker for template customization
- PDF preview in admin
- Email template customization
- Custom PDF paper sizes
- Barcode/QR code support
