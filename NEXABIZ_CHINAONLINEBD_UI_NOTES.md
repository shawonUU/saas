# NexaBiz Storefront UI Update — ChinaOnlineBD-inspired

This patch is an incremental update **after** `nexabiz_business_ecommerce_patch.php` (app(9).zip snapshot). It does not change Business Admin folder hierarchy or replace back-office routes.

Changes:
- Marketplace-style category rail, search header, pink promotion surfaces, circular live category tiles, compact product cards, mobile bottom menu.
- Homepage, listing, product details, cart, wishlist and shared layout continue to use the existing real database and checkout workflow.
- Display **real sold quantities** from confirmed/completed order items, excluding returned quantities. No invented ratings or counts.
- Optional compare-at MRP in Business Admin > Products; percent-off labels only when a positive compare-at price exceeds a product's visible current variant price. MRP does not alter the charged price. One migration.
- Business-owned banner/logo remain sourced from General Settings, not copied from the reference site.
- Bangla/English and light/dark selectors remain in shared header; category and navigation responsive on mobile.
- Restore shopping cart local state on page views; order-success page removes the cart only after order confirmation view is displayed.

Install after `--apply`:
```
composer dump-autoload
php artisan migrate --force
php artisan view:clear
php artisan route:clear
```

Review on local development business subdomain and check responsive 375px/768px/1366px layouts. A real MySQL database and Laravel vendor installation are needed for end-to-end testing.

Rollback of file changes: `php nexabiz_chinaonlinebd_ui_patch.php --rollback`. Migrations are **not** automatically reverted; only roll back a migration after checking its impact on any entered MRP values.
