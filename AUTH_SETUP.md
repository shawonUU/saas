# SaaS authentication: Laravel 13 + Blade

## Changes

- Four separate session guards and providers: `web` (`users`), `authority` (`authority_users`), `shop_admin` (`business_users`), `shop_customer` (`shop_customers`).
- Shop resolution is always based on `shops.code`, not a client-submitted `shop_id`.
- Both tenant logins include `shop_id` and `approval_status` in their credential queries; guarded dashboard routes re-check `shop_id` to prevent cross-tenant access.
- Root SaaS user registration + login, **admin.saas.test** authority login only, tenant admin login only, tenant storefront registration + login.
- Shop subdomain `admin` is reserved through registration validation, Eloquent save events and shop resolution; no shop owner can register `admin.saas.test`.
- Shop creation adds `shops` and an independent owner record in `business_users` within a database transaction.
- Basic English/Bangla toggle stored in per-host session. HTML forms include CSRF tokens.
- New migration repairs the existing SQLite `users` columns and adds `business_users.role`, `business_users.user_id`, plus uniqueness of `shops.code`.
- Simple, interactive console commands to create initial authority user and a shop employee (not a public registration route).

## Existing database findings

Uploaded `database/database.sqlite` contains only the original three migration records and **does not yet have** `authority_users`, `business_users`, `shops`, `shop_customers`, `shop_categories`. Its existing `users` table was created before `code` and `approval_status` were introduced. `php artisan migrate` creates the missing tables using existing migrations, then the new migration repairs/extends the schema. No `migrate:fresh` is required.

## 1. Local .env (Laragon example)

Set in `.env` (keep the existing `APP_KEY`, database credentials and other secrets):

```env
APP_URL=http://saas.test
SAAS_DOMAIN=saas.test
SESSION_DOMAIN=null
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false
```

Use an actual domain on deployment (e.g. `saas.test`). **Do not set `SESSION_DOMAIN=.saas.test`.** Keeping a host-only session cookie is necessary for isolation between `admin.rohimstore.saas.test` and `rohimstore.saas.test`.

For Laragon, ensure virtual hosts / local DNS resolve all four hostnames to the same Laravel `public` folder:

```text
saas.test
admin.saas.test
rohimstore.saas.test
admin.rohimstore.saas.test
```

Windows hosts file cannot wildcard: add each hostname explicitly during local testing (often `127.0.0.1` for each), and configure your web server's vhosts to answer these hosts. `php artisan serve` by itself does **not** configure wildcard subdomain routing.

## 2. Install and migrate

From project root:

```bash
composer install
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=ShopCategorySeeder
php artisan authority:create
```

The authority command safely asks for a name, email, and password via your terminal. No default authority password is bundled.

## 3. End-to-end login flow

1. Browse `http://saas.test/register`; create a normal SaaS account. You land at `/dashboard`.
2. Click **Create new shop**, choose category **General**, enter `Rohim Store` and `rohimstore`. `admin` is reserved and will be rejected. Set an optional admin password. `shops` and `business_users` are created together.
3. Browse `http://admin.rohimstore.saas.test/login`; log in using the shop owner email plus the chosen admin password (or the initial SaaS password if left blank).
4. Browse `http://rohimstore.saas.test/register`; register a customer with an independent password. Customer auth does not grant admin access.
5. Browse `http://admin.saas.test/login`; log in with the terminal-created authority account. This host is only for SaaS Authority.
6. Optional employee test: `php artisan shop:employee rohimstore`, then log in to the shop admin using the new employee credentials.

## Why the sessions are separate

- Four independent Laravel guards check different tables.
- For tenant admins and customers, `shop_id` is checked during login and on every protected request.
- Cookies are host-only (`SESSION_DOMAIN=null`), so subdomains do not share session cookies.
- SaaS Authority and normal SaaS users are on **different hosts** (`admin.saas.test` and `saas.test`), each with a host-only session cookie, plus distinct Laravel guards.
- Logging out only invalidates the session on the current host.

## Approval policy used for the auth-only MVP

- Root SaaS users, new shop owners, new shops, and new customers are automatically marked `Approved` so the setup can be tried without a not-yet-built approval management UI.
- Authority accounts must be created by an operator via the terminal. Store employees are also created by terminal until Employee Management is built.
- `Pending`, `Rejected` and `Cancelled` users cannot log in. Changing an approved user's status also blocks their next protected request.
- Once an authority approval workflow is implemented, change new `shops.approval_status` from `Approved` to `Pending` and release the tenant domain after approval.

## Production checklist

- HTTPS everywhere; use `SESSION_SECURE_COOKIE=true` and `APP_DEBUG=false`.
- Ensure DNS supports shop domains and two-level admin hosts. Certificate `*.saas.test` alone does **not** cover `admin.rohimstore.saas.test`; configure TLS accordingly.
- Keep `APP_KEY` private and retain `SESSION_DOMAIN=null` (host-only). Never put `.env` or database dumps in a public repository.
- Apply `php artisan migrate` on a backup/staging copy first; ensure existing `shops.code` values are unique before applying the unique index. If an existing record already has `shops.code = admin`, rename it safely with a planned migration (do not delete it automatically); the authority host now takes precedence.
- Add email verification/password reset and MFA for authority/admin, invite-based employee creation, authorization/permissions, and shop suspension handling when those features are in scope.

## Sanity tests

```bash
php artisan route:list
php artisan test --filter=PortalAuthenticationTest
```

Tests cover SaaS vs authority guard separation, authority subdomain routes, reserved `admin` rejection, cross-shop admin/customer denial, and shop owner separate admin password.
