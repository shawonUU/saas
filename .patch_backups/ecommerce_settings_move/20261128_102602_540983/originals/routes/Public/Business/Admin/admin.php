<?php

use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use Illuminate\Support\Facades\Route;

$domain = config('saas.domain');

Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->group(function () {
    Route::middleware(['portal.auth:business_admin', 'auth.session'])->group(function () {

        Route::get('/dashboard', [WorkspaceController::class, 'dashboard'])->name('business.admin.dashboard');

        // Module groups own the URL and name prefix; included files use relative routes.
        Route::prefix('configurations')->name('business.admin.configurations.')->group(function () {
            require __DIR__.'/Configurations/Customers/routes.php';
            require __DIR__.'/Configurations/Suppliers/routes.php';
            require __DIR__.'/Configurations/Units/routes.php';
            require __DIR__.'/Configurations/Sizes/routes.php';
            require __DIR__.'/Configurations/DiningAreas/routes.php';
            require __DIR__.'/Configurations/Tables/routes.php';
            require __DIR__.'/Configurations/KitchenStations/routes.php';
            require __DIR__.'/Configurations/Colors/routes.php';
            require __DIR__.'/Configurations/Brands/routes.php';
            require __DIR__.'/Configurations/Categories/routes.php';
            require __DIR__.'/Configurations/SubCategories/routes.php';
            require __DIR__.'/Configurations/Products/routes.php';
            require __DIR__.'/Configurations/Ingredients/routes.php';
            require __DIR__.'/Configurations/Recipes/routes.php';
            require __DIR__.'/Configurations/Modifiers/routes.php';
            require __DIR__.'/Configurations/Taxes/routes.php';
            require __DIR__.'/Configurations/Currencies/routes.php';
            require __DIR__.'/Configurations/PaymentMethods/routes.php';
            require __DIR__.'/Configurations/ShippingMethods/routes.php';
        });

        Route::prefix('sales')->name('business.admin.sales.')->group(function () {
            require __DIR__.'/Sales/Orders/OrderList/routes.php';
            require __DIR__.'/Sales/Orders/NewOrder/routes.php';
            require __DIR__.'/Sales/Orders/OrderDetails/routes.php';
            require __DIR__.'/Sales/Pos/routes.php';
            require __DIR__.'/Sales/RestaurantPos/routes.php';
            require __DIR__.'/Sales/Quotations/QuotationList/routes.php';
            require __DIR__.'/Sales/Quotations/NewQuotation/routes.php';
            require __DIR__.'/Sales/Returns/ReturnList/routes.php';
            require __DIR__.'/Sales/Returns/NewReturn/routes.php';
            require __DIR__.'/Sales/Discounts/Coupons/routes.php';
            require __DIR__.'/Sales/Reports/SalesSummary/routes.php';
            require __DIR__.'/Sales/Settings/PosSettings/routes.php';
        });

        Route::prefix('inventory')->name('business.admin.inventory.')->group(function () {
            require __DIR__.'/Inventory/Stock/StockReport/routes.php';
            require __DIR__.'/Inventory/Stock/StockLedger/routes.php';
            require __DIR__.'/Inventory/Stock/LowStock/routes.php';
            require __DIR__.'/Inventory/Movements/StockTransfer/routes.php';
            require __DIR__.'/Inventory/Movements/StockAdjustment/routes.php';
            require __DIR__.'/Inventory/Movements/IngredientConsumption/routes.php';
            require __DIR__.'/Inventory/Movements/Wastage/routes.php';
        });

        Route::prefix('restaurant')->name('business.admin.restaurant.')->group(function () {
            require __DIR__.'/Restaurant/Dining/TableMap/routes.php';
            require __DIR__.'/Restaurant/Dining/Reservations/routes.php';
            require __DIR__.'/Restaurant/KitchenDisplay/KitchenTickets/routes.php';
            require __DIR__.'/Restaurant/Production/routes.php';
            require __DIR__.'/Restaurant/Reports/FoodCostReport/routes.php';
        });

        Route::prefix('purchase')->name('business.admin.purchase.')->group(function () {
            require __DIR__.'/Purchase/PurchaseOrders/PoList/routes.php';
            require __DIR__.'/Purchase/PurchaseOrders/NewPo/routes.php';
            require __DIR__.'/Purchase/Receiving/GrnList/routes.php';
            require __DIR__.'/Purchase/Receiving/NewGrn/routes.php';
            require __DIR__.'/Purchase/PurchaseReturns/PurchaseReturnList/routes.php';
        });

        Route::prefix('finance')->name('business.admin.finance.')->group(function () {
            require __DIR__.'/Finance/ChartOfAccounts/AccountList/routes.php';
            require __DIR__.'/Finance/ChartOfAccounts/AccountGroups/routes.php';
            require __DIR__.'/Finance/Ledger/JournalEntries/routes.php';
            require __DIR__.'/Finance/Ledger/GeneralLedger/routes.php';
            require __DIR__.'/Finance/Receivables/CustomerDues/routes.php';
            require __DIR__.'/Finance/Receivables/Collections/routes.php';
            require __DIR__.'/Finance/Payables/VendorBills/routes.php';
            require __DIR__.'/Finance/Payables/VendorPayments/routes.php';
            require __DIR__.'/Finance/Banking/BankTransfers/routes.php';
            require __DIR__.'/Finance/Banking/BankReconciliation/routes.php';
            require __DIR__.'/Finance/Expenses/ExpenseList/routes.php';
            require __DIR__.'/Finance/Expenses/NewExpense/routes.php';
            require __DIR__.'/Finance/FinancialReports/TrialBalance/routes.php';
            require __DIR__.'/Finance/FinancialReports/ProfitLoss/routes.php';
            require __DIR__.'/Finance/FinancialReports/BalanceSheet/routes.php';
            require __DIR__.'/Finance/FinancialReports/CashFlow/routes.php';
        });

        Route::prefix('ecommerce')->name('business.admin.ecommerce.')->group(function () {
            require __DIR__.'/Ecommerce/OnlineOrders/OnlineOrderList/routes.php';
            require __DIR__.'/Ecommerce/OnlineOrders/Fulfillment/routes.php';
            require __DIR__.'/Ecommerce/OnlineOrders/ReturnRequests/routes.php';
            require __DIR__.'/Ecommerce/OnlineMarketing/Campaigns/routes.php';
            require __DIR__.'/Ecommerce/OnlineMarketing/AbandonedCarts/routes.php';
        });

        Route::prefix('logistics')->name('business.admin.logistics.')->group(function () {
            require __DIR__.'/Logistics/Delivery/DeliveryList/routes.php';
            require __DIR__.'/Logistics/Courier/CourierBooking/routes.php';
        });

        Route::prefix('authorization')->name('business.admin.authorization.')->group(function () {
            require __DIR__.'/Authorization/Users/routes.php';
            require __DIR__.'/Authorization/Roles/routes.php';
            require __DIR__.'/Authorization/Permissions/routes.php';
            require __DIR__.'/Authorization/AccessControl/routes.php';
        });

        Route::prefix('settings')->name('business.admin.settings.')->group(function () {
            require __DIR__.'/Settings/Business/GeneralSettings/routes.php';
            require __DIR__.'/Settings/Business/Profile/routes.php';
            require __DIR__.'/Settings/Business/Notifications/routes.php';
            require __DIR__.'/Settings/Business/Branding/routes.php';
            require __DIR__.'/Settings/Locations/Warehouses/routes.php';
            require __DIR__.'/Settings/Integrations/ApiKeys/routes.php';
        });

        Route::get('/{module}/{menu}/{child?}', [WorkspaceController::class, 'feature'])->name('business.admin.feature');
    });
});
