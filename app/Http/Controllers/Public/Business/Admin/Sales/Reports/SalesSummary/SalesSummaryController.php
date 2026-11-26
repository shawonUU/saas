<?php

namespace App\Http\Controllers\Public\Business\Admin\Sales\Reports\SalesSummary;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Payment;
use App\Models\Sales\Order;
use App\Models\Sales\SaleReturn;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesSummaryController extends Controller
{
    public function salesSummary(Request $r, Access $a)
    {
        $v = $a->check($r, 'sales', 'reports', 'sales-summary');
        $b = $v['business'];
        $v['title'] = 'Sales Summary';
        $query = Order::where('business_id', $b->id)->whereIn('status', ['confirmed', 'returned']);
        $returns = SaleReturn::where('business_id', $b->id);
        $payments = Payment::where('business_id', $b->id)->where('direction', 'in');
        $invoiceDate = DB::raw('COALESCE(posted_at, created_at)');
        $from = $r->query('from');
        $to = $r->query('to');
        if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $query->whereDate($invoiceDate, '>=', $from);
            $returns->whereDate('created_at', '>=', $from);
            $payments->whereDate('created_at', '>=', $from);
        }
        if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $query->whereDate($invoiceDate, '<=', $to);
            $returns->whereDate('created_at', '<=', $to);
            $payments->whereDate('created_at', '<=', $to);
        }
        $v['counts'] = ['orders' => (clone $query)->count(), 'gross' => (float) (clone $query)->sum('subtotal'), 'discount' => (float) (clone $query)->sum('discount'),
            'tax' => (float) (clone $query)->sum('tax'), 'delivery' => (float) (clone $query)->sum('shipping'), 'invoiced' => (float) (clone $query)->sum('total'), 'returns' => (float) $returns->sum('amount'), 'collected' => (float) $payments->sum('amount')];
        $query->select('orders.*')->selectRaw('COALESCE(posted_at, created_at) AS invoiced_at');
        $v['records'] = Lists::page($query, $r, ['id' => 'number', 'order_no' => 'text', 'channel' => 'text', 'subtotal' => 'number', 'discount' => 'number', 'shipping' => 'number', 'tax' => 'number', 'total' => 'number', 'paid' => 'number', 'invoiced_at' => 'date'], ['invoiced_at' => $invoiceDate]);

        return view('public.business.admin.retail.reports.sales', $v);
    }
}
