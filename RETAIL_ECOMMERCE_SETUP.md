# Retail & E-commerce Extension — Advanced Business Operations

This extension is written for the uploaded **app(6).zip** Laravel SaaS project. It is **not** a fresh Laravel installer. Existing project folder structure, business resolver, user guards, subscription/permissions and the Bangla/English + dark/light design system remain in use.

## Install on the existing project

1. Back up your database and existing files.
2. Put `update_retail_saas.php` next to `artisan`.
3. `php update_retail_saas.php --dry-run`
4. `php update_retail_saas.php --migrate --seed`
5. `php artisan test --filter=RetailAccountingFlowTest`
6. If supported, run the new smoke tests and confirm the business UI manually.

The updater detects files from both `app(6).zip` **and** the previously generated Retail/E-commerce update. If your copy has independent edits, it **stops** instead of overwriting those edits unless you explicitly pass `--force`. A file backup is created before changes. It does **not** back up your database. `--seed` invokes `ModuleMenuSeeder` and `SubscriptionPlanSeeder`, which may change existing category/plan mappings. Review those mappings and take a DB backup first.

## Major business flows

- **Products:** Simple or variant products (size/color optional), separate SKU, selling price, cost and tracked stock.
- **Warehouse stock:** Default Main Warehouse; per-warehouse quantities; warehouse creation; warehouse-to-warehouse transfers; transfers are stock-only, **no revenue or expense posting**, because inventory ownership and total value do not change. Existing aggregate stock is initially allocated to Main Warehouse. GRN, POS, online sales and returns use Main Warehouse; stock moved to a secondary warehouse must be transferred back before selling through these standard screens. Stock ledger retains warehouse ID.
- **Purchasing:** Draft Purchase Order → Approve → Part/Full Receive (GRN); receiving updates stock and AP/inventory journal; supplier payments; supplier return reduces available stock and credits AP for unpaid portion, and records paid-portion supplier refund to Cash/Bank. Purchases can still be received without PO.
- **Sales:** Sales orders, POS, invoices/order details, quotations (not posted until conversion), coupon discount, tax, refunds/returns, customer payments and delivery status.
- **Accounting:** Business-specific chart of accounts; automatic debit/credit posting for purchases, sales, adjustments, returns, payments, supplier refunds, cash-bank transfers and expenses. Manual multi-line journal form validates balance and disallows duplicate account rows. Standard ledger, trial balance, balance sheet, P&L and cash-flow reports remain available.
- **Marketing:** Time-bound campaign banners linked to existing coupons; consent-based incomplete-cart capture and conversion cleanup. No automatic promotional SMS/email sending is enabled.
- **E-commerce:** `/shop` public catalogue, variant-aware cart, shipping methods, COD checkout, optional SSLCommerz hosted payment, stock-backed order posting.
- **Courier:** Pathao API credentials per business, booking from online orders, shipping and consignment ID recording, refresh status. Because Pathao city/zone IDs vary, booking form requires valid IDs from the merchant environment. An unknown booking state is deliberately blocked from retry to prevent duplicate deliveries.

## URL pattern

The feature URLs follow `module/menu/child`, for example:

```text
configurations/products
purchasing/purchase-orders/po-list
purchasing/purchase-orders/new-po
purchasing/receiving/grn-list
purchasing/purchase-returns/purchase-return-list
inventory/movements/stock-transfer
settings/locations/warehouses
sales/quotations/quotation-list
finance/ledger/journal-entries
finance/ledger/journal-entries/manual
finance/banking/bank-transfers
finance/banking/bank-reconciliation
finance/expenses/expense-list
ecommerce/online-marketing/campaigns
ecommerce/online-marketing/abandoned-carts
settings/integrations/api-keys
ecommerce/online-orders/fulfillment/courier
```

## SSLCommerz setup and validation

As a store admin, visit `settings/integrations/api-keys` and enter your business merchant **Store ID** and **Store Password**. Keep Sandbox selected until you have verified a real test. The credentials are stored using Laravel's encrypted array cast; `APP_KEY` must be configured and preserved. The store checkout only offers Online Payment when the gateway is enabled.

When a user chooses online payment, the order is created with a receivable and the cart's stock is deducted. The merchant server requests a hosted checkout URL. Gateway callbacks are **never trusted** until verified through the provider's validation API; amount, currency, status, risk flag and merchant transaction ID must match. Only verified payments post debit to Gateway Clearing (1150) and credit to the customer receivable; duplicate callbacks do not double-post. Merchant bank settlement is posted separately by an authorized user through `finance/banking/bank-reconciliation`, moving the **net deposit** to Bank (1100) and the actual fee to Gateway Fees (6200). Failed payments leave unpaid orders outstanding for manual resolution. An authorized administrator can cancel an unpaid, unshipped order and reverse stock/accounting; pending gateway transactions must be verified/cleared before cancellation. Automatic timeout release is not implemented.

Official docs: https://sandbox-gw.sslcommerz.com/docs

**Important:** SSLCommerz IPN callbacks must reach the shop's publicly accessible HTTPS subdomain. Configure your merchant panel's allowed return URLs. No live merchant credentials were supplied, so end-to-end merchant validation has not been performed.

## Pathao setup

At `settings/integrations/api-keys`, provide your business Pathao merchant Client ID, Client Secret, Username, Password, Store ID, sender contact and Sandbox/Live mode. Use `ecommerce/online-orders/fulfillment/courier` to book a parcel with the provider city and zone ID. The API confirms consignment ID before the system records a shipped status. If the request times out or fails ambiguously, the shipment is `unknown` and requires checking the merchant panel before attempting further action.

Pathao API flow: issue-token → `/aladdin/api/v1/orders` → `/aladdin/api/v1/orders/{consignment_id}/info`. Merchant API permissions, address fields and courier charging rules may vary; validate with the merchant account before production use. Docs: https://pathao.com/bn/blog/api-merchant-auto-address-feature/

## Accounting safeguards

- Every Retail transaction is associated with `business_id` and its queries are scoped to the business; admin features use existing subscription/navigation permission checks.
- Never directly mutate `retail_journal_lines`, `retail_stock_movements` or aggregate variant quantities via unvalidated web requests.
- Positive and negative stock updates acquire row locks inside `DB::transaction`; transfers maintain the aggregate quantity.
- Never rerun a paid external action just because the browser timed out. Verify both external provider and internal journal first.
- Use test databases and verify financial reports and VAT specifics against your legal and operational rules before deploying live.

## Validation status and remaining production dependencies

PHP syntax checks, file manifest hashes and ZIP patch application were performed. Full Laravel feature/integration tests were **not run** in this environment because the uploaded project excludes `vendor/` and an installed application database. Actual SSLCommerz/Pathao API calls cannot be live-tested without merchant credentials. This package is an extended implementation, **not a certified production-ready ERP**. Complex business-specific areas such as full landed-cost accounting, multi-currency bank ledgers, tax return filing, payment reversal/chargeback automation, sales stock reservation timeouts, customer SMS/email campaigns and all non-retail sidebar modules require separate business-rule decisions and integration testing.
