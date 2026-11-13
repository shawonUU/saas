<?php
namespace Database\Seeders;
use App\Models\{Module,Menu,ChildMenu,BusinessCategory,BusinessCategoryModule,BusinessCategoryMenu,BusinessCategoryChildMenu};
use Illuminate\Database\Seeder;
class ModuleMenuSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            ['sales','Sales','shopping-cart',[
                ['orders','Orders','receipt',[['order-list','Order List',null],['new-order','New Order',null]]],
                ['customers','Customers','users',[['customer-list','Customer List',null]]],
            ]],
            ['inventory','Inventory','package',[
                ['products','Products','boxes',[['product-list','Product List',null],['categories','Categories',null]]],
                ['stock','Stock','warehouse',[['stock-report','Stock Report',null]]],
            ]],
            ['settings','Settings','settings',[
                ['team','Team','shield-check',[['user-permissions','Users & Permissions',null]]],
                ['business','Business','store',[['general-settings','General Settings',null]]],
            ]],
        ];
        foreach ($structure as $modIndex => [$moduleSlug,$moduleName,$icon,$menus]) {
            $module = Module::firstOrCreate(['slug' => $moduleSlug], ['name' => $moduleName,'icon' => $icon,'sort_order' => $modIndex,'approval_status' => 'Approved']);
            foreach ($menus as $menuIndex => [$menuSlug,$menuName,$menuIcon,$children]) {
                $menu = Menu::firstOrCreate(['module_id' => $module->id,'slug' => $menuSlug], ['name' => $menuName,'icon' => $menuIcon,'sort_order' => $menuIndex,'approval_status' => 'Approved']);
                foreach ($children as $childIndex => [$childSlug,$childName,$route]) {
                    ChildMenu::firstOrCreate(['menu_id' => $menu->id,'slug' => $childSlug], ['name' => $childName,'route' => $route,'sort_order' => $childIndex,'approval_status' => 'Approved']);
                }
            }
        }
        $bangla = ['Overview' => 'ওভারভিউ','Dashboard' => 'ড্যাশবোর্ড','Summary' => 'সারাংশ','Sales' => 'বিক্রয়',
            'Orders' => 'অর্ডার','Order List' => 'অর্ডার তালিকা','New Order' => 'নতুন অর্ডার','Customers' => 'কাস্টমার',
            'Customer List' => 'কাস্টমার তালিকা','Inventory' => 'ইনভেন্টরি','Products' => 'প্রোডাক্ট',
            'Product List' => 'প্রোডাক্ট তালিকা','Categories' => 'ক্যাটাগরি','Stock' => 'স্টক','Stock Report' => 'স্টক রিপোর্ট',
            'Settings' => 'সেটিংস','Team' => 'টিম','Users & Permissions' => 'ইউজার ও পারমিশন',
            'Business' => 'বিজনেস','General Settings' => 'সাধারণ সেটিংস'];
        foreach (Module::with('menus.children')->get() as $module) {
            if (empty($module->name_bn)) $module->update(['name_bn' => $bangla[$module->name] ?? null]);
            foreach ($module->menus as $menu) {
                if (empty($menu->name_bn)) $menu->update(['name_bn' => $bangla[$menu->name] ?? null]);
                foreach ($menu->children as $child) {
                    if (empty($child->name_bn)) $child->update(['name_bn' => $bangla[$child->name] ?? null]);
                }
            }
        }
        $modules = Module::with('menus.children')->get();
        foreach (BusinessCategory::where('approval_status','Approved')->get() as $category) {
            foreach ($modules as $module) {
                BusinessCategoryModule::firstOrCreate(['business_category_id' => $category->id,'module_id' => $module->id]);
                foreach ($module->menus as $menu) {
                    BusinessCategoryMenu::firstOrCreate(['business_category_id' => $category->id,'menu_id' => $menu->id]);
                    foreach ($menu->children as $child) {
                        BusinessCategoryChildMenu::firstOrCreate(['business_category_id' => $category->id,'child_menu_id' => $child->id]);
                    }
                }
            }
        }
    }
}
