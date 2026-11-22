# NexaBiz SaaS — marketplace storefront V2

Base: `app(9).zip` after applying both earlier patches:
1. `nexabiz_business_ecommerce_patch.php --apply`
2. `nexabiz_chinaonlinebd_ui_patch.php --apply`

## Install
Copy `nexabiz_china_marketplace_v2_patch.php` next to `artisan` in Laravel project root. Back up database first.

```
php nexabiz_china_marketplace_v2_patch.php --dry-run
php nexabiz_china_marketplace_v2_patch.php --apply
composer dump-autoload
php artisan migrate --force
php artisan storage:link
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

If local MySQL is not running, do not run migration until MySQL connects. `php artisan optimize:clear` can fail if CACHE_STORE=database and MySQL is offline; this patch does not depend on that command.

## What's new
- Fix `promo.blade.php: Undefined variable $brandSettings`: promo receives its own business-scoped settings.
- Fixed desktop category rail throughout storefront; nested expandable subcategories; compact pink search header and marketplace section cards.
- Full responsive homepage, catalog, product gallery and variant chips, product suggestions, cart, checkout, footer, wishlist integration, dark/light and Bangla/English.
- Separate `/cart` (selection, quantities, removal) and `/checkout` (customer details, address, shipping, COD/SSLCommerz, coupons) pages.
- `/checkout/quote` computes coupon and shipping securely per business on server. This is a preview only; final order uses existing `Sales::create` validation.
- Admin General Settings now supports upload of **secondary_banner** alongside existing main banner/logo/favicon.
- Product admin supports **sub_category_id** selection, correctly limited to selected category and tenant.
- Product admin also accepts up to 80 specifications as `Attribute | Value` lines, displayed in the product details table.
- Three additive migrations. Existing order/payment accounting logic is retained. No fake discount or sold numbers are seeded.

## Business controls
- Main & secondary banners: Business Admin > Settings > Business > General Settings. Suggested image dimensions 1200×330 and 600×330, respectively.
- Add products/variants and images: Business Admin > Configurations > Products > Manage.
- Categories/subcategories from existing library; subcategory must be assigned on product form to filter products by it.
- Storefront routes: `/`, `/shop`, `/product/{product}`, `/cart`, `/checkout`, `/wishlist`.

## Notes
- The design follows the reference's layout and interaction but uses business-owned assets. This patch does **not** include the reference company's logo, banners or products.
- `--rollback` restores files from installer backups. It does not reverse database migrations or delete uploaded assets. Roll back database fields manually after confirming data is no longer needed.
- A Laravel/MySQL end-to-end test must be run against your actual business database (including quantity/stock, coupons, payment and subdomains). Source ZIP did not contain vendor dependencies or database.
