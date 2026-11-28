<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

use App\Models\Module;
use App\Models\Menu;
use App\Models\ChildMenu;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
class ModuleMenuSeeder extends Seeder
{
    public function run():void
    {
        $structure=[
            ['configurations','Configurations','কনফিগারেশন','settings',[
                ['branch','Branch','শাখা','building',[]],
                ['warehouse','Warehouse','গুদাম','warehouse',[]],
                ['customers','Customers','কাস্টমার','user',[]],
                ['suppliers','Suppliers','সরবরাহকারী','truck',[]],
                ['units','Units','ইউনিট','ruler',[]],
                ['sizes','Sizes','সাইজ','ruler',[]],
                ['colors','Colors','রঙ','ruler',[]],
                ['brands','Brands','ব্র্যান্ড','package',[]],
                ['categories','Categories','ক্যাটাগরি','list',[]],
                ['sub-categories','Sub-Categories','সাব-ক্যাটাগরি','list',[]],
                ['products','Products','পণ্য','package',[]],
                ['taxes','Taxes','ট্যাক্স','percent',[]],
                ['currencies','Currencies','মুদ্রা','currency-bdt',[]],
                ['payment-methods','Payment Methods','পেমেন্ট পদ্ধতি','credit-card',[]],
                ['shipping-methods','Shipping Methods','শিপিং পদ্ধতি','truck',[]],
                ['ingredients','Ingredients','উপকরণ','package',[]],
                ['recipes','Recipes','রেসিপি','book-open',[]],
                ['modifiers','Modifiers & Add-ons','অপশন ও অ্যাডঅন','list-plus',[]],
                ['dining-areas','Dining Areas','ডাইনিং এরিয়া','layout-grid',[]],
                ['tables','Tables','টেবিল','grid-2x2',[]],
                ['kitchen-stations','Kitchen Stations','কিচেন স্টেশন','chef-hat',[]],
            ]],
            ['sales','Sales','বিক্রয়','shopping-cart',[
                ['orders','Orders','অর্ডার','receipt',[[ 'order-list','Order List','অর্ডার তালিকা',null],['new-order','New Order','নতুন অর্ডার',null],['order-details','Order Details','অর্ডারের বিবরণ',null]]],
                ['retail-pos','POS','পস','pos',[]],
                ['restaurant-pos','POS','পস','pos',[]],
                ['quotations','Quotations','কোটেশন','file-text',[[ 'quotation-list','Quotation List','কোটেশন তালিকা',null],['new-quotation','New Quotation','নতুন কোটেশন',null]]],
                ['invoices','Sales Invoices','বিক্রয় ইনভয়েস','file-check',[[ 'invoice-list','Invoice List','ইনভয়েস তালিকা',null],['new-invoice','New Invoice','নতুন ইনভয়েস',null]]],
                ['returns','Sales Returns','বিক্রয় রিটার্ন','undo-2',[[ 'return-list','Return List','রিটার্ন তালিকা',null],['new-return','New Return','নতুন রিটার্ন',null]]],
                ['discounts','Discounts','ডিসকাউন্ট','badge-percent',[[ 'coupons','Coupons','কুপন',null],['price-rules','Price Rules','মূল্যছাড়ের নিয়ম',null]]],
                ['reports','Sales Reports','বিক্রয় রিপোর্ট','chart-bar',[[ 'sales-summary','Sales Summary','বিক্রয় সারাংশ',null]]],
                ['settings','Settings','সেটিংস','settings',[[ 'general-settings','General Settings','সাধারণ সেটিংস',null], [ 'pos-settings','POS Settings','পস সেটিংস',null]]]
            ]],
            ['inventory','Inventory','ইনভেন্টরি','package',[
                ['stock','Stock','স্টক','warehouse',[[ 'stock-report','Stock Report','স্টক রিপোর্ট',null],['stock-ledger','Stock Ledger','স্টক লেজার',null],['low-stock','Low Stock','কম স্টক',null]]],
                ['movements','Stock Movements','স্টক মুভমেন্ট','arrow-left-right',[[ 'stock-transfer','Stock Transfer','স্টক ট্রান্সফার',null],['stock-adjustment','Stock Adjustment','স্টক সমন্বয়',null],['stock-count','Stock Count','স্টক গণনা',null],['ingredient-consumption','Ingredient Consumption','উপকরণ ব্যবহার',null],['wastage','Wastage','অপচয়',null]]],
            ]],
            ['purchase','Purchase','ক্রয়','shopping-bag',[
                ['requisitions','Requisitions','রিকুইজিশন','clipboard-list',[[ 'requisition-list','Requisition List','রিকুইজিশন তালিকা',null],['new-requisition','New Requisition','নতুন রিকুইজিশন',null]]],
                ['purchase-orders','Purchase Orders','ক্রয় আদেশ','file-plus',[[ 'po-list','PO List','ক্রয় আদেশ তালিকা',null],['new-po','New PO','নতুন ক্রয় আদেশ',null]]],
                ['receiving','Goods Receiving','পণ্য গ্রহণ','package-check',[[ 'grn-list','GRN List','পণ্য গ্রহণ তালিকা',null],['new-grn','New GRN','নতুন পণ্য গ্রহণ',null]]],
                ['purchase-returns','Purchase Returns','ক্রয় রিটার্ন','rotate-ccw',[[ 'purchase-return-list','Return List','রিটার্ন তালিকা',null]]]
            ]],
            ['finance','Accounts & Finance','হিসাব ও অর্থব্যবস্থা','landmark',[
                ['chart-of-accounts','Chart of Accounts','হিসাবের খাতসমূহ','list-tree',[[ 'account-list','Account List','হিসাবের খাত তালিকা',null],['account-groups','Account Groups','হিসাবের গ্রুপ',null]]],
                ['ledger','Ledger & Journals','লেজার ও জার্নাল','book-open',[[ 'journal-entries','Vouchers & Journals','ভাউচার ও জার্নাল',null],['general-ledger','General Ledger','জেনারেল লেজার',null]]],
                ['receivables','Receivables','পাওনা হিসাব','hand-coins',[[ 'customer-dues','Customer Dues','কাস্টমারের বকেয়া',null],['collections','Collections','আদায়',null]]],
                ['payables','Payables','দেনা হিসাব','credit-card',[[ 'vendor-bills','Vendor Bills','ভেন্ডর বিল',null],['vendor-payments','Vendor Payments','ভেন্ডর পেমেন্ট',null]]],
                ['banking','Bank & Cash','ব্যাংক ও নগদ','wallet',[[ 'bank-accounts','Bank Accounts','ব্যাংক অ্যাকাউন্ট',null],['bank-transfers','Bank Transfers','ব্যাংক ট্রান্সফার',null],['bank-reconciliation','Bank Reconciliation','ব্যাংক রিকনসিলিয়েশন',null]]],
                ['expenses','Expenses','খরচ','receipt-text',[[ 'expense-list','Expense List','খরচের তালিকা',null],['new-expense','New Expense','নতুন খরচ',null]]],
                ['financial-reports','Financial Reports','আর্থিক রিপোর্ট','chart-no-axes-combined',[[ 'trial-balance','Trial Balance','ট্রায়াল ব্যালেন্স',null],['profit-loss','Profit & Loss','লাভ-ক্ষতি হিসাব',null],['balance-sheet','Balance Sheet','ব্যালেন্স শিট',null],['cash-flow','Cash Flow','নগদ প্রবাহ',null]]]
            ]],
            ['ecommerce','E-Commerce','ই-কমার্স','shopping-basket',[
                ['storefront','Storefront','অনলাইন স্টোর','store',[[ 'pages','Pages','পেজ',null],['theme','Theme','থিম',null],['navigation','Navigation','নেভিগেশন',null]]],
                ['catalog','Online Catalog','অনলাইন ক্যাটালগ','grid-2x2',[[ 'collections','Collections','কালেকশন',null],['featured-products','Featured Products','ফিচার্ড প্রোডাক্ট',null]]],
                ['online-orders','Online Orders','অনলাইন অর্ডার','package-search',[[ 'online-order-list','Order List','অর্ডার তালিকা',null],['fulfillment','Fulfillment','অর্ডার পূরণ',null],['return-requests','Return Requests','রিটার্ন অনুরোধ',null]]],
                ['checkout','Checkout','চেকআউট','credit-card',[[ 'shipping-methods','Shipping Methods','ডেলিভারি পদ্ধতি',null],['payment-methods','Payment Methods','পেমেন্ট পদ্ধতি',null]]],
                ['online-marketing','Marketing','মার্কেটিং','megaphone',[[ 'campaigns','Campaigns','ক্যাম্পেইন',null],['abandoned-carts','Abandoned Carts','অসম্পূর্ণ কার্ট',null]]]
            ]],
            ['logistics','Delivery & Logistics','ডেলিভারি ও লজিস্টিকস','truck',[
                ['delivery','Delivery Management','ডেলিভারি ব্যবস্থাপনা','package',[
                    ['delivery-list','Delivery List','ডেলিভারি তালিকা',null],
                    ['ready-to-ship','Ready to Ship','ডেলিভারির জন্য প্রস্তুত',null],
                    ['delivery-tracking','Delivery Tracking','ডেলিভারি ট্র্যাকিং',null],
                    ['returned-delivery','Returned Delivery','ফেরত ডেলিভারি',null]
                ]],
                ['courier','Courier Management','কুরিয়ার ব্যবস্থাপনা','truck',[
                    ['courier-partners','Courier Partners','কুরিয়ার পার্টনার',null],
                    ['courier-booking','Courier Booking','কুরিয়ার বুকিং',null]
                ]],
                ['cod','Cash on Delivery','ক্যাশ অন ডেলিভারি','banknote',[
                    ['cod-collection','COD Collection','COD সংগ্রহ',null],
                    ['cod-settlement','COD Settlement','COD সেটেলমেন্ট',null]
                ]],
                ['settings','Delivery Settings','ডেলিভারি সেটিংস','settings',[
                    ['delivery-methods','Delivery Methods','ডেলিভারি পদ্ধতি',null],
                    ['delivery-zones','Delivery Zones','ডেলিভারি এলাকা',null],
                    ['delivery-charges','Delivery Charges','ডেলিভারি চার্জ',null],
                    ['courier-integration','Courier Integration','কুরিয়ার ইন্টিগ্রেশন',null]
                ]],
                ['reports','Reports','রিপোর্ট','chart-no-axes-combined',[
                    ['delivery-report','Delivery Report','ডেলিভারি রিপোর্ট',null],
                    ['courier-report','Courier Report','কুরিয়ার রিপোর্ট',null],
                    ['cod-report','COD Report','COD রিপোর্ট',null],
                    ['return-report','Return Report','রিটার্ন রিপোর্ট',null]
                ]]
            ]],
            ['restaurant','Restaurant Operations','রেস্টুরেন্ট পরিচালনা','utensils',[
                ['dining','Dining Operations','ডাইনিং কার্যক্রম','layout-grid',[
                    ['table-map','Table Map','টেবিল ম্যাপ',null],
                    ['reservations','Reservations','রিজার্ভেশন',null]
                ]],
                ['kitchen-display','Kitchen Display','কিচেন ডিসপ্লে','chef-hat',[
                    ['kitchen-tickets','Kitchen Tickets (KOT)','কিচেন টিকিট',null]
                ]],
                ['production','Kitchen Production','কিচেন উৎপাদন','cooking-pot',[]],
                ['reports','Restaurant Reports','রেস্টুরেন্ট রিপোর্ট','chart-bar',[
                    ['food-cost-report','Food Cost Report','খাবারের ব্যয় রিপোর্ট',null]
                ]]
            ]],
            ['hr','Human Resources','মানবসম্পদ','users-round',[
                ['employees','Employees','কর্মী','user-round',[[ 'employee-list','Employee List','কর্মী তালিকা',null],['departments','Departments','বিভাগ',null]]],
                ['attendance','Attendance','উপস্থিতি','calendar-check',[[ 'daily-attendance','Daily Attendance','দৈনিক উপস্থিতি',null],['rosters','Rosters','ডিউটি রোস্টার',null]]],
                ['leave','Leave','ছুটি','calendar-off',[[ 'leave-requests','Leave Requests','ছুটির আবেদন',null],['leave-policies','Leave Policies','ছুটির নীতি',null]]],
                ['payroll','Payroll','বেতন','wallet',[[ 'salary-structure','Salary Structure','বেতন কাঠামো',null],['payroll-runs','Payroll Runs','বেতন প্রক্রিয়া',null]]]
            ]],
            ['support','Support','সহায়তা','life-buoy',[
                ['tickets','Support Tickets','সাপোর্ট টিকিট','ticket',[[ 'ticket-list','Ticket List','টিকিট তালিকা',null],['new-ticket','New Ticket','নতুন টিকিট',null]]],
                ['feedback','Feedback','মতামত','message-square',[[ 'feedback-list','Feedback List','মতামত তালিকা',null]]]
            ]],
            ['authorization','Authorization','অনুমতি','shield',[
                ['users','Users','ইউজার','user',[]],
                ['roles','Roles','রোল','shield-check',[]],
                ['permissions','Permissions','পারমিশন','key-round',[]],
                ['access-control','Access Control','অ্যাক্সেস কন্ট্রোল','lock-keyhole',[]]
            ]],
            ['settings','Settings','সেটিংস','settings',[
                ['navigation-management','Navigation Management','নেভিগেশন ব্যবস্থাপনা','network',[]],
                ['business','Business','বিজনেস','store',[[ 'general-settings','General Settings','সাধারণ সেটিংস',null],['branding','Branding','ব্র্যান্ডিং',null]]],
                ['ecommerce','E-commerce','ই-কমার্স','store',[[ 'general-settings','General Settings','সাধারণ সেটিংস',null]]],
                ['team','Team & Permissions','টিম ও পারমিশন','shield-check',[[ 'user-permissions','Users & Permissions','ইউজার ও পারমিশন',null],['user-list','User List','ইউজার তালিকা',null]]],
                ['locations','Branches & Warehouses','শাখা ও গুদাম','building-2',[[ 'branches','Branches','শাখা',null],['warehouses','Warehouses','গুদাম',null]]],
                ['taxes','Tax Settings','ট্যাক্স সেটিংস','percent',[[ 'tax-rates','Tax Rates','ট্যাক্স রেট',null],['tax-groups','Tax Groups','ট্যাক্স গ্রুপ',null]]],
                ['integrations','Integrations','ইন্টিগ্রেশন','plug',[[ 'api-keys','API Keys','এপিআই কি',null],['webhooks','Webhooks','ওয়েবহুক',null]]],
                ['notifications','Notifications','নোটিফিকেশন','bell',[[ 'notification-templates','Templates','টেমপ্লেট',null],['notification-channels','Channels','চ্যানেল',null]]],
                ['security','Security','নিরাপত্তা','lock-keyhole',[[ 'roles','Roles','রোল',null],['login-history','Login History','লগইন ইতিহাস',null]]],
                ['audit','Audit Logs','অডিট লগ','scroll-text',[[ 'activity-logs','Activity Logs','অ্যাক্টিভিটি লগ',null]]]
            ]]
        ];

        // Reusable category profiles: '*' = all children of this menu; [] = direct menu only.
        $common=[
            'configurations'=>[
                'warehouse'=>[],'branch'=>[],
                'customers'=>[], 'suppliers'=>[], 'units'=>[], 'sizes'=>[],
                'colors'=>[], 'brands'=>[], 'categories'=>[], 'sub-categories'=>[],'products'=>[],
                'taxes'=>[], 'currencies'=>[], 'payment-methods'=>[], 'shipping-methods'=>[],
            ],
            'authorization'=>['users'=>'*','roles'=>'*','permissions'=>'*','access-control'=>'*'],
            'settings'=>['navigation-management'=>'*','business'=>'*', 'ecommerce'=>'*', 'team'=>'*','locations'=>'*','taxes'=>'*','integrations'=>'*','notifications'=>'*','security'=>'*','audit'=>'*'],
            'support'=>['tickets'=>'*','feedback'=>'*'],
        ];
        $profiles=[
            'retail'=>[
                'sales'=>['orders'=>'*','retail-pos'=>'*','quotations'=>'*','invoices'=>'*','returns'=>'*','discounts'=>'*','reports'=>'*', 'settings'=>'*'],
                'logistics'=>['delivery'=>'*','courier'=>'*','cod'=>'*','settings'=>'*','reports'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>['stock-transfer','stock-adjustment','stock-count']],
                'purchase'=>['purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*','online-marketing'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'restaurant'=>[
                'configurations'=>['ingredients'=>[],'recipes'=>[],'modifiers'=>[],'dining-areas'=>[],'tables'=>[],'kitchen-stations'=>[]],
                'sales'=>['orders'=>'*','restaurant-pos'=>'*','invoices'=>'*','returns'=>'*','discounts'=>'*','reports'=>'*', 'settings'=>'*'],
                'restaurant'=>['dining'=>'*','kitchen-display'=>'*','production'=>[],'reports'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>'*'],
                'purchase'=>['requisitions'=>'*','purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*','online-marketing'=>'*'],
                'logistics'=>['delivery'=>'*','courier'=>'*','cod'=>'*','settings'=>'*','reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','leave'=>'*','payroll'=>'*']
            ],
        ];

        // Category slug => [English, বাংলা, profile]. Add new categories here.
        $categories=[
            'retail'=>['Retail Shop','খুচরা দোকান','retail'],
            'restaurant'=>['Restaurant','রেস্টুরেন্ট','restaurant'],
        ];

        DB::transaction(function() use($structure,$profiles,$common,$categories){
            foreach($structure as $moduleOrder=>[$slug,$name,$nameBn,$icon,$menus]){
                $module=Module::updateOrCreate(['slug'=>$slug],[
                    'name'=>$name,'name_bn'=>$nameBn,'icon'=>$icon,'sort_order'=>$moduleOrder,'approval_status'=>'Approved'
                ]);
                foreach($menus as $menuOrder=>[$menuSlug,$menuName,$menuBn,$menuIcon,$children]){
                    $menu=Menu::updateOrCreate(['module_id'=>$module->id,'slug'=>$menuSlug],[
                        'name'=>$menuName,'name_bn'=>$menuBn,'icon'=>$menuIcon,'sort_order'=>$menuOrder,'approval_status'=>'Approved'
                    ]);
                    foreach($children as $childOrder=>[$childSlug,$childName,$childBn,$route]){
                        ChildMenu::updateOrCreate(['menu_id'=>$menu->id,'slug'=>$childSlug],[
                            'name'=>$childName,'name_bn'=>$childBn,'route'=>$route,'sort_order'=>$childOrder,'approval_status'=>'Approved'
                        ]);
                    }
                }
            }

            $modules=Module::with('menus.children')->get()->keyBy('slug');
            foreach($categories as $slug=>[$name,$nameBn,$profile]){
                if(!isset($profiles[$profile]))throw new RuntimeException("Missing category profile: {$profile}");
                $category=BusinessCategory::firstOrCreate(['slug'=>$slug],[
                    'name'=>$name,'name_bn'=>$nameBn,'approval_status'=>'Approved'
                ]);
                if($category->approval_status!=='Approved')continue;
                if(!$category->name_bn)$category->update(['name_bn'=>$nameBn]);

                $allowed=$common;
                foreach($profiles[$profile] as $moduleSlug=>$menus){
                    $allowed[$moduleSlug]=array_replace($allowed[$moduleSlug]??[],$menus);
                }
                if($profile==='restaurant'){
                    unset($allowed['configurations']['colors'],$allowed['configurations']['brands']);
                }
                $moduleIds=[];$menuIds=[];$childIds=[];
                foreach($allowed as $moduleSlug=>$allowedMenus){
                    $module=$modules->get($moduleSlug);
                    if(!$module)throw new RuntimeException("Module missing: {$moduleSlug}");
                    $moduleIds[]=$module->id;
                    foreach($allowedMenus as $menuSlug=>$allowedChildren){
                        $menu=$module->menus->firstWhere('slug',$menuSlug);
                        if(!$menu)throw new RuntimeException("Menu missing: {$moduleSlug}/{$menuSlug}");
                        $menuIds[]=$menu->id;
                        if($allowedChildren==='*')$allowedChildren=$menu->children->pluck('slug')->all();
                        foreach($allowedChildren as $childSlug){
                            $child=$menu->children->firstWhere('slug',$childSlug);
                            if(!$child)throw new RuntimeException("Child missing: {$moduleSlug}/{$menuSlug}/{$childSlug}");
                            $childIds[]=$child->id;
                        }
                    }
                }
                $id=$category->id;
                BusinessCategoryChildMenu::where('business_category_id',$id)->whereNotIn('child_menu_id',$childIds)->delete();
                BusinessCategoryMenu::where('business_category_id',$id)->whereNotIn('menu_id',$menuIds)->delete();
                BusinessCategoryModule::where('business_category_id',$id)->whereNotIn('module_id',$moduleIds)->delete();
                foreach(array_unique($moduleIds) as $moduleId)BusinessCategoryModule::withTrashed()->updateOrCreate(['business_category_id'=>$id,'module_id'=>$moduleId],['deleted_at'=>null,'approval_status'=>'Approved','is_active'=>true]);
                foreach(array_unique($menuIds) as $menuId)BusinessCategoryMenu::withTrashed()->updateOrCreate(['business_category_id'=>$id,'menu_id'=>$menuId],['deleted_at'=>null,'approval_status'=>'Approved','is_active'=>true]);
                foreach(array_unique($childIds) as $childId)BusinessCategoryChildMenu::withTrashed()->updateOrCreate(['business_category_id'=>$id,'child_menu_id'=>$childId],['deleted_at'=>null,'approval_status'=>'Approved','is_active'=>true]);
            }
        });
    }
}
