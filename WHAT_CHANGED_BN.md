# NexaBiz SaaS — Sidebar, Category Access, Authority Control, Theme

## Uploaded project review

This ZIP contains a **Laravel 13 / Blade** application (based on `composer.json` and `resources/views`), **not a React application**. The supplied project did not include a React source tree, so this update retains the existing Blade frontend and existing portal authentication architecture. It does not change unrelated business logic.

## What was implemented

1. **Business Admin dynamic sidebar:** `modules → menus → child_menus` shown in the business admin dashboard. Navigation is filtered using the business category, authority shop overrides, active state and business-user permissions. Expandable menus and permission-checked feature URLs.
2. **Category access inheritance:** New shops use `businesses.category_id` to inherit `business_category_modules`, `business_category_menus`, `business_category_child_menus`. There is **no need to duplicate permissions at shop creation**. Changes to category defaults apply to existing shops unless a shop override exists.
3. **Owner access:** Shop creation now creates an `Owner` business role (`is_owner=1`), assigns `role_id` and `user_id` to the new business-admin account. Owner automatically sees all shop-entitled menu items; non-owners need `business_user_accesses.can_view=1`.
4. **Authority editor:** `admin.{SAAS_DOMAIN}/features` allows authority to create and update Module/Menu/Child Menu names (English/Bengali), slugs, order, icon and active status. A disabled parent disables everything below it.
5. **Category/Shop editor:** `admin.{SAAS_DOMAIN}/access` → category defaults (checkboxes) or per-shop override (`Inherit`, `Enabled`, `Disabled`). Stored in existing schema tables: `business_module_overrides`, `business_menus`, `business_child_menus`.
6. **Model classes:** Added missing Eloquent models for navigation, mapping tables, business roles and employee access, all with `protected $guarded = [];`. Existing auth models use `$guarded = [];`; password protection/hidden fields retained. `ErpCrud` generates the required unique `code` for tables using `erpCommonColumns()` when no code is provided.
7. **Seeder:** `BusinessCategorySeeder` and new `ModuleMenuSeeder` seed a small example hierarchy and default category mappings. Seeded child pages that do not exist yet display an explicit **not implemented** placeholder (no fake sales/inventory functionality).
8. **Light/Dark appearance:** Toggle in the common Blade layout, saved in `localStorage` under `nexabiz-theme`; styles for public/registration/dashboard/authority/business-admin pages. Bengali labels continue using the existing `t()` helper, navigation labels use `navName()`.
9. **Existing errors corrected:** `unique:businesss,code` → `unique:businesses,code`, Shop Owner creation now uses actual `role_id` column, migration rollback table names fixed for the relevant tables. New additive migrations introduce `business_users.user_id` and optional Bengali names.
10. **Tests:** Updated the supplied portal-auth test fixture and added `BusinessNavigationTest` covering owner category access, shop override, employee permission, authority route protection.

## Important files

- `app/Services/BusinessNavigation.php` — access computation
- `app/Http/Controllers/Store/Admin/WorkspaceController.php` — Business Admin dashboard & secured feature placeholder
- `app/Http/Controllers/Saas/Authority/FeatureController.php` — hierarchy CRUD (create/update/disable)
- `app/Http/Controllers/Saas/Authority/FeatureAccessController.php` — category and shop access
- `app/Http/Controllers/Saas/OwnerPortal/BusinessRegistration/BusinessRegistrationController.php` — owner role creation
- `resources/views/portals/partials/admin-sidebar.blade.php` — sidebar
- `resources/views/portals/partials/theme-styles.blade.php` — shared styling
- `resources/views/portals/authority-*.blade.php` — authority screens
- `database/seeders/ModuleMenuSeeder.php` — default hierarchy
- `database/migrations/2026_11_13_000001_add_business_user_owner_link.php` — owner linked to SaaS user
- `database/migrations/2026_11_13_000002_add_navigation_bengali_names.php` — translations

## Install / apply

In your existing project root, **back up your database before deployment**:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=BusinessCategorySeeder
php artisan db:seed --class=ModuleMenuSeeder
php artisan optimize:clear
```

On an **existing installation**, retain the current `.env` and **do not run** `key:generate`; the commands above to create `.env` and generate a key are **only for a fresh installation**. Install files into the existing project, run only `php artisan migrate` and explicitly the seeder(s) you need. Re-running `ModuleMenuSeeder` re-adds the built-in default category mappings; after custom authority changes, don't re-run it unless you want to restore those defaults.

Suggested checks after setup:

- Log in to `http://admin.saas.test` using an **existing authority account** (this update does not create a default superadmin password).
- Visit `/features` to inspect hierarchy; `/access` to choose category and per-shop access.
- Create a new shop on `saas.test`, select a business category, open `admin.<shop>.saas.test` and log in with its new owner account.
- Toggle a category child menu off via authority and refresh the business-admin sidebar; check that direct `/features/{childMenu}` access returns 403 when disallowed.
- Use the theme toggle to switch light/dark appearance on each portal.

## Behavior and boundaries

- The shop uses *category inheritance*, not a copy of every permission record. Empty shop override tables on a newly created shop are expected.
- A non-owner employee's permissions are read from **existing** `business_user_accesses` (`business_user_id`, `module_id`, `menu_id`, optional `child_menu_id`, `can_view`). Employee permission editing UI is **not** added here; this scope is the sidebar and authority control. For create/update/delete authorization, future business endpoints must check the relevant flags as those endpoints are implemented.
- Seeded Inventory, Sales, Team, and other pages are navigation placeholders, because the submitted project did not contain those underlying pages. Setting a `route` string on a child menu does not create a business feature controller automatically.
- Existing business-admin users created before this release may need their `user_id` and Owner `role_id` reconciled to qualify for automatic Owner access. **Newly registered shops are handled.**
- `.env`, log output, runtime caches, database contents, dependencies and build artifacts were **excluded** from the final ZIP to protect credentials and keep the source clean.
- PHP source lint was run. Full Laravel migration/feature tests were **not run** in this environment because the uploaded ZIP did not include `vendor/` and Composer was not available. Run `php artisan test` locally after `composer install`.
