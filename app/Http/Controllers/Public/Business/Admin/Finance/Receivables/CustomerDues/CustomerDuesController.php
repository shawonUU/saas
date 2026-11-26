<?php

namespace App\Http\Controllers\Public\Business\Admin\Finance\Receivables\CustomerDues;

use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;

class CustomerDuesController extends Controller
{
    public function dues(Request $r, Access $a)
    {
        $v = $a->check($r, 'finance', 'receivables', 'customer-dues');
        $v['title'] = 'Customer Dues';
        $v['records'] = Lists::page(Order::where('business_id', $v['business']->id)->whereIn('status', ['confirmed', 'returned'])->whereRaw('paid + credited < total'), $r, ['id' => 'number', 'order_no' => 'text', 'customer_name' => 'text', 'total' => 'number', 'paid' => 'number', 'created_at' => 'date']);

        return view('public.business.admin.retail.reports.dues', $v);
    }
}
