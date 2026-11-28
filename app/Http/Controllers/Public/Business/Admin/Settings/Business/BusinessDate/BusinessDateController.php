<?php

namespace App\Http\Controllers\Public\Business\Admin\Settings\Business\BusinessDate;

use App\Http\Controllers\Controller;
use App\Models\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDayClosure;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class BusinessDateController extends Controller
{
    public function index(Request $request, Access $access, BusinessDateService $days): View
    {
        $data = $access->check($request, 'settings', 'business', 'business-date');
        $state = $days->current($data['business']);
        $data['state'] = $state;
        $data['dayStatus'] = $days->status($state);
        $data['permissions'] = $access->permissions($data, 'settings', 'business', 'business-date');
        $data['closures'] = BusinessDayClosure::where('business_id', $data['business']->id)->latest('id')->paginate(15);
        return view('public.business.admin.settings.business.business-date.index', $data);
    }

    public function update(Request $request, Access $access, BusinessDateService $days): RedirectResponse
    {
        $data = $access->check($request, 'settings', 'business', 'business-date', 'update');
        $validated = $request->validate([
            'manual_mode' => ['required', 'boolean'],
            'timezone' => ['required', Rule::in(timezone_identifiers_list())],
            'alert_start_time' => ['required', 'date_format:H:i'],
            'closing_deadline' => ['required', 'date_format:H:i'],
        ]);
        if ($validated['alert_start_time'] > $validated['closing_deadline']) {
            return back()->withErrors(['alert_start_time' => t('Alert time must not be after closing deadline.', 'সতর্কবার্তার সময় বন্ধের শেষ সময়ের পরে হতে পারবে না।')])->withInput();
        }
        $days->updateSettings($data['business'], $validated);
        return back()->with('success', t('Business date settings saved.', 'বিজনেস ডেট সেটিংস সংরক্ষিত হয়েছে।'));
    }

    public function close(Request $request, Access $access, BusinessDateService $days): RedirectResponse
    {
        $data = $access->check($request, 'settings', 'business', 'business-date', 'update');
        $validated = $request->validate([
            'confirm_business_date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $current = $days->current($data['business']);
        if ($validated['confirm_business_date'] !== $current->open_date->toDateString()) {
            return back()->withErrors(['confirm_business_date' => t('Business date changed; refresh before closing.', 'বিজনেস তারিখ বদলেছে; বন্ধ করার আগে রিফ্রেশ করুন।')]);
        }
        $closed = $days->close($data['business'], (int) $data['user']->id, $data['user']->name, $validated['notes'] ?? null);
        return back()->with('success', t('Business day closed. New business date: ', 'বিজনেস দিন বন্ধ হয়েছে। নতুন তারিখ: ').$closed->next_business_date->toDateString());
    }
}
