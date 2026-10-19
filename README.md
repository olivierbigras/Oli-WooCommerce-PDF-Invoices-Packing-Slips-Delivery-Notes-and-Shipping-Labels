# Oli PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels for WooCommerce

A comprehensive WooCommerce plugin for generating professional PDF invoices, packing slips, delivery notes, and shipping labels.

## Features

- **Sequential Invoice Numbering** - Customizable prefix, suffix, padding, and start number
- **Auto Email Attachments** - Attach invoices to WooCommerce customer emails
- **Multiple Document Types** - Invoices, packing slips, delivery notes, and shipping labels
- **Bulk Operations** - Download multiple PDFs as ZIP from orders list
- **Customer Downloads** - Let customers download invoices from My Account
- **Template Customization** - Logo, company info, VAT/tax numbers, colors, show/hide fields
- **Theme Overrides** - Override templates in your theme folder
- **Multi-Currency** - Displays order currency correctly
- **RTL Support** - Full right-to-left language support
- **HPOS Compatible** - Works with WooCommerce High-Performance Order Storage
- **Migration Tool** - Import from WebToffee Print Invoices plugin

## Installation

### From GitHub

1. Clone this repository or download the ZIP
2. Run `composer install --no-dev` to install dependencies
3. Upload to `/wp-content/plugins/oli-pdf-invoices-packing-slips/`
4. Activate through WordPress admin
5. Configure at WooCommerce > PDF Documents

### Requirements

- WordPress 6.5 or higher
- WooCommerce 9.0 or higher
- PHP 7.4 or higher

## Development

### Setup

```bash
composer install
```

### Code Quality

Run PHP lint:
```bash
find . -name "*.php" ! -path "./vendor/*" -exec php -l {} \;
```

Run PHPCS:
```bash
./vendor/bin/phpcs
```

### Translations

The plugin is translation-ready with:
- POT file for translators
- French translation included
- Compatible with WPML and TranslatePress

## Template Override

Copy templates from `templates/` to `yourtheme/oli-pdf-templates/`:

- `invoice.php`
- `packing-slip.php`
- `delivery-note.php`
- `shipping-label.php`

## Migration from WebToffee

1. Go to WooCommerce > PDF Documents > Migration
2. Run "Dry Run (Preview)" to see what will be imported
3. Click "Run Migration" to import settings and invoice numbers
4. Existing invoice numbers are preserved

## Hooks & Filters

### Actions

- `oli_pdf_invoice_generated` - Fired when an invoice is generated

### Filters

All standard WordPress filters apply to content.

## Credits

**Author:** Olivier Bigras  
**Website:** https://olivierbigras.com/  
**License:** GPL-2.0-or-later

## Support

For issues and feature requests, please use the GitHub issue tracker.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.
