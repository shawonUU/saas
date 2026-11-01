<?php

namespace Modules\Business\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Business\App\Models\AppModule;
use Modules\Business\App\Models\AppMenu;
use Modules\Business\App\Models\AppChildMenu;
use Modules\Business\App\Models\BusinessCategory;
use Modules\Business\App\Models\BusinessCategoryModule;

class AppModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Dashboard',
                'name_bn' => 'ড্যাশবোর্ড',
                'slug' => 'dashboard',
                'icon' => 'LayoutDashboard',
                'route' => '/dashboard',
                'sort_order' => 1,
            ],
            [
                'name' => 'Products',
                'name_bn' => 'পণ্যসমূহ',
                'slug' => 'products',
                'icon' => 'Package',
                'route' => '/products',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sales',
                'name_bn' => 'বিক্রয়',
                'slug' => 'sales',
                'icon' => 'ShoppingCart',
                'route' => '/sales',
                'sort_order' => 3,
            ],
            [
                'name' => 'Purchases',
                'name_bn' => 'ক্রয়',
                'slug' => 'purchases',
                'icon' => 'ShoppingBag',
                'route' => '/purchases',
                'sort_order' => 4,
            ],
            [
                'name' => 'Inventory',
                'name_bn' => 'ইনভেন্টরি',
                'slug' => 'inventory',
                'icon' => 'Boxes',
                'route' => '/inventory',
                'sort_order' => 5,
            ],
            [
                'name' => 'Customers',
                'name_bn' => 'ক্রেতাগণ',
                'slug' => 'customers',
                'icon' => 'Users',
                'route' => '/customers',
                'sort_order' => 6,
            ],
            [
                'name' => 'Suppliers',
                'name_bn' => 'সরবরাহকারীগণ',
                'slug' => 'suppliers',
                'icon' => 'Truck',
                'route' => '/suppliers',
                'sort_order' => 7,
            ],
            [
                'name' => 'Reports',
                'name_bn' => 'রিপোর্টসমূহ',
                'slug' => 'reports',
                'icon' => 'ChartNoAxesCombined',
                'route' => '/reports',
                'sort_order' => 8,
            ],
            [
                'name' => 'Settings',
                'name_bn' => 'সেটিংস',
                'slug' => 'settings',
                'icon' => 'Settings',
                'route' => '/settings',
                'sort_order' => 9,
            ],
            [
                'name' => 'Restaurant Menu',
                'name_bn' => 'রেস্টুরেন্ট মেনু',
                'slug' => 'restaurant-menu',
                'icon' => 'BookOpen',
                'route' => '/restaurant/menu',
                'sort_order' => 10,
            ],
            [
                'name' => 'Orders',
                'name_bn' => 'অর্ডারসমূহ',
                'slug' => 'orders',
                'icon' => 'ClipboardList',
                'route' => '/orders',
                'sort_order' => 11,
            ],
            [
                'name' => 'Kitchen',
                'name_bn' => 'রান্নাঘর',
                'slug' => 'kitchen',
                'icon' => 'ChefHat',
                'route' => '/kitchen',
                'sort_order' => 12,
            ],
            [
                'name' => 'Medicines',
                'name_bn' => 'ওষুধসমূহ',
                'slug' => 'medicines',
                'icon' => 'Pill',
                'route' => '/medicines',
                'sort_order' => 13,
            ],
            [
                'name' => 'Variants',
                'name_bn' => 'ভ্যারিয়েন্টসমূহ',
                'slug' => 'variants',
                'icon' => 'Layers',
                'route' => '/variants',
                'sort_order' => 14,
            ],
            [
                'name' => 'Online Store',
                'name_bn' => 'অনলাইন স্টোর',
                'slug' => 'online-store',
                'icon' => 'Store',
                'route' => '/online-stores',
                'sort_order' => 15,
            ],
        ];

        foreach ($modules as $moduleData) {
            AppModule::updateOrCreate(
                ['slug' => $moduleData['slug']],
                array_merge($moduleData, [
                    'name_bn' => $moduleData['name_bn'] ?? $moduleData['name'],
                    'is_active' => true,
                ])
            );
        }

        $menuDefinitions = [
            'dashboard' => [
                [
                    'name' => 'Overview',
                    'name_bn' => 'সারসংক্ষেপ',
                    'slug' => 'overview',
                    'icon' => 'LayoutDashboard',
                    'route' => '/dashboard',
                    'sort_order' => 1,
                ],
            ],

            'products' => [
                [
                    'name' => 'Product Management',
                    'name_bn' => 'পণ্য ব্যবস্থাপনা',
                    'slug' => 'product-management',
                    'icon' => 'Package',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Products',
                            'name_bn' => 'সকল পণ্য',
                            'slug' => 'all-products',
                            'icon' => 'Package',
                            'route' => '/products',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Product',
                            'name_bn' => 'পণ্য যোগ করুন',
                            'slug' => 'add-product',
                            'icon' => 'Package',
                            'route' => '/products/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Categories',
                    'name_bn' => 'ক্যাটাগরিসমূহ',
                    'slug' => 'categories',
                    'icon' => 'Boxes',
                    'route' => null,
                    'sort_order' => 2,
                    'children' => [
                        [
                            'name' => 'All Categories',
                            'name_bn' => 'সকল ক্যাটাগরি',
                            'slug' => 'all-categories',
                            'icon' => 'Boxes',
                            'route' => '/products/categories',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Category',
                            'name_bn' => 'ক্যাটাগরি যোগ করুন',
                            'slug' => 'add-category',
                            'icon' => 'Boxes',
                            'route' => '/products/categories/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Units',
                    'name_bn' => 'এককসমূহ',
                    'slug' => 'units',
                    'icon' => 'Layers',
                    'route' => null,
                    'sort_order' => 3,
                    'children' => [
                        [
                            'name' => 'All Units',
                            'name_bn' => 'সকল একক',
                            'slug' => 'all-units',
                            'icon' => 'Layers',
                            'route' => '/products/units',
                            'sort_order' => 1,
                        ],
                    ],
                ],
            ],

            'sales' => [
                [
                    'name' => 'Sales Management',
                    'name_bn' => 'বিক্রয় ব্যবস্থাপনা',
                    'slug' => 'sales-management',
                    'icon' => 'ShoppingCart',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Sales List',
                            'name_bn' => 'বিক্রয় তালিকা',
                            'slug' => 'sales-list',
                            'icon' => 'ClipboardList',
                            'route' => '/sales',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Sale',
                            'name_bn' => 'নতুন বিক্রয়',
                            'slug' => 'create-sale',
                            'icon' => 'ShoppingCart',
                            'route' => '/sales/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Sales Returns',
                            'name_bn' => 'বিক্রয় ফেরত',
                            'slug' => 'sales-returns',
                            'icon' => 'RefreshCw',
                            'route' => '/sales/returns',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'purchases' => [
                [
                    'name' => 'Purchase Management',
                    'name_bn' => 'ক্রয় ব্যবস্থাপনা',
                    'slug' => 'purchase-management',
                    'icon' => 'ShoppingBag',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Purchase List',
                            'name_bn' => 'ক্রয় তালিকা',
                            'slug' => 'purchase-list',
                            'icon' => 'ClipboardList',
                            'route' => '/purchases',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Purchase',
                            'name_bn' => 'নতুন ক্রয়',
                            'slug' => 'create-purchase',
                            'icon' => 'ShoppingBag',
                            'route' => '/purchases/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Purchase Returns',
                            'name_bn' => 'ক্রয় ফেরত',
                            'slug' => 'purchase-returns',
                            'icon' => 'RefreshCw',
                            'route' => '/purchases/returns',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'inventory' => [
                [
                    'name' => 'Stock Management',
                    'name_bn' => 'স্টক ব্যবস্থাপনা',
                    'slug' => 'stock-management',
                    'icon' => 'Boxes',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Stock Overview',
                            'name_bn' => 'স্টকের সারসংক্ষেপ',
                            'slug' => 'stock-overview',
                            'icon' => 'Boxes',
                            'route' => '/inventory',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Stock Adjustments',
                            'name_bn' => 'স্টক সমন্বয়',
                            'slug' => 'stock-adjustments',
                            'icon' => 'RefreshCw',
                            'route' => '/inventory/adjustments',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Stock Transfers',
                            'name_bn' => 'স্টক স্থানান্তর',
                            'slug' => 'stock-transfers',
                            'icon' => 'Truck',
                            'route' => '/inventory/transfers',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'customers' => [
                [
                    'name' => 'Customer Management',
                    'name_bn' => 'ক্রেতা ব্যবস্থাপনা',
                    'slug' => 'customer-management',
                    'icon' => 'Users',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Customers',
                            'name_bn' => 'সকল ক্রেতা',
                            'slug' => 'all-customers',
                            'icon' => 'Users',
                            'route' => '/customers',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Customer',
                            'name_bn' => 'ক্রেতা যোগ করুন',
                            'slug' => 'add-customer',
                            'icon' => 'Users',
                            'route' => '/customers/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
            ],

            'suppliers' => [
                [
                    'name' => 'Supplier Management',
                    'name_bn' => 'সরবরাহকারী ব্যবস্থাপনা',
                    'slug' => 'supplier-management',
                    'icon' => 'Truck',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Suppliers',
                            'name_bn' => 'সকল সরবরাহকারী',
                            'slug' => 'all-suppliers',
                            'icon' => 'Truck',
                            'route' => '/suppliers',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Supplier',
                            'name_bn' => 'সরবরাহকারী যোগ করুন',
                            'slug' => 'add-supplier',
                            'icon' => 'Truck',
                            'route' => '/suppliers/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
            ],

            'reports' => [
                [
                    'name' => 'Reports Overview',
                    'name_bn' => 'রিপোর্টের সারসংক্ষেপ',
                    'slug' => 'reports-overview',
                    'icon' => 'ChartNoAxesCombined',
                    'route' => '/reports',
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Sales Reports',
                    'name_bn' => 'বিক্রয় রিপোর্ট',
                    'slug' => 'sales-reports',
                    'icon' => 'BarChart3',
                    'route' => '/reports/sales',
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Purchase Reports',
                    'name_bn' => 'ক্রয় রিপোর্ট',
                    'slug' => 'purchase-reports',
                    'icon' => 'BarChart3',
                    'route' => '/reports/purchases',
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Inventory Reports',
                    'name_bn' => 'ইনভেন্টরি রিপোর্ট',
                    'slug' => 'inventory-reports',
                    'icon' => 'Boxes',
                    'route' => '/reports/inventory',
                    'sort_order' => 4,
                ],
            ],

            'settings' => [
                [
                    'name' => 'General Settings',
                    'name_bn' => 'সাধারণ সেটিংস',
                    'slug' => 'general-settings',
                    'icon' => 'Settings',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Business Profile',
                            'name_bn' => 'ব্যবসার প্রোফাইল',
                            'slug' => 'business-profile',
                            'icon' => 'Building2',
                            'route' => '/settings/business',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Branches',
                            'name_bn' => 'শাখাসমূহ',
                            'slug' => 'branches',
                            'icon' => 'Store',
                            'route' => '/settings/branches',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Users & Permissions',
                            'name_bn' => 'ব্যবহারকারী ও অনুমতিসমূহ',
                            'slug' => 'users-permissions',
                            'icon' => 'Users',
                            'route' => '/settings/users',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'restaurant-menu' => [
                [
                    'name' => 'Menu Management',
                    'name_bn' => 'মেনু ব্যবস্থাপনা',
                    'slug' => 'menu-management',
                    'icon' => 'BookOpen',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Menu Items',
                            'name_bn' => 'সকল মেনু আইটেম',
                            'slug' => 'all-menu-items',
                            'icon' => 'BookOpen',
                            'route' => '/restaurant/menu',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Menu Item',
                            'name_bn' => 'মেনু আইটেম যোগ করুন',
                            'slug' => 'add-menu-item',
                            'icon' => 'BookOpen',
                            'route' => '/restaurant/menu/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Menu Categories',
                            'name_bn' => 'মেনু ক্যাটাগরিসমূহ',
                            'slug' => 'menu-categories',
                            'icon' => 'Boxes',
                            'route' => '/restaurant/menu/categories',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'orders' => [
                [
                    'name' => 'Order Management',
                    'name_bn' => 'অর্ডার ব্যবস্থাপনা',
                    'slug' => 'order-management',
                    'icon' => 'ClipboardList',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Orders',
                            'name_bn' => 'সকল অর্ডার',
                            'slug' => 'all-orders',
                            'icon' => 'ClipboardList',
                            'route' => '/orders',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Order',
                            'name_bn' => 'নতুন অর্ডার',
                            'slug' => 'create-order',
                            'icon' => 'ShoppingCart',
                            'route' => '/orders/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Order History',
                            'name_bn' => 'অর্ডারের ইতিহাস',
                            'slug' => 'order-history',
                            'icon' => 'ClipboardList',
                            'route' => '/orders/history',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'kitchen' => [
                [
                    'name' => 'Kitchen Management',
                    'name_bn' => 'রান্নাঘর ব্যবস্থাপনা',
                    'slug' => 'kitchen-management',
                    'icon' => 'ChefHat',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Kitchen Orders',
                            'name_bn' => 'রান্নাঘরের অর্ডার',
                            'slug' => 'kitchen-orders',
                            'icon' => 'ClipboardList',
                            'route' => '/kitchen',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Preparation Queue',
                            'name_bn' => 'প্রস্তুতির অপেক্ষমাণ তালিকা',
                            'slug' => 'preparation-queue',
                            'icon' => 'ChefHat',
                            'route' => '/kitchen/queue',
                            'sort_order' => 2,
                        ],
                    ],
                ],
            ],

            'medicines' => [
                [
                    'name' => 'Medicine Management',
                    'name_bn' => 'ওষুধ ব্যবস্থাপনা',
                    'slug' => 'medicine-management',
                    'icon' => 'Pill',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Medicines',
                            'name_bn' => 'সকল ওষুধ',
                            'slug' => 'all-medicines',
                            'icon' => 'Pill',
                            'route' => '/medicines',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Medicine',
                            'name_bn' => 'ওষুধ যোগ করুন',
                            'slug' => 'add-medicine',
                            'icon' => 'Pill',
                            'route' => '/medicines/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Medicine Categories',
                            'name_bn' => 'ওষুধের ক্যাটাগরিসমূহ',
                            'slug' => 'medicine-categories',
                            'icon' => 'Boxes',
                            'route' => '/medicines/categories',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'variants' => [
                [
                    'name' => 'Product Variants',
                    'name_bn' => 'পণ্যের ভ্যারিয়েন্টসমূহ',
                    'slug' => 'product-variants',
                    'icon' => 'Layers',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Variants',
                            'name_bn' => 'সকল ভ্যারিয়েন্ট',
                            'slug' => 'all-variants',
                            'icon' => 'Layers',
                            'route' => '/variants',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Variant',
                            'name_bn' => 'ভ্যারিয়েন্ট যোগ করুন',
                            'slug' => 'add-variant',
                            'icon' => 'Layers',
                            'route' => '/variants/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Attributes',
                            'name_bn' => 'বৈশিষ্ট্যসমূহ',
                            'slug' => 'attributes',
                            'icon' => 'Settings',
                            'route' => '/variants/attributes',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],

            'online-store' => [
                [
                    'name' => 'Store Management',
                    'name_bn' => 'স্টোর ব্যবস্থাপনা',
                    'slug' => 'store-management',
                    'icon' => 'Store',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'My Online Stores',
                            'name_bn' => 'আমার অনলাইন স্টোরসমূহ',
                            'slug' => 'my-online-stores',
                            'icon' => 'Store',
                            'route' => '/online-stores',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Online Store',
                            'name_bn' => 'অনলাইন স্টোর তৈরি করুন',
                            'slug' => 'create-online-store',
                            'icon' => 'Plus',
                            'route' => '/online-stores/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($menuDefinitions as $moduleSlug => $menus) {
            $module = AppModule::where('slug', $moduleSlug)->first();

            if (! $module) {
                $this->command?->warn(
                    "Module '{$moduleSlug}' was not found. Its menus were skipped."
                );

                continue;
            }

            foreach ($menus as $menuData) {
                $children = $menuData['children'] ?? [];

                unset($menuData['children']);

                $menu = AppMenu::updateOrCreate(
                    [
                        'app_module_id' => $module->id,
                        'slug' => $menuData['slug'],
                    ],
                    array_merge($menuData, [
                        'name_bn' => $menuData['name_bn'] ?? $menuData['name'],
                        'app_module_id' => $module->id,
                        'is_active' => true,
                    ])
                );

                foreach ($children as $childData) {
                    AppChildMenu::updateOrCreate(
                        [
                            'app_menu_id' => $menu->id,
                            'slug' => $childData['slug'],
                        ],
                        array_merge($childData, [
                            'name_bn' => $childData['name_bn'] ?? $childData['name'],
                            'app_menu_id' => $menu->id,
                            'is_active' => true,
                        ])
                    );
                }
            }
        }

        $categoryModuleMap = [
            'retail-general-store' => [
                'dashboard',
                'products',
                'sales',
                'purchases',
                'inventory',
                'customers',
                'suppliers',
                'reports',
                'settings',
                'variants',
                'online-store',
            ],

            'restaurant' => [
                'dashboard',
                'restaurant-menu',
                'orders',
                'sales',
                'purchases',
                'inventory',
                'customers',
                'suppliers',
                'kitchen',
                'reports',
                'settings',
            ],

            'pharmacy' => [
                'dashboard',
                'medicines',
                'sales',
                'purchases',
                'inventory',
                'customers',
                'suppliers',
                'reports',
                'settings',
            ],

            'clothing' => [
                'dashboard',
                'products',
                'variants',
                'sales',
                'purchases',
                'inventory',
                'customers',
                'suppliers',
                'reports',
                'settings',
            ],
        ];

        foreach ($categoryModuleMap as $categorySlug => $moduleSlugs) {
            $category = BusinessCategory::where(
                'slug',
                $categorySlug
            )->first();

            if (! $category) {
                $this->command?->warn(
                    "Business category '{$categorySlug}' was not found."
                );

                continue;
            }

            $moduleIds = AppModule::query()
                ->whereIn('slug', $moduleSlugs)
                ->pluck('id', 'slug');

            foreach ($moduleSlugs as $moduleSlug) {
                if (! isset($moduleIds[$moduleSlug])) {
                    $this->command?->warn(
                        "Module '{$moduleSlug}' was not found for category '{$categorySlug}'."
                    );

                    continue;
                }

                BusinessCategoryModule::updateOrCreate(
                    [
                        'business_category_id' => $category->id,
                        'app_module_id' => $moduleIds[$moduleSlug],
                    ],
                    []
                );
            }
        }

        $this->command?->info(
            'Modules, menus, child menus and category mappings seeded successfully.'
        );
    }
}
