# Testing Guide for Oli PDF Invoices v0.1.1

This document provides comprehensive testing instructions for the WooCommerce PDF plugin.

## Pre-Installation Setup

### System Requirements
- WordPress 6.5 or higher (tested up to 7.1)
- WooCommerce 9.0 or higher (tested up to 11.0)
- PHP 7.4 or higher
- Composer (for dependency installation)

### Installing Dependencies

**IMPORTANT**: Before activating the plugin, you must install Composer dependencies:

```bash
cd wp-content/plugins/oli-pdf-invoices-packing-slips
composer install --no-dev
```

This will install Dompdf, the GPL-compatible PDF generation library.

## Installation Testing

1. **Clone/Download Plugin**
   ```bash
   git clone [repo-url]
   cd oli-pdf-invoices-packing-slips
   composer install --no-dev
   ```

2. **Upload to WordPress**
   - Copy plugin folder to `/wp-content/plugins/`
   - Or upload ZIP through WordPress admin

3. **Activate Plugin**
   - Go to Plugins > Installed Plugins
   - Click "Activate" on "Oli PDF Invoices..."
   - Verify no errors appear
   - Check WooCommerce > PDF Documents menu item exists

## Feature Testing

### 1. Invoice Numbering

**Test Sequential Numbering**
```
Steps:
1. Go to WooCommerce > PDF Documents
2. Set prefix: "INV-"
3. Set suffix: "-FR"
4. Set padding: 6
5. Set start number: 100
6. Save settings

7. Create test order #1001
8. Set status to Processing
9. Verify invoice number: INV-000100-FR

10. Create test order #1002
11. Set status to Completed
12. Verify invoice number: INV-000101-FR

Expected: Sequential numbering with proper padding
```

**Test Status-Based Generation**
```
Steps:
1. Set "Generate on Status" to only "Completed"
2. Create order, set to Processing
3. Verify no invoice generated
4. Change order to Completed
5. Verify invoice now generated

Expected: Invoice only created on Completed status
```

### 2. PDF Generation

**Test All Document Types**
```
For each document type (invoice, packing slip, delivery note, shipping label):

1. Create test order with:
   - Multiple products
   - Different quantities
   - Tax enabled
   - Shipping fee
   - Customer note

2. Open order edit screen
3. Find "PDF Documents" meta box
4. Click "View [Document Type]"
5. Verify PDF opens in browser
6. Click "Download [Document Type]"
7. Verify PDF downloads

Expected: All 4 document types generate correctly
```

**Test Invoice Content**
```
Verify invoice includes:
- ✓ Company logo (if set)
- ✓ Company name and address
- ✓ VAT/Tax numbers (if set)
- ✓ Invoice number
- ✓ Invoice date
- ✓ Order number and date
- ✓ Customer billing address
- ✓ Product list with quantities and prices
- ✓ Subtotal, shipping, tax, total
- ✓ Payment method
- ✓ Customer note
- ✓ Footer text
```

### 3. Email Attachments

**Test Auto-Attach to Emails**
```
Setup:
1. Go to PDF Documents settings
2. Select "Customer Invoice" and "Completed Order" for email attachment
3. Save settings

Test:
1. Create new order
2. Set to Completed status
3. Check email sent to customer
4. Verify invoice PDF attached
5. Verify invoice number matches order

Alternative Test (without actual email):
1. Add filter to capture attachments:
   add_filter('woocommerce_email_attachments', function($attachments) {
       error_log('Attachments: ' . print_r($attachments, true));
       return $attachments;
   }, 999, 3);
2. Check error log for attachment paths
```

### 4. Customer Downloads

**Test My Account Access**
```
Setup:
1. Create customer account: test@example.com
2. Create order for this customer
3. Generate invoice (set to Completed)
4. Logout from admin

Test:
1. Login as customer
2. Go to My Account > Orders
3. Verify "Download Invoice" button appears
4. Click button
5. Verify PDF downloads
6. Verify content matches admin version

Security Test:
1. Copy download URL
2. Logout
3. Try URL again
4. Expected: Access denied or invalid token
```

### 5. Bulk Actions

**Test Bulk Download**
```
Steps:
1. Create 5 test orders
2. Generate invoices for all (set to Completed)
3. Go to WooCommerce > Orders
4. Select all 5 orders (checkboxes)
5. Choose "Download Invoices (PDF)" bulk action
6. Click Apply
7. Verify ZIP file downloads
8. Extract ZIP
9. Verify 5 PDF files present
10. Verify filenames correct (invoice-[order-number].pdf)

Repeat for:
- Packing Slips
- Delivery Notes
```

### 6. Template Customization

**Test Company Information**
```
Steps:
1. Go to PDF Documents > Company tab
2. Upload logo image
3. Enter company name: "ACME Corp"
4. Enter address: "123 Main St\nParis, France"
5. Enter VAT: "FR12345678"
6. Save settings

7. Generate invoice
8. Verify all info appears in PDF
9. Verify logo displays correctly
```

**Test Show/Hide Fields**
```
Go to Template tab and test each option:

1. Uncheck "Show SKU"
   - Generate PDF
   - Verify SKU column hidden

2. Uncheck "Show Weight"
   - Generate packing slip
   - Verify weight column hidden

3. Uncheck "Show Tax Breakdown"
   - Generate invoice
   - Verify single tax line instead of breakdown

4. Uncheck "Show Shipping"
   - Generate invoice
   - Verify shipping line hidden

5. Uncheck "Show Payment Method"
   - Generate invoice
   - Verify payment method not shown

6. Uncheck "Show Customer Note"
   - Generate invoice
   - Verify note section hidden

Expected: Each toggle correctly shows/hides field
```

### 7. Migration from WebToffee

**Test Migration Tool**
```
Setup (Optional - if you have WebToffee data):
1. Install WebToffee Print Invoices plugin
2. Configure settings (company info, prefix, etc.)
3. Generate invoices on some orders
4. Note current invoice number sequence

Migration Test:
1. Go to PDF Documents > Migration tab
2. Click "Dry Run (Preview)"
3. Verify preview shows:
   - Settings to be imported
   - Number of orders with invoices
4. Click "Run Migration"
5. Verify success message

6. Check settings imported:
   - Company name matches
   - Logo URL matches
   - Invoice prefix matches
   - Current number continues sequence

7. Check orders with WebToffee invoices:
   - Open old order
   - Verify invoice number preserved
   - Verify can generate PDF with old number

Expected: All settings and numbers migrated without data loss
```

### 8. HPOS Compatibility

**Test with High-Performance Order Storage**
```
Setup:
1. Go to WooCommerce > Settings > Advanced > Features
2. Enable "High-performance order storage"
3. Enable compatibility mode (if available)
4. Save settings

Test:
1. Create new order (stored in custom tables)
2. Verify invoice generated
3. Verify meta box appears in order edit screen
4. Verify bulk actions work
5. Verify My Account downloads work

Expected: Everything works identically with HPOS enabled
```

### 9. Multi-Currency & Multilingual

**Test Multi-Currency**
```
Setup:
1. Install WPML Currency or similar
2. Create products in EUR and USD
3. Create order in USD

Test:
1. Generate invoice
2. Verify currency symbol: $
3. Verify amounts in USD

4. Create order in EUR
5. Generate invoice
6. Verify currency symbol: €
7. Verify amounts in EUR

Expected: Currency displays correctly per order
```

**Test French Translation**
```
Setup:
1. Go to Settings > General
2. Set Site Language to "Français"
3. Save settings

Test:
1. Go to WooCommerce > Documents PDF
2. Verify all labels in French
3. Generate invoice
4. Verify PDF labels in French

Expected: Full French translation
```

### 10. Template Overrides

**Test Theme Override**
```
Steps:
1. Copy plugin template:
   From: wp-content/plugins/oli-pdf-invoices-packing-slips/templates/invoice.php
   To: wp-content/themes/[your-theme]/oli-pdf-templates/invoice.php

2. Edit copied template:
   - Change title to "CUSTOM INVOICE"
   - Add custom text

3. Generate invoice
4. Verify custom changes appear

Expected: Theme template overrides plugin template
```

### 11. Security Testing

**Test Permission Checks**
```
1. Login as Shop Manager (not Administrator)
   - Verify can access PDF Documents settings
   - Verify can generate PDFs

2. Login as Editor
   - Verify cannot access PDF Documents
   - Verify cannot download PDFs

3. Login as Customer
   - Verify can only download own invoices
   - Try to access another customer's order URL
   - Expected: Access denied
```

**Test Token Security**
```
1. As customer, get invoice download URL
2. Note the token parameter
3. Change token value slightly
4. Try to access
5. Expected: Access denied or "Invalid token"
```

### 12. Performance Testing

**Test with Many Orders**
```
Steps:
1. Create 100+ test orders (use bulk order creation plugin if needed)
2. Generate invoices for all
3. Test bulk download
4. Verify:
   - No timeout errors
   - ZIP contains all PDFs
   - Memory usage acceptable

Expected: Plugin handles large batches
```

## Code Quality Testing

### PHP Lint Check
```bash
cd wp-content/plugins/oli-pdf-invoices-packing-slips
find . -name "*.php" ! -path "./vendor/*" -exec php -l {} \;
```
Expected: No syntax errors

### PHPCS Check
```bash
composer global require squizlabs/php_codesniffer
composer global require wp-coding-standards/wpcs
phpcs --standard=WordPress .
```
Expected: No coding standards violations

### Plugin Check (PCP)
```
1. Install "Plugin Check" plugin from wordpress.org
2. Run check on Oli PDF Invoices
3. Review results
4. Expected: All checks pass
```

## Common Issues & Troubleshooting

### Issue: PDFs not generating
**Solution**: Check that Composer dependencies installed
```bash
cd wp-content/plugins/oli-pdf-invoices-packing-slips
composer install --no-dev
```

### Issue: Logo not displaying
**Solution**: 
- Verify logo URL is absolute
- Check image file exists and accessible
- Test with different image format (PNG, JPG)

### Issue: Email attachment not working
**Solution**:
- Check email type selected in settings
- Verify order status triggers invoice generation
- Check temp directory writable: wp-content/uploads/oli-pdf-temp/

### Issue: Migration not finding WebToffee data
**Solution**:
- Verify WebToffee plugin was active
- Check if option exists: wp_options > wf_woocommerce_packing_list
- Run dry run first to see what will be imported

## Success Criteria

Plugin is ready for production when:
- ✅ All 4 document types generate correctly
- ✅ Sequential numbering works
- ✅ Email attachments work
- ✅ Customer downloads work
- ✅ Bulk actions work
- ✅ Migration from WebToffee works
- ✅ HPOS compatible
- ✅ No PHP errors
- ✅ No JavaScript console errors
- ✅ PHPCS passes
- ✅ Plugin Check passes
- ✅ Security tests pass

## Reporting Issues

When reporting issues, please include:
1. WordPress version
2. WooCommerce version
3. PHP version
4. Active theme
5. Other active plugins
6. Steps to reproduce
7. Expected vs actual behavior
8. Error logs if available

---

**Happy Testing!** 🎉
