# NexaBiz SaaS — Ecommerce demo catalog seeder

## What's included
- **17 categories, 102 subcategories, 204 sample products, 306 stocked variants**.
- 42 local JPEG sample product images generated for this demo, plus 2 sample promotional banners (no runtime image downloads or external URLs).
- Individual seeder classes for taxonomy, media, products and orchestration.
- Business-scoped files stored under `storage/app/public/businesses/{id}/products/demo/` and banners under `businesses/{id}/demo-banners/`.
- Each sample product is active, has a SKU, description, MRP, display price, image, subcategory, a variant, opening stock and a balanced opening stock journal.
- Product names contain `Classic Edition` or `Premium Collection` and descriptions identify **demo-only** merchandise. Photos are illustrative, not genuine inventory.

## Requirements
1. Previous ecommerce patches must already be installed, including the China marketplace V2 patch.
2. Your MySQL service must be running and migrations applied (`php artisan migrate --force`).
3. Have an existing **test business**. Avoid seeding on a real live merchant account.

## Install
Copy `nexabiz_ecommerce_demo_seed_patch.php` beside `artisan`.

```
php nexabiz_ecommerce_demo_seed_patch.php --dry-run
php nexabiz_ecommerce_demo_seed_patch.php --apply
composer dump-autoload
php artisan migrate --force
php artisan storage:link
php artisan ecommerce:demo-businesses
php artisan ecommerce:seed-demo 1
php artisan view:clear
```

Replace `1` with the actual business ID reported by `ecommerce:demo-businesses`.

On a production environment this demo command refuses to run unless you explicitly add `--force`. **Do not use `--force` for a business with real orders or stock.** The starter data represents fictional products and physical stock, and could otherwise be bought by customers.

Standard seeder alternative:
```
# PowerShell
$env:ECOMMERCE_DEMO_BUSINESS_ID="1"
php artisan db:seed --class=EcommerceDemoSeeder --force
Remove-Item Env:ECOMMERCE_DEMO_BUSINESS_ID
```

## How it behaves
- Re-running the seed command skips the existing `DEMO-C...` product SKUs, does not overwrite merchant records or reset stocks, and does not duplicate their opening journal entries.
- Existing categories/brands/sizes/colors with the same name in this business are reused.
- Each business has its own categories, sample products and image copies.
- Empty promotional banner fields are filled with demo images, but existing uploaded banners are not replaced. Logo and favicon are left unchanged.
- Seeding adds opening warehouse stock, stock movements and balanced accounting entries. Since this is demo accounting data, **use a non-live tenant**.
- No false sold counts or past orders are generated. Sold counts will appear after genuine test orders in that business.

## Restore
```
php nexabiz_ecommerce_demo_seed_patch.php --rollback
```
Rollback only restores the installed *source files* and removes newly installed asset files from the codebase. It does **not** remove seeded DB rows, journals, stock or copied images. Back up your test database before seeding. To revert actual demo data safely, restore that DB backup or use a fresh test tenant; do not manually delete products that have orders.

For Windows local test, make sure Laragon MySQL runs. If `php artisan optimize:clear` fails with `SQLSTATE[HY000] [2002]` due to database-backed cache, use `php artisan view:clear` and `php artisan config:clear` after starting MySQL, or configure `CACHE_STORE=file` in `.env`.
