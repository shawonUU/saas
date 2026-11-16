# Category-Based Navigation and Basic Free Subscription

## Access policy

**Global active/approved Module, Menu, Child Menu** → **Business Category mapping** → **Active, unexpired Business Subscription** (matching category, enabled Plan feature, enabled Business Subscription snapshot feature) → **Business override** (may disable, never grant a missing earlier feature) → **User access** (owner bypasses only the last user permission check).

The sidebar, direct feature URLs, and dedicated Users/Access-Control endpoints use `App\Services\BusinessNavigation`.
The authorization editor uses its business-level tree, without inheriting the editor's user permissions.

## Data tables

- Global: `modules`, `menus`, `child_menus`
- Category: `business_category_modules`, `business_category_menus`, `business_category_child_menus`
- Basic Free Plan per category: `subscription_plans` (`<category-slug>-basic`), `subscription_plan_modules`, `subscription_plan_menus`, `subscription_plan_child_menus`
- Business active subscription: `business_subscriptions`
- Snapshot of subscription entitlements: `business_subscription_features`
- Business-specific overrides: `business_modules`, `business_menus`, `business_child_menus` (nullable `business_subscription_id` for legacy compatibility; overrides do not depend on it)
- Staff access: `business_user_accesses`

## Installation (development)

```bash
php artisan migrate
php artisan db:seed --class=ModuleMenuSeeder
php artisan db:seed --class=SubscriptionPlanSeeder
php artisan optimize:clear
php artisan test --filter=BusinessNavigationTest
```

Or run `php artisan db:seed` (DatabaseSeeder invokes ModuleMenuSeeder and SubscriptionPlanSeeder in order).

**Important:** Back up your real database before migrating. Do not use `migrate:fresh` on production. The old migration definitions are repaired for *fresh* databases, and an additive upgrade migration adapts tables already created by the previous definitions. Existing rows are not intentionally truncated. If you have manually customized the table layouts, review the upgrade migration before executing it.

## Behaviour

- `ModuleMenuSeeder` includes the Configurations module and its 12 direct menus in the common profile of every seeded category. It also adds them to **pre-existing approved categories**.
- `SubscriptionPlanSeeder` creates one free Basic plan **per active approved category**. It copies only that category's assigned module/menu/child-menu IDs into the three plan tables, is re-runnable, and synchronizes active Basic subscriptions.
- On registration, `BusinessRegistrationController` creates the business, owner role/user, active non-expiring Basic subscription and its `business_subscription_features` snapshot in one transaction.
- After an authority updates a category's mapping, `FeatureAccessController` synchronizes that category's Basic plan and existing active Basic subscription snapshots in the same transaction.
- A feature not in the Plan or snapshot is *denied*, including for the owner and even if a business override enables it.
- A parent menu that has children in the Global definition never becomes a direct-link menu just because its children were denied.
- No subscription or expired/canceled/wrong-category subscription = empty navigation. Users cannot browse those features by typing a URL.

## Future paid plans

Add a `subscription_plans` row with `business_category_id`, add category-specific rows into the three plan feature tables, and activate a BusinessSubscription. Call `BusinessSubscriptionService::syncSubscriptionFeatures($subscription)` when activating/changing a paid subscription. This does not add a plan management UI or payment gateway.
