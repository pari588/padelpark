# PP.PariToshAjmera.com - Deployment Summary

**Date:** January 20, 2026
**Server:** pp.paritoshajmera.com
**Project:** Padel Park / Bombay Engineering Syndicate

---

## 1. Initial Setup & xAdmin Fixes

### Issues Fixed:
- **FollowSymLinks Error:** Removed `+FollowSymLinks` from `.htaccess` (not allowed on server)
- **Missing Constants:** Added `COREURL`, `LIBURL`, `LIBDIR` to `config.inc.php`
- **Session Start:** Added `session_start()` to `config.inc.php`
- **Login Token Issue:** Fixed JWT token validation in `xadmin/core-admin/ajax.inc.php` by adding `$ignoreToken = true` for login action

### Files Modified:
- `/home/padelpark/public_html/.htaccess`
- `/home/padelpark/public_html/config.inc.php`
- `/home/padelpark/public_html/xadmin/core-admin/ajax.inc.php`

---

## 2. Full Site Extraction from besss.zip

Extracted the following from the uploaded `besss.zip` (515MB):

### Folders Extracted:
- `xadmin/` - Admin panel (101 modules)
- `core/` - Core PHP files
- `skypadel/` - Sky Padel Client Portal
- `paynplay/` - Pay & Play Portal
- `ipt/` - Indian Padel Tournament Portal
- `studentfeedback/` - Student Feedback Portal
- `xsite/mod/vendorportal/` - Vendor Management Portal

### Database Restored:
- Restored from `database_backup_20260120.sql`

---

## 3. Configuration Updates

### config.inc.php
```php
$DBHOST = "localhost";
$DBUSER = "padelpark";
$DBPASS = "Padel@2024#Secure";
$DBNAME = "padelpark_bombayengg";

define('ROOTPATH', '/home/padelpark/public_html');
define('SITEURL', 'https://pp.paritoshajmera.com/xsite');
define('ADMINURL', 'https://pp.paritoshajmera.com/xadmin');
```

### skypadel/core/config.php
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'padelpark');
define('DB_PASS', 'Padel@2024#Secure');
define('DB_NAME', 'padelpark_bombayengg');
define('SITE_URL', 'https://pp.paritoshajmera.com/skypadel');
define('ADMIN_URL', 'https://pp.paritoshajmera.com/xadmin');
```

---

## 4. URL Routing (.htaccess)

### Final .htaccess Configuration:
```apache
<IfModule mod_rewrite.c>
    Options -Indexes
    RewriteEngine On
    RewriteBase /

    # Pay & Play Portal
    RewriteCond %{REQUEST_URI} ^/paynplay(/.*)?$
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^paynplay(/)?$ paynplay/index.php [L]

    # Sky Padel Client Portal
    RewriteCond %{REQUEST_URI} ^/skypadel(/.*)?$
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^skypadel(/)?$ skypadel/index.php [L]

    # IPT - Indian Padel Tournament
    RewriteCond %{REQUEST_URI} ^/ipt(/.*)?$
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ipt(/)?$ ipt/index.php [L]

    # Student Feedback Portal
    RewriteCond %{REQUEST_URI} ^/studentfeedback(/.*)?$
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^studentfeedback(/)?$ studentfeedback/index.php [L]

    # xAdmin
    RewriteRule ^xadmin/?$ xadmin/index.php [L]
    RewriteCond %{REQUEST_URI} ^/xadmin/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^xadmin/(.*)$ xadmin/index.php?request=$1 [QSA,L]

    # Vendor Portal - Explicit Routes
    RewriteRule ^vendorportal/?$ xsite/mod/vendorportal/x-login.php [L]
    RewriteRule ^vendorportal/login/?$ xsite/mod/vendorportal/x-login.php [L]
    RewriteRule ^vendorportal/register/?$ xsite/mod/vendorportal/x-registration.php [L]
    RewriteRule ^vendorportal/dashboard/?$ xsite/mod/vendorportal/x-dashboard.php [L]
    RewriteRule ^vendorportal/profile/?$ xsite/mod/vendorportal/x-profile.php [L]
    RewriteRule ^vendorportal/documents/?$ xsite/mod/vendorportal/x-documents.php [L]
    RewriteRule ^vendorportal/rfq/?$ xsite/mod/vendorportal/x-rfq-list.php [L]
    RewriteRule ^vendorportal/rfq-list/?$ xsite/mod/vendorportal/x-rfq-list.php [L]
    RewriteRule ^vendorportal/quotes/?$ xsite/mod/vendorportal/x-quotes.php [L]
    RewriteRule ^vendorportal/quote-view/?$ xsite/mod/vendorportal/x-quote-view.php [L]
    RewriteRule ^vendorportal/quote-submit/?$ xsite/mod/vendorportal/x-quote-submit.php [L]
    RewriteRule ^vendorportal/orders/?$ xsite/mod/vendorportal/x-orders.php [L]
    RewriteRule ^vendorportal/forgot-password/?$ xsite/mod/vendorportal/x-forgot-password.php [L]
    RewriteRule ^vendorportal/reset-password/?$ xsite/mod/vendorportal/x-reset-password.php [L]
    RewriteRule ^vendorportal/logout/?$ xsite/mod/vendorportal/x-logout.php [L]

    # Fallback to xsite
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ xsite/index.php?request=$1 [QSA,L]
</IfModule>
```

---

## 5. Vendor Portal Fixes

### Issue: SITEURL Mismatch
- `SITEURL` was defined as `https://pp.paritoshajmera.com/xsite`
- Vendor Portal is at `/vendorportal` (not `/xsite/vendorportal`)
- AJAX calls were going to wrong URL causing "Connection Error"

### Solution: Added VP_BASEURL Constant

**x-vendorportal.inc.php:**
```php
// Define vendor portal base URL (root URL without /xsite)
if (!defined('VP_BASEURL')) {
    define('VP_BASEURL', 'https://pp.paritoshajmera.com');
}
```

### Files Updated with VP_BASEURL:
- `x-vendorportal.inc.php` - Added constant, updated `vpRequireAuth()` and `vpLogout()`
- `x-vendorportal.php` - Updated redirects
- `x-login.php` - Updated AJAX URL, redirects, and links
- `x-header.php` - Updated all navigation links (16 occurrences)
- `x-dashboard.php`
- `x-rfq-list.php`
- `x-quote-view.php`
- `x-quote-submit.php`
- `x-quotes.php`
- `x-orders.php`
- `x-profile.php`
- `x-documents.php`
- `x-registration.php`
- `x-forgot-password.php`
- `x-reset-password.php`

---

## 6. Portal Access URLs

| Portal | URL |
|--------|-----|
| xAdmin | https://pp.paritoshajmera.com/xadmin |
| Sky Padel | https://pp.paritoshajmera.com/skypadel |
| Pay & Play | https://pp.paritoshajmera.com/paynplay |
| Vendor Portal | https://pp.paritoshajmera.com/vendorportal |
| IPT | https://pp.paritoshajmera.com/ipt |
| Student Feedback | https://pp.paritoshajmera.com/studentfeedback |
| Main Site | https://pp.paritoshajmera.com/xsite |

---

## 7. Login Credentials

### Vendor Portal
- **URL:** https://pp.paritoshajmera.com/vendorportal/login
- **Email:** `vendor@test.com`
- **Password:** `password`

### Pay & Play Portal
- Check `mx_pnp_staff` table for staff credentials

---

## 8. Database Tables

### Vendor Portal Tables:
- `mx_vendor` - Vendor master
- `mx_vendor_portal_user` - Portal users
- `mx_vendor_onboarding` - Vendor onboarding data
- `mx_vendor_category` - Vendor categories
- `mx_vendor_document` - Uploaded documents
- `mx_vendor_rfq` - Request for Quotations
- `mx_vendor_rfq_item` - RFQ line items
- `mx_vendor_quote` - Vendor quotes
- `mx_vendor_quote_item` - Quote line items
- `mx_vendor_approval_log` - Approval history
- `mx_vendor_info_request` - Information requests

### IPT Tables:
- `mx_ipt_tournament` - Tournaments
- `mx_ipt_category` - Tournament categories
- `mx_ipt_tournament_category` - Tournament-category mapping
- `mx_ipt_participant` - Registered participants
- `mx_ipt_fixture` - Match fixtures

---

## 9. Key Technical Notes

### Authentication Flow:
- **xAdmin:** JWT token-based authentication
- **Vendor Portal:** Session-based with bcrypt password hashing
- **Pay & Play:** Session-based authentication

### Password Hashing:
- Uses PHP `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)
- Test password hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` = "password"

### Vendor Portal Login Requirements:
- User must exist in `mx_vendor_portal_user` with `isActive=1` and `status=1`
- Linked vendor must exist in `mx_vendor_onboarding` with `approvalStatus='Approved'`

---

## 10. Pending/Future Tasks

- [ ] Verify all portal CSS/JS assets loading correctly
- [ ] Test vendor registration flow
- [ ] Test quote submission workflow
- [ ] Verify email notifications (if configured)
- [ ] Set up SSL certificate (currently has issues with curl)
- [ ] Configure production email settings

---

## Summary

Successfully deployed the complete Padel Park / Bombay Engineering site from local development (`localhost/bes`) to production server (`pp.paritoshajmera.com`). All major portals are accessible and functional:

1. **xAdmin** - Working with updated design
2. **Sky Padel** - Working
3. **Pay & Play** - Working
4. **Vendor Portal** - Working (login tested successfully)
5. **IPT** - Deployed
6. **Student Feedback** - Deployed

The main fixes involved correcting URL paths that were hardcoded for localhost, fixing JWT token validation for admin login, and creating proper URL rewrite rules for each portal.
