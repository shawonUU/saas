# NexaBiz SaaS upgrade (source: app(9).zip)

## Scope
- New admin Settings > Business > General Settings / Branding, tenant-scoped, permission-checked, with image uploads, company information, storefront tagline, theme colour, favicon and homepage banner.
- Storefront home is no longer a hardcoded demo catalog: product data, category filtering, product detail variant/stock selection, cart, wishlist, shipping/coupon/checkout and bilingual labels; order posting continues through the existing Sales service and SSLCommerz integration.
- Product and variant images are uploaded to `storage/app/public`, not stored as remote URLs.
- Searchable native <select> enhancements revised for dynamic fields; store filters and checkout include search.
- Individual MVC files grouped by relevant module/menu/child in new and relocated features. Existing unrelated Controllers/Models retained to avoid unsafe business-logic rewrites.
- Idempotent BusinessLibrarySeeder: units, sizes, colours, categories, generic brand, zero-rate tax, BDT, payment and delivery. Uses tenant-specific codes. Existing navigation seeder remains authoritative for modules/menus/children.

## Install
1. Backup database and uploads separately. Place PHP patch in Laravel root.
2. `php nexabiz_business_ecommerce_patch.php --dry-run`
3. `php nexabiz_business_ecommerce_patch.php --apply`
4. `composer dump-autoload`
5. `php artisan migrate --force`
6. `php artisan storage:link`
7. `php artisan db:seed --class=BusinessLibrarySeeder --force` (only if you want example library defaults)
8. `php artisan config:clear && php artisan view:clear && php artisan route:clear`

If MySQL is off: start Laragon first; for cache driver use CACHE_STORE=file and clear config. `optimize:clear` does not work with a database cache if DB is unreachable.

Rollback patch files: `php nexabiz_business_ecommerce_patch.php --rollback`. Database migrations and uploaded assets are not automatically reverted; review before `migrate:rollback`.

## Important limitations
- Guest wishlist is local-browser storage (not customer cloud sync).
- Prices and stock in cart are client previews; server validates authoritative totals and quantities on checkout.
- Existing unrelated admin controllers still use their original directories. A full hierarchy relocation requires a separate, tested migration across routes, all cross-class imports, database models, and views.
- This patch is based on the uploaded `app(9).zip` snapshot; run in staging and test with real DB data before production.
