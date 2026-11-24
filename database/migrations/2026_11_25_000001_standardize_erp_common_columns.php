<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    private const TABLES = ['audit_logs', 'purchase_orders', 'purchase_order_items', 'po_receipts', 'warehouses', 'warehouse_stocks', 'stock_transfers', 'stock_transfer_items', 'purchase_returns', 'quotations', 'quotation_items', 'manual_journals', 'cash_transfers', 'expenses', 'campaigns', 'abandoned_carts', 'integrations', 'gateway_payments', 'courier_shipments', 'business_brand_settings', 'products', 'variants', 'stock_movements', 'accounts', 'journals', 'journal_lines', 'discounts', 'orders', 'order_items', 'purchases', 'purchase_items', 'returns', 'return_items', 'payments'];
    public function up(): void {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) continue;
            $existing = Schema::getColumnListing($table);
            Schema::table($table, fn (Blueprint $t) => $t->erpCommonColumns($existing, true));
            // Keep existing semantic codes and document numbers unchanged.
            if (in_array($table, ['accounts','discounts','warehouses'], true)) continue;
            DB::table($table)->whereNull('code')->chunkById(500, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    $code = getGenerateCode(DB::table($table), 'code', erpCodePrefix($table));
                    DB::table($table)->where('id', $row->id)->whereNull('code')->update(['code' => $code]);
                }
            });
            $codeColumn = collect(Schema::getColumns($table))->firstWhere('name', 'code');
            if ($codeColumn && $codeColumn['nullable']) {
                Schema::table($table, fn (Blueprint $t) => $t->string('code')->nullable(false)->change());
            }
        }
    }
    public function down(): void {
        // Intentionally retain common columns: removing them would destroy audit data.
    }
};
