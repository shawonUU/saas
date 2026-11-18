<?php

namespace App\Http\Controllers\Public\Business\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Retail\Account;
use App\Services\Retail\{Access, Accounting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountGroupController extends Controller
{
    public function index(Request $r, Access $a)
    {
        $v = $a->check($r, 'finance', 'chart-of-accounts', 'account-groups');
        $b = $v['business'];
        app(Accounting::class)->ensure($b);

        $v['groups'] = Account::where('retail_accounts.business_id', $b->id)
            ->leftJoin('retail_journal_lines as l', function ($j) {
                $j->on('l.account_id', '=', 'retail_accounts.id')
                    ->on('l.business_id', '=', 'retail_accounts.business_id');
            })
            ->select(
                'retail_accounts.type',
                DB::raw('COUNT(DISTINCT retail_accounts.id) as accounts'),
                DB::raw('COALESCE(SUM(l.debit),0) as debit'),
                DB::raw('COALESCE(SUM(l.credit),0) as credit')
            )
            ->groupBy('retail_accounts.type')
            ->get();

        return view('public.business.admin.retail.account_groups.index', $v);
    }
}
