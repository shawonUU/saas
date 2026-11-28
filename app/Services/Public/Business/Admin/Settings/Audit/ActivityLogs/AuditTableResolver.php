<?php

namespace App\Services\Public\Business\Admin\Settings\Audit\ActivityLogs;

class AuditTableResolver
{
    private const TABLES = [
        'retail/orders'=>'orders', 'retail/purchases'=>'purchases', 'retail/returns'=>'returns',
        'retail/quotations'=>'quotations', 'retail/payments'=>'payments', 'retail/discounts'=>'discounts',
        'retail/expenses'=>'expenses', 'retail/accounts'=>'accounts', 'retail/warehouses'=>'warehouses',
        'retail/manual_journals'=>'manual_journals', 'retail/purchase_orders'=>'purchase_orders',
        'retail/purchase_returns'=>'purchase_returns', 'retail/courier'=>'courier_shipments',
        'retail/delivery'=>'courier_shipments', 'retail/bank_transfers'=>'cash_transfers',
        'retail/gateway_settlements'=>'gateway_payments',
    ];
    public static function fromPath(string $path): ?string
    {
        foreach (self::TABLES as $slug => $table) if (str_contains($path, str_replace('_','-', $slug))) return $table;
        return null;
    }
}
