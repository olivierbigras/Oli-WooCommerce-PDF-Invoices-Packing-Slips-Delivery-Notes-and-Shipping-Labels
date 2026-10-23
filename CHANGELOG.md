# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.4] - 2026-10-23

### Added
- Distribution zip via `tools/build-zip.sh` (`composer install --no-dev --optimize-autoloader` from the versioned lockfile): production `vendor/` (Dompdf 3.1.6), `LICENSE.txt` (GPLv2 or later), and `languages/` (`.pot` 0.1.4, `fr_FR.po`, compiled `.mo` and `.l10n.php`). Translations load on `init` only on WordPress older than 6.7; `phpcs.xml.dist` excludes `/vendor/*` and `/languages/*`.

### Changed
- Production dependency is `dompdf/dompdf` `^3.1.6`. The zip build no longer passes `--no-security-blocking`; `composer audit --no-dev --locked` must stay clean.
- Invoice (and other document) logos use explicit CSS/`<img>` width and height so Dompdf 3 does not collapse PNG or SVG logos to ~1px.
- Admin View/Download buttons use complete translatable strings per document type (`View invoice`, …) instead of concatenating the slug.
- `tools/build-zip.sh` compiles `.mo` / `.l10n.php` in the staging directory only.

## [0.1.3] - 2026-10-21

### Fixed
- Plugin header `Tested up to` now matches `readme.txt` (`7.1`) so Plugin Check no longer reports `mismatched_tested_up_to_header`.
- Declared `Requires Plugins: woocommerce` in the plugin header.
- PDF templates assign their locals inside a prefixed closure, so unprefixed `$company_name` / `$item` variables are no longer global.

### Added
- `.distignore` and `.gitattributes` `export-ignore` so the release zip omits development paths (`tests/`, `bin/`, `.github/`, PHPCS/PHPUnit/Composer/npm metadata, `.gitignore`, internal Markdown). `readme.txt` stays in the zip.

## [0.1.2] - 2026-10-21

### Changed
- Display name is now **Oli PDF Invoices & Packing Slips for WooCommerce** (wordpress.org trademark-safe). Slug and text domain stay `oli-pdf-invoices-packing-slips` so existing installs keep working.
- PDF template CSS lives in `assets/css/` and is injected for Dompdf; admin CSS/JS are enqueued. No inline `<script>`, `<style>`, or `style=` attributes remain in plugin PHP.
- Output escaping wraps whole `echo` expressions (SKU, weight, tab class).

### Added
- Upgrade routine copies any leftover `olipdf_*` options onto `oli_pdf_*` then deletes the old keys, and records `oli_pdf_db_version`.
- `readme.txt` **External services** section (the plugin does not call remote APIs; Dompdf stays local).

## [0.1.1] - 2026-10-20

### Added
- Shipping label size presets: A4, Letter, 4x6, 4x4, and custom mm dimensions
- Multiple labels per sheet (columns × rows, copies per sheet)
- WordPress color pickers for template primary and secondary colors
- Credit notes for WooCommerce refunds (sequential CN- numbers, email attach on refunded-order, My Account + admin + bulk)
- Optimized product images in PDFs: local thumbnail/intermediate sizes embedded as data URIs (no remote fonts or CDN)
- Release zip build script (`bin/build-release.sh`) that vendors Dompdf so the zip works without Composer on the server
- Standalone unit tests that lock the real WebToffee 5.x option names and order meta keys
- HPOS compatibility declaration

### Fixed
- Migration now reads the real WebToffee 5.x storage:
  - Main settings: `Wf_Woocommerce_Packing_List` (legacy fallback `wf_woocommerce_packing_list`)
  - Invoice module: `wf_woocommerce_packing_list_invoice`
  - Current counter: `woocommerce_wf_Current_Invoice_number` inside the invoice option (capital C)
  - Suffix key: `woocommerce_wf_invoice_number_postfix`
  - Order invoice number: `wf_invoice_number` (legacy `_wf_invoice_number`)
  - Order invoice date: `_wf_invoice_date` as a unix timestamp
- Invoice generation statuses now use WooCommerce status slugs without the `wc-` prefix
- Settings tabs only save the visible section
- GitHub URLs updated from `bigrat95` to `olivierbigras` (wordpress.org contributor remains `bigrat95`)

### Changed
- Composer package name is `olivierbigras/oli-pdf-invoices-packing-slips`
- CI runs PHP lint, PHPCS (WordPress standard), WebToffee format tests, and a release-zip smoke check on every push/PR

## [0.1.0] - 2026-10-19

### Added
- Initial release of Oli PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels
- Sequential invoice numbering, email attachments, bulk ZIP download, My Account downloads
- Theme-overridable templates and French translation
