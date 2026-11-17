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
        // [slug, English, বাংলা, icon, menus]
        // Menu: [slug, English, বাংলা, icon, children]
        // Child: [slug, English, বাংলা, route]
        // Menu with [] children is directly clickable.
        $structure=[
            ['configurations','Configurations','কনফিগারেশন','settings',[
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
            ]],
            ['sales','Sales','বিক্রয়','shopping-cart',[
                ['orders','Orders','অর্ডার','receipt',[[ 'order-list','Order List','অর্ডার তালিকা',null],['new-order','New Order','নতুন অর্ডার',null],['order-details','Order Details','অর্ডার বিস্তারিত',null]]],
                ['quotations','Quotations','কোটেশন','file-text',[[ 'quotation-list','Quotation List','কোটেশন তালিকা',null],['new-quotation','New Quotation','নতুন কোটেশন',null]]],
                ['invoices','Sales Invoices','বিক্রয় ইনভয়েস','file-check',[[ 'invoice-list','Invoice List','ইনভয়েস তালিকা',null],['new-invoice','New Invoice','নতুন ইনভয়েস',null]]],
                ['returns','Sales Returns','বিক্রয় রিটার্ন','undo-2',[[ 'return-list','Return List','রিটার্ন তালিকা',null],['new-return','New Return','নতুন রিটার্ন',null]]],
                ['customers','Customers','কাস্টমার','users',[[ 'customer-list','Customer List','কাস্টমার তালিকা',null],['customer-groups','Customer Groups','কাস্টমার গ্রুপ',null]]],
                ['discounts','Discounts','ডিসকাউন্ট','badge-percent',[[ 'coupons','Coupons','কুপন',null],['price-rules','Price Rules','মূল্যছাড়ের নিয়ম',null]]]
            ]],
            ['pos','Point of Sale','পয়েন্ট অব সেল','monitor-smartphone',[
                ['counter','POS Counter','পস কাউন্টার','scan-barcode',[[ 'new-sale','New Sale','নতুন বিক্রয়',null],['held-sales','Held Sales','স্থগিত বিক্রয়',null]]],
                ['registers','Registers','ক্যাশ রেজিস্টার','wallet',[[ 'register-list','Register List','রেজিস্টার তালিকা',null],['cash-movements','Cash Movements','নগদ লেনদেন',null]]],
                ['shifts','POS Shifts','পস শিফট','clock',[[ 'open-shift','Open Shift','শিফট শুরু',null],['close-shift','Close Shift','শিফট শেষ',null],['shift-history','Shift History','শিফট ইতিহাস',null]]]
            ]],
            ['inventory','Inventory','ইনভেন্টরি','package',[
                ['stock','Stock','স্টক','warehouse',[[ 'stock-report','Stock Report','স্টক রিপোর্ট',null],['stock-ledger','Stock Ledger','স্টক লেজার',null],['low-stock','Low Stock','কম স্টক',null]]],
                ['movements','Stock Movements','স্টক মুভমেন্ট','arrow-left-right',[[ 'stock-transfer','Stock Transfer','স্টক ট্রান্সফার',null],['stock-adjustment','Stock Adjustment','স্টক সমন্বয়',null],['stock-count','Stock Count','স্টক গণনা',null]]],
                ['masters','Item Masters','আইটেম মাস্টার','layers',[[ 'units','Units','ইউনিট',null],['brands','Brands','ব্র্যান্ড',null],['barcodes','Barcodes','বারকোড',null]]]
            ]],
            ['purchasing','Purchasing','ক্রয়','shopping-bag',[
                ['requisitions','Requisitions','রিকুইজিশন','clipboard-list',[[ 'requisition-list','Requisition List','রিকুইজিশন তালিকা',null],['new-requisition','New Requisition','নতুন রিকুইজিশন',null]]],
                ['purchase-orders','Purchase Orders','ক্রয় আদেশ','file-plus',[[ 'po-list','PO List','ক্রয় আদেশ তালিকা',null],['new-po','New PO','নতুন ক্রয় আদেশ',null]]],
                ['receiving','Goods Receiving','পণ্য গ্রহণ','package-check',[[ 'grn-list','GRN List','পণ্য গ্রহণ তালিকা',null],['new-grn','New GRN','নতুন পণ্য গ্রহণ',null]]],
                ['purchase-returns','Purchase Returns','ক্রয় রিটার্ন','rotate-ccw',[[ 'purchase-return-list','Return List','রিটার্ন তালিকা',null]]]
            ]],
            ['finance','Accounts & Finance','হিসাব ও অর্থব্যবস্থা','landmark',[
                ['chart-of-accounts','Chart of Accounts','হিসাবের খাতসমূহ','list-tree',[[ 'account-list','Account List','হিসাবের খাত তালিকা',null],['account-groups','Account Groups','হিসাবের গ্রুপ',null]]],
                ['ledger','Ledger & Journals','লেজার ও জার্নাল','book-open',[[ 'journal-entries','Journal Entries','জার্নাল এন্ট্রি',null],['general-ledger','General Ledger','জেনারেল লেজার',null]]],
                ['receivables','Receivables','পাওনা হিসাব','hand-coins',[[ 'customer-dues','Customer Dues','কাস্টমারের বকেয়া',null],['collections','Collections','আদায়',null]]],
                ['payables','Payables','দেনা হিসাব','credit-card',[[ 'vendor-bills','Vendor Bills','ভেন্ডর বিল',null],['vendor-payments','Vendor Payments','ভেন্ডর পেমেন্ট',null]]],
                ['banking','Bank & Cash','ব্যাংক ও নগদ','wallet',[[ 'bank-accounts','Bank Accounts','ব্যাংক অ্যাকাউন্ট',null],['bank-transfers','Bank Transfers','ব্যাংক ট্রান্সফার',null],['bank-reconciliation','Bank Reconciliation','ব্যাংক রিকনসিলিয়েশন',null]]],
                ['expenses','Expenses','খরচ','receipt-text',[[ 'expense-list','Expense List','খরচের তালিকা',null],['new-expense','New Expense','নতুন খরচ',null]]],
                ['financial-reports','Financial Reports','আর্থিক রিপোর্ট','chart-no-axes-combined',[[ 'trial-balance','Trial Balance','ট্রায়াল ব্যালেন্স',null],['profit-loss','Profit & Loss','লাভ-ক্ষতি হিসাব',null],['balance-sheet','Balance Sheet','ব্যালেন্স শিট',null],['cash-flow','Cash Flow','নগদ প্রবাহ',null]]]
            ]],
            ['ecommerce','E-Commerce','ই-কমার্স','shopping-basket',[
                ['storefront','Storefront','অনলাইন স্টোর','store',[[ 'pages','Pages','পেজ',null],['theme','Theme','থিম',null],['navigation','Navigation','নেভিগেশন',null]]],
                ['catalog','Online Catalog','অনলাইন ক্যাটালগ','grid-2x2',[[ 'collections','Collections','কালেকশন',null],['featured-products','Featured Products','ফিচার্ড প্রোডাক্ট',null]]],
                ['online-orders','Online Orders','অনলাইন অর্ডার','package-search',[[ 'online-order-list','Order List','অর্ডার তালিকা',null],['fulfillment','Fulfillment','অর্ডার পূরণ',null]]],
                ['checkout','Checkout','চেকআউট','credit-card',[[ 'shipping-methods','Shipping Methods','ডেলিভারি পদ্ধতি',null],['payment-methods','Payment Methods','পেমেন্ট পদ্ধতি',null]]],
                ['online-marketing','Marketing','মার্কেটিং','megaphone',[[ 'campaigns','Campaigns','ক্যাম্পেইন',null],['abandoned-carts','Abandoned Carts','অসম্পূর্ণ কার্ট',null]]]
            ]],
            ['restaurant','Restaurant Operations','রেস্টুরেন্ট পরিচালনা','utensils',[
                ['dining','Tables & Dining','টেবিল ও ডাইনিং','layout-grid',[[ 'table-map','Table Map','টেবিল ম্যাপ',null],['dining-areas','Dining Areas','ডাইনিং এরিয়া',null]]],
                ['food-menu','Food Menu','খাবারের মেনু','book-open',[[ 'menu-items','Menu Items','মেনু আইটেম',null],['combos','Combos','কম্বো',null],['modifiers','Modifiers','অপশন ও অ্যাডঅন',null]]],
                ['kitchen-display','Kitchen Display','কিচেন ডিসপ্লে','chef-hat',[[ 'kitchen-tickets','Kitchen Tickets','কিচেন টিকিট',null],['kitchen-stations','Kitchen Stations','কিচেন স্টেশন',null]]],
                ['table-bookings','Table Reservations','টেবিল রিজার্ভেশন','calendar-days',[[ 'reservation-list','Reservation List','রিজার্ভেশন তালিকা',null],['new-reservation','New Reservation','নতুন রিজার্ভেশন',null]]],
                ['buffet','Buffet','বুফে','salad',[[ 'buffet-sessions','Buffet Sessions','বুফে সেশন',null],['buffet-production','Buffet Production','বুফে প্রোডাকশন',null]]]
            ]],
            ['pharmacy','Pharmacy','ফার্মেসি','pill',[
                ['medicines','Medicines','ওষুধ','tablets',[[ 'medicine-list','Medicine List','ওষুধ তালিকা',null],['medicine-categories','Categories','ক্যাটাগরি',null]]],
                ['medicine-batches','Batches & Expiry','ব্যাচ ও মেয়াদ','calendar-x',[[ 'batch-list','Batch List','ব্যাচ তালিকা',null],['expiry-alerts','Expiry Alerts','মেয়াদ সতর্কতা',null]]],
                ['dispensing','Dispensing','ওষুধ বিতরণ','hand-heart',[[ 'dispense-list','Dispense List','বিতরণ তালিকা',null],['dispense-returns','Dispense Returns','বিতরণ রিটার্ন',null]]]
            ]],
            ['education','Education','শিক্ষা ব্যবস্থাপনা','graduation-cap',[
                ['students','Students','শিক্ষার্থী','users',[[ 'student-list','Student List','শিক্ষার্থী তালিকা',null],['guardians','Guardians','অভিভাবক',null]]],
                ['courses','Courses','কোর্স','book-open',[[ 'course-list','Course List','কোর্স তালিকা',null],['batches','Batches','ব্যাচ',null]]],
                ['classes','Classes','ক্লাস','calendar',[[ 'timetable','Timetable','ক্লাস রুটিন',null],['attendance','Attendance','উপস্থিতি',null]]],
                ['exams','Exams','পরীক্ষা','clipboard-check',[[ 'exam-schedule','Exam Schedule','পরীক্ষার সূচি',null],['results','Results','ফলাফল',null]]],
                ['student-fees','Fees','ফি','banknote',[[ 'fee-structure','Fee Structure','ফি কাঠামো',null],['fee-collection','Fee Collection','ফি আদায়',null]]]
            ]],
            ['laundry','Laundry','লন্ড্রি','shirt',[
                ['laundry-orders','Laundry Orders','লন্ড্রি অর্ডার','clipboard-list',[[ 'intake','Intake','কাপড় গ্রহণ',null],['order-tracking','Order Tracking','অর্ডার ট্র্যাকিং',null]]],
                ['processing','Processing','প্রসেসিং','washing-machine',[[ 'wash-queue','Wash Queue','ধোয়ার তালিকা',null],['quality-check','Quality Check','মান যাচাই',null]]],
                ['laundry-delivery','Delivery','ডেলিভারি','bike',[[ 'ready-orders','Ready Orders','প্রস্তুত অর্ডার',null],['delivered-orders','Delivered Orders','ডেলিভারি সম্পন্ন',null]]]
            ]],
            ['hr','Human Resources','মানবসম্পদ','users-round',[
                ['employees','Employees','কর্মী','user-round',[[ 'employee-list','Employee List','কর্মী তালিকা',null],['departments','Departments','বিভাগ',null]]],
                ['attendance','Attendance','উপস্থিতি','calendar-check',[[ 'daily-attendance','Daily Attendance','দৈনিক উপস্থিতি',null],['rosters','Rosters','ডিউটি রোস্টার',null]]],
                ['leave','Leave','ছুটি','calendar-off',[[ 'leave-requests','Leave Requests','ছুটির আবেদন',null],['leave-policies','Leave Policies','ছুটির নীতি',null]]],
                ['payroll','Payroll','বেতন','wallet',[[ 'salary-structure','Salary Structure','বেতন কাঠামো',null],['payroll-runs','Payroll Runs','বেতন প্রক্রিয়া',null]]]
            ]],
            ['assets','Assets & Maintenance','সম্পদ ও রক্ষণাবেক্ষণ','wrench',[
                ['fixed-assets','Fixed Assets','স্থায়ী সম্পদ','package',[[ 'asset-register','Asset Register','সম্পদ রেজিস্টার',null],['asset-assignments','Asset Assignments','সম্পদ বরাদ্দ',null]]],
                ['maintenance','Maintenance','রক্ষণাবেক্ষণ','hammer',[[ 'maintenance-requests','Maintenance Requests','রক্ষণাবেক্ষণ আবেদন',null],['maintenance-schedule','Maintenance Schedule','রক্ষণাবেক্ষণ সূচি',null]]],
                ['depreciation','Depreciation','অবচয়','chart-no-axes-column-decreasing',[[ 'depreciation-schedule','Depreciation Schedule','অবচয় সূচি',null]]]
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
                'customers'=>[], 'suppliers'=>[], 'units'=>[], 'sizes'=>[],
                'colors'=>[], 'brands'=>[], 'categories'=>[], 'sub-categories'=>[],'products'=>[],
                'taxes'=>[], 'currencies'=>[], 'payment-methods'=>[], 'shipping-methods'=>[],
            ],
            'authorization'=>['users'=>'*','roles'=>'*','permissions'=>'*','access-control'=>'*'],
            'settings'=>['navigation-management'=>'*','business'=>'*','team'=>'*','locations'=>'*','taxes'=>'*','integrations'=>'*','notifications'=>'*','security'=>'*','audit'=>'*'],
            'support'=>['tickets'=>'*','feedback'=>'*'],
        ];
        $profiles=[
            'retail'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*','discounts'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*','online-marketing'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'online'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*','discounts'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*','online-marketing'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['purchase-orders'=>'*','receiving'=>'*'],
                'finance'=>['receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'logistics'=>['shipments'=>'*','delivery'=>'*','cod'=>'*'],
            ],
            'food'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','customers'=>'*','discounts'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'restaurant'=>['dining'=>'*','food-menu'=>'*','kitchen-display'=>'*','table-bookings'=>'*','buffet'=>'*'],
                'kitchen'=>['recipes'=>'*','preparation'=>'*','food-costing'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'pharmacy'=>[
                'pharmacy'=>['medicines'=>'*','medicine-batches'=>'*','dispensing'=>'*'],
                'sales'=>['orders'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'education'=>[
                'education'=>['students'=>'*','courses'=>'*','classes'=>'*','exams'=>'*','student-fees'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','leave'=>'*','payroll'=>'*'],
            ],
        ];

        // Category slug => [English, বাংলা, profile]. Add new categories here.
        $categories=[
            'retail'=>['Retail Shop','খুচরা দোকান','retail'],
            'grocery'=>['Grocery','মুদি দোকান','retail'],
            // 'supermarket'=>['Supermarket','সুপারশপ','retail'],
            // 'fashion'=>['Fashion & Clothing','ফ্যাশন ও পোশাক','retail'],
            // 'electronics'=>['Electronics','ইলেকট্রনিকস','retail'],
            // 'hardware'=>['Hardware Store','হার্ডওয়্যার দোকান','retail'],
            // 'furniture'=>['Furniture Store','আসবাবপত্রের দোকান','retail'],
            // 'bookstore'=>['Bookstore','বইয়ের দোকান','retail'],
            // 'jewelry'=>['Jewelry Store','গয়নার দোকান','retail'],
            // 'pet-shop'=>['Pet Shop','পোষাপ্রাণীর দোকান','retail'],
            // 'wholesale'=>['Wholesale','পাইকারি ব্যবসা','wholesale'],
            // 'distribution'=>['Distribution Business','ডিস্ট্রিবিউশন ব্যবসা','wholesale'],
            // 'ecommerce'=>['E-Commerce','ই-কমার্স','online'],
            // 'online-store'=>['Online Store','অনলাইন দোকান','online'],
            // 'restaurant'=>['Restaurant','রেস্টুরেন্ট','food'],
            // 'cafe'=>['Cafe','ক্যাফে','food'],
            // 'bakery'=>['Bakery','বেকারি','food'],
            // 'fast-food'=>['Fast Food','ফাস্ট ফুড','food'],
            // 'catering'=>['Catering','ক্যাটারিং','food'],
            // 'cloud-kitchen'=>['Cloud Kitchen','ক্লাউড কিচেন','cloud-food'],
            // 'hotel'=>['Hotel','হোটেল','hospitality'],
            // 'resort'=>['Resort','রিসোর্ট','hospitality'],
            // 'guesthouse'=>['Guesthouse','গেস্টহাউস','hospitality'],
            // 'salon'=>['Beauty Salon','বিউটি সেলুন','beauty'],
            // 'barbershop'=>['Barbershop','বারবার শপ','beauty'],
            // 'spa'=>['Spa & Wellness','স্পা ও ওয়েলনেস','beauty'],
            // 'gym'=>['Gym','জিম','fitness'],
            // 'fitness-center'=>['Fitness Center','ফিটনেস সেন্টার','fitness'],
            // 'yoga-studio'=>['Yoga Studio','যোগব্যায়াম স্টুডিও','fitness'],
            // 'clinic'=>['Clinic','ক্লিনিক','clinic'],
            // 'dental-clinic'=>['Dental Clinic','ডেন্টাল ক্লিনিক','clinic'],
            // 'physiotherapy'=>['Physiotherapy','ফিজিওথেরাপি','clinic'],
            // 'diagnostic-center'=>['Diagnostic Center','ডায়াগনস্টিক সেন্টার','diagnostic'],
            // 'pharmacy'=>['Pharmacy','ফার্মেসি','pharmacy'],
            // 'school'=>['School','স্কুল','education'],
            // 'college'=>['College','কলেজ','education'],
            // 'coaching-center'=>['Coaching Center','কোচিং সেন্টার','education'],
            // 'training-center'=>['Training Center','প্রশিক্ষণ কেন্দ্র','education'],
            // 'manufacturing'=>['Manufacturing','উৎপাদন শিল্প','factory'],
            // 'garments-factory'=>['Garments Factory','পোশাক কারখানা','factory'],
            // 'food-factory'=>['Food Processing Factory','খাদ্য প্রক্রিয়াজাত কারখানা','factory'],
            // 'construction'=>['Construction Company','নির্মাণ প্রতিষ্ঠান','construction'],
            // 'contractor'=>['Contractor','ঠিকাদারি প্রতিষ্ঠান','construction'],
            // 'real-estate'=>['Real Estate','রিয়েল এস্টেট','property'],
            // 'property-management'=>['Property Management','সম্পত্তি ব্যবস্থাপনা','property'],
            // 'logistics'=>['Logistics','লজিস্টিকস','logistics'],
            // 'courier'=>['Courier Service','কুরিয়ার সেবা','logistics'],
            // 'transport'=>['Transport Service','পরিবহন সেবা','logistics'],
            // 'car-workshop'=>['Car Workshop','গাড়ির ওয়ার্কশপ','garage'],
            // 'auto-dealership'=>['Auto Dealership','গাড়ি বিক্রয় কেন্দ্র','vehicle-sales'],
            // 'vehicle-rental'=>['Vehicle Rental','যানবাহন ভাড়া','rental'],
            // 'equipment-rental'=>['Equipment Rental','যন্ত্রপাতি ভাড়া','rental'],
            // 'event-management'=>['Event Management','ইভেন্ট ম্যানেজমেন্ট','events'],
            // 'event-venue'=>['Event Venue','ইভেন্ট ভেন্যু','events'],
            // 'travel-agency'=>['Travel Agency','ট্রাভেল এজেন্সি','travel'],
            // 'tour-operator'=>['Tour Operator','ট্যুর অপারেটর','travel'],
            // 'agriculture'=>['Agriculture','কৃষি ব্যবসা','agriculture'],
            // 'dairy-farm'=>['Dairy Farm','দুগ্ধ খামার','agriculture'],
            // 'livestock-farm'=>['Livestock Farm','পশুর খামার','agriculture'],
            // 'laundry'=>['Laundry','লন্ড্রি','laundry'],
            // 'dry-cleaning'=>['Dry Cleaning','ড্রাই ক্লিনিং','laundry'],
            // 'service'=>['Service Business','সেবা ব্যবসা','services'],
            // 'repair-service'=>['Repair Service','মেরামত সেবা','services'],
            // 'it-service'=>['IT Services','আইটি সেবা','services'],
            // 'consulting'=>['Consulting','পরামর্শ সেবা','services'],
            // 'marketing-agency'=>['Marketing Agency','মার্কেটিং এজেন্সি','services'],
            // 'law-firm'=>['Law Firm','আইন সেবা প্রতিষ্ঠান','services'],
            // 'subscription-business'=>['Subscription Business','সাবস্ক্রিপশন ব্যবসা','subscriptions'],
            // 'saas'=>['SaaS Business','সাস ব্যবসা','subscriptions'],
            // 'nonprofit'=>['Nonprofit Organization','অলাভজনক সংস্থা','nonprofit'],
            // 'ngo'=>['NGO','এনজিও','nonprofit']
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
