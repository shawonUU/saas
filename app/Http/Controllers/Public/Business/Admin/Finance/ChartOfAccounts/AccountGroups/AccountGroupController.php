<?php

namespace App\Http\Controllers\Public\Business\Admin\Finance\ChartOfAccounts\AccountGroups;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Account;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountGroupController extends Controller
{
    public function index(Request $r, Access $a)
    {
        $v = $a->check($r, 'finance', 'chart-of-accounts', 'account-groups');
        $b = $v['business'];
        app(Accounting::class)->ensure($b);

        $groupQuery = Account::where('accounts.business_id', $b->id)
            ->leftJoin('journal_lines as l', function ($j) {
                $j->on('l.account_id', '=', 'accounts.id')
                    ->on('l.business_id', '=', 'accounts.business_id');
            })
            ->select(
                'accounts.type',
                DB::raw('COUNT(DISTINCT accounts.id) as accounts'),
                DB::raw('COALESCE(SUM(l.debit),0) as debit'),
                DB::raw('COALESCE(SUM(l.credit),0) as credit')
            )
            ->groupBy('accounts.type');
        if (in_array($r->query('export'), ['excel','pdf'], true)) {
            return app(\App\Services\Public\Business\Admin\Shared\ListExportService::class)->download(clone $groupQuery, ['type'=>'text','accounts'=>'number','debit'=>'number','credit'=>'number'], 'Account Groups', $r->query('export'));
        }
        $groups = (clone $groupQuery)->get();
        if ($r->filled('f.type')) $groups = $groups->filter(fn($item) => str_contains(strtolower((string)$item->type), strtolower((string)$r->query('f.type'))))->values();
        $perPage=(int)$r->query('per_page',15);if(!in_array($perPage,[10,15,25,50,100],true))$perPage=15;
        $page=max(1,(int)$r->query('page',1));
        $v['groups']=new \Illuminate\Pagination\LengthAwarePaginator($groups->forPage($page,$perPage)->values(),$groups->count(),$perPage,$page,['path'=>$r->url(),'query'=>$r->query()]);

        return view('public.business.admin.retail.account_groups.index', $v);
    }
}
