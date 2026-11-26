<?php

namespace App\Services\Public\Business\Admin\Authorization\AccessControl;

use App\Models\Business;
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Access
{
    public function permissions(array $context, string $module, string $menu, ?string $child = null): array
    {
        $user = $context['user'];
        $business = $context['business'];
        $owner = $user->isOwnerOf($business);
        $permissions = ['create' => $owner, 'update' => $owner, 'delete' => $owner];
        if ($owner) {
            return $permissions;
        }
        $moduleEntry = $context['modules']->firstWhere('slug', $module);
        $menuEntry = $moduleEntry?->menus->firstWhere('slug', $menu);
        if (! $menuEntry) {
            return $permissions;
        }
        $query = $user->accesses()->where('module_id', $moduleEntry->id)->where('menu_id', $menuEntry->id)
            ->where('is_active', true)->where('approval_status', 'Approved')->where('can_view', true);
        if ($child === null) {
            $query->whereNull('child_menu_id');
        } else {
            $childEntry = $menuEntry->children->firstWhere('slug', $child);
            if (! $childEntry) {
                return $permissions;
            }
            $query->where('child_menu_id', $childEntry->id);
        }
        $accesses = $query->get();
        foreach (array_keys($permissions) as $action) {
            $permissions[$action] = $accesses->contains(fn ($entry) => (bool) $entry->{'can_'.$action});
        }

        return $permissions;
    }

    public function check(Request $request, string $module, string $menu, ?string $child = null, string $action = 'view'): array
    {
        $business = $request->attributes->get('current_business');
        $user = Auth::guard('business_admin')->user();
        abort_unless($business instanceof Business && $user && (int) $business->id === (int) $user->business_id, 403);
        $modules = app(BusinessNavigation::class)->forBusiness($business, $user);
        $m = $modules->firstWhere('slug', $module);
        $entry = $m?->menus->firstWhere('slug', $menu);
        abort_unless($entry && ($child === null ? $entry->children->isEmpty() : $entry->children->contains('slug', $child)), 403);
        if ($action !== 'view' && ! $user->isOwnerOf($business)) {
            $query = $user->accesses()->where('module_id', $m->id)->where('menu_id', $entry->id)
                ->where('is_active', true)->where('approval_status', 'Approved')->where('can_view', true)->where('can_'.$action, true);
            if ($child === null) {
                $query->whereNull('child_menu_id');
            } else {
                $query->where('child_menu_id', $entry->children->firstWhere('slug', $child)->id);
            }
            abort_unless($query->exists(), 403);
        }

        return ['business' => $business, 'user' => $user, 'modules' => $modules, 'businessKey' => $business->domain];
    }
}
