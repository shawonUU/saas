<?php
namespace App\Services;
use App\Models\{Business,BusinessUser,Module,BusinessCategoryModule,BusinessCategoryMenu,BusinessCategoryChildMenu,BusinessModuleOverride,BusinessMenu,BusinessChildMenu};
use Illuminate\Support\Collection;
class BusinessNavigation
{
    public function forBusiness(Business $business, ?BusinessUser $user = null): Collection
    {
        if ($user && (int)$user->business_id !== (int)$business->id) return collect();
        $category = $business->category_id;
        $moduleIds = BusinessCategoryModule::where('business_category_id',$category)->pluck('module_id')->all();
        $menuIds = BusinessCategoryMenu::where('business_category_id',$category)->pluck('menu_id')->all();
        $childIds = BusinessCategoryChildMenu::where('business_category_id',$category)->pluck('child_menu_id')->all();
        $moduleOverrides = BusinessModuleOverride::where('business_id',$business->id)->pluck('is_enabled','module_id');
        $menuOverrides = BusinessMenu::where('business_id',$business->id)->pluck('is_enabled','menu_id');
        $childOverrides = BusinessChildMenu::where('business_id',$business->id)->pluck('is_enabled','child_menu_id');
        $owner = $user && $user->isOwnerOf($business);
        $permitted = $user && !$owner ? $user->accesses()->where('can_view',true)->get() : collect();
        return Module::with(['menus' => fn ($q) => $q->where('is_active',1)->where('approval_status','Approved'),
            'menus.children' => fn ($q) => $q->where('is_active',1)->where('approval_status','Approved')])
            ->where('is_active',1)->where('approval_status','Approved')->orderBy('sort_order')->get()
            ->filter(function ($module) use ($moduleIds,$menuIds,$childIds,$moduleOverrides,$menuOverrides,$childOverrides,$user,$owner,$permitted) {
                if (!(bool) $moduleOverrides->get($module->id,in_array($module->id,$moduleIds))) return false;
                $menus = $module->menus->filter(function ($menu) use ($module,$menuIds,$childIds,$menuOverrides,$childOverrides,$user,$owner,$permitted) {
                    if (!(bool) $menuOverrides->get($menu->id,in_array($menu->id,$menuIds))) return false;
                    $children = $menu->children->filter(function ($child) use ($module,$menu,$childIds,$childOverrides,$user,$owner,$permitted) {
                        if (!(bool) $childOverrides->get($child->id,in_array($child->id,$childIds))) return false;
                        if (!$user || $owner) return true;
                        return $permitted->contains(fn ($permission) => (int)$permission->module_id === (int)$module->id
                            && (int)$permission->menu_id === (int)$menu->id
                            && ($permission->child_menu_id === null || (int)$permission->child_menu_id === (int)$child->id));
                    })->values();
                    $menu->setRelation('children',$children);
                    return $children->isNotEmpty();
                })->values();
                $module->setRelation('menus',$menus);
                return $menus->isNotEmpty();
            })->values();
    }
    public function canView(Business $business, BusinessUser $user, int $childId): bool
    {
        return $this->forBusiness($business,$user)->contains(fn ($module) => $module->menus->contains(
            fn ($menu) => $menu->children->contains('id',$childId)
        ));
    }
}
