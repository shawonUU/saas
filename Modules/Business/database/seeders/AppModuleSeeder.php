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
                'slug' => 'dashboard',
                'icon' => 'LayoutDashboard',
                'route' => '/dashboard',
                'sort_order' => 1,
            ],
            [
                'name' => 'Products',
                'slug' => 'products',
                'icon' => 'Package',
                'route' => '/products',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'icon' => 'ShoppingCart',
                'route' => '/sales',
                'sort_order' => 3,
            ],
            [
                'name' => 'Purchases',
                'slug' => 'purchases',
                'icon' => 'ShoppingBag',
                'route' => '/purchases',
                'sort_order' => 4,
            ],
            [
                'name' => 'Inventory',
                'slug' => 'inventory',
                'icon' => 'Boxes',
                'route' => '/inventory',
                'sort_order' => 5,
            ],
            [
                'name' => 'Customers',
                'slug' => 'customers',
                'icon' => 'Users',
                'route' => '/customers',
                'sort_order' => 6,
            ],
            [
                'name' => 'Suppliers',
                'slug' => 'suppliers',
                'icon' => 'Truck',
                'route' => '/suppliers',
                'sort_order' => 7,
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'icon' => 'ChartNoAxesCombined',
                'route' => '/reports',
                'sort_order' => 8,
            ],
            [
                'name' => 'Settings',
                'slug' => 'settings',
                'icon' => 'Settings',
                'route' => '/settings',
                'sort_order' => 9,
            ],
            [
                'name' => 'Restaurant Menu',
                'slug' => 'restaurant-menu',
                'icon' => 'BookOpen',
                'route' => '/restaurant/menu',
                'sort_order' => 10,
            ],
            [
                'name' => 'Orders',
                'slug' => 'orders',
                'icon' => 'ClipboardList',
                'route' => '/orders',
                'sort_order' => 11,
            ],
            [
                'name' => 'Kitchen',
                'slug' => 'kitchen',
                'icon' => 'ChefHat',
                'route' => '/kitchen',
                'sort_order' => 12,
            ],
            [
                'name' => 'Medicines',
                'slug' => 'medicines',
                'icon' => 'Pill',
                'route' => '/medicines',
                'sort_order' => 13,
            ],
            [
                'name' => 'Variants',
                'slug' => 'variants',
                'icon' => 'Layers',
                'route' => '/variants',
                'sort_order' => 14,
            ],
            [
                'name' => 'Online Store',
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
                    'is_active' => true,
                ])
            );
        }

        $menuDefinitions = [
            'dashboard' => [
                [
                    'name' => 'Overview',
                    'slug' => 'overview',
                    'icon' => 'LayoutDashboard',
                    'route' => '/dashboard',
                    'sort_order' => 1,
                ],
            ],

            'products' => [
                [
                    'name' => 'Product Management',
                    'slug' => 'product-management',
                    'icon' => 'Package',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Products',
                            'slug' => 'all-products',
                            'icon' => 'Package',
                            'route' => '/products',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Product',
                            'slug' => 'add-product',
                            'icon' => 'Package',
                            'route' => '/products/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Categories',
                    'slug' => 'categories',
                    'icon' => 'Boxes',
                    'route' => null,
                    'sort_order' => 2,
                    'children' => [
                        [
                            'name' => 'All Categories',
                            'slug' => 'all-categories',
                            'icon' => 'Boxes',
                            'route' => '/products/categories',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Category',
                            'slug' => 'add-category',
                            'icon' => 'Boxes',
                            'route' => '/products/categories/create',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Units',
                    'slug' => 'units',
                    'icon' => 'Layers',
                    'route' => null,
                    'sort_order' => 3,
                    'children' => [
                        [
                            'name' => 'All Units',
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
                    'slug' => 'sales-management',
                    'icon' => 'ShoppingCart',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Sales List',
                            'slug' => 'sales-list',
                            'icon' => 'ClipboardList',
                            'route' => '/sales',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Sale',
                            'slug' => 'create-sale',
                            'icon' => 'ShoppingCart',
                            'route' => '/sales/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Sales Returns',
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
                    'slug' => 'purchase-management',
                    'icon' => 'ShoppingBag',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Purchase List',
                            'slug' => 'purchase-list',
                            'icon' => 'ClipboardList',
                            'route' => '/purchases',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Purchase',
                            'slug' => 'create-purchase',
                            'icon' => 'ShoppingBag',
                            'route' => '/purchases/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Purchase Returns',
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
                    'slug' => 'stock-management',
                    'icon' => 'Boxes',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Stock Overview',
                            'slug' => 'stock-overview',
                            'icon' => 'Boxes',
                            'route' => '/inventory',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Stock Adjustments',
                            'slug' => 'stock-adjustments',
                            'icon' => 'RefreshCw',
                            'route' => '/inventory/adjustments',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Stock Transfers',
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
                    'slug' => 'customer-management',
                    'icon' => 'Users',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Customers',
                            'slug' => 'all-customers',
                            'icon' => 'Users',
                            'route' => '/customers',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Customer',
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
                    'slug' => 'supplier-management',
                    'icon' => 'Truck',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Suppliers',
                            'slug' => 'all-suppliers',
                            'icon' => 'Truck',
                            'route' => '/suppliers',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Supplier',
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
                    'slug' => 'reports-overview',
                    'icon' => 'ChartNoAxesCombined',
                    'route' => '/reports',
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Sales Reports',
                    'slug' => 'sales-reports',
                    'icon' => 'BarChart3',
                    'route' => '/reports/sales',
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Purchase Reports',
                    'slug' => 'purchase-reports',
                    'icon' => 'BarChart3',
                    'route' => '/reports/purchases',
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Inventory Reports',
                    'slug' => 'inventory-reports',
                    'icon' => 'Boxes',
                    'route' => '/reports/inventory',
                    'sort_order' => 4,
                ],
            ],

            'settings' => [
                [
                    'name' => 'General Settings',
                    'slug' => 'general-settings',
                    'icon' => 'Settings',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Business Profile',
                            'slug' => 'business-profile',
                            'icon' => 'Building2',
                            'route' => '/settings/business',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Branches',
                            'slug' => 'branches',
                            'icon' => 'Store',
                            'route' => '/settings/branches',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Users & Permissions',
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
                    'slug' => 'menu-management',
                    'icon' => 'BookOpen',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Menu Items',
                            'slug' => 'all-menu-items',
                            'icon' => 'BookOpen',
                            'route' => '/restaurant/menu',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Menu Item',
                            'slug' => 'add-menu-item',
                            'icon' => 'BookOpen',
                            'route' => '/restaurant/menu/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Menu Categories',
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
                    'slug' => 'order-management',
                    'icon' => 'ClipboardList',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Orders',
                            'slug' => 'all-orders',
                            'icon' => 'ClipboardList',
                            'route' => '/orders',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Order',
                            'slug' => 'create-order',
                            'icon' => 'ShoppingCart',
                            'route' => '/orders/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Order History',
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
                    'slug' => 'kitchen-management',
                    'icon' => 'ChefHat',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'Kitchen Orders',
                            'slug' => 'kitchen-orders',
                            'icon' => 'ClipboardList',
                            'route' => '/kitchen',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Preparation Queue',
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
                    'slug' => 'medicine-management',
                    'icon' => 'Pill',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Medicines',
                            'slug' => 'all-medicines',
                            'icon' => 'Pill',
                            'route' => '/medicines',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Medicine',
                            'slug' => 'add-medicine',
                            'icon' => 'Pill',
                            'route' => '/medicines/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Medicine Categories',
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
                    'slug' => 'product-variants',
                    'icon' => 'Layers',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'All Variants',
                            'slug' => 'all-variants',
                            'icon' => 'Layers',
                            'route' => '/variants',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Add Variant',
                            'slug' => 'add-variant',
                            'icon' => 'Layers',
                            'route' => '/variants/create',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Attributes',
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
                    'slug' => 'store-management',
                    'icon' => 'Store',
                    'route' => null,
                    'sort_order' => 1,
                    'children' => [
                        [
                            'name' => 'My Online Stores',
                            'slug' => 'my-online-stores',
                            'icon' => 'Store',
                            'route' => '/online-stores',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Create Online Store',
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
