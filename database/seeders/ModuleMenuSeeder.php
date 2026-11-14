<?php
namespace Database\Seeders;

use App\Models\{Module,Menu,ChildMenu,BusinessCategory,BusinessCategoryModule,BusinessCategoryMenu,BusinessCategoryChildMenu};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ModuleMenuSeeder extends Seeder
{
    public function run():void
    {
        // [slug, English, বাংলা, icon, menus]
        // Menu: [slug, English, বাংলা, icon, children]
        // Child: [slug, English, বাংলা, route]
        // Menu with [] children is directly clickable.
        $structure=[
            ['sales','Sales','বিক্রয়','shopping-cart',[
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
                ['products','Products','প্রোডাক্ট','boxes',[[ 'product-list','Product List','প্রোডাক্ট তালিকা',null],['new-product','New Product','নতুন প্রোডাক্ট',null],['categories','Categories','ক্যাটাগরি',null],['variants','Variants','ভ্যারিয়েন্ট',null]]],
                ['stock','Stock','স্টক','warehouse',[[ 'stock-report','Stock Report','স্টক রিপোর্ট',null],['stock-ledger','Stock Ledger','স্টক লেজার',null],['low-stock','Low Stock','কম স্টক',null]]],
                ['movements','Stock Movements','স্টক মুভমেন্ট','arrow-left-right',[[ 'stock-transfer','Stock Transfer','স্টক ট্রান্সফার',null],['stock-adjustment','Stock Adjustment','স্টক সমন্বয়',null],['stock-count','Stock Count','স্টক গণনা',null]]],
                ['masters','Item Masters','আইটেম মাস্টার','layers',[[ 'units','Units','ইউনিট',null],['brands','Brands','ব্র্যান্ড',null],['barcodes','Barcodes','বারকোড',null]]]
            ]],
            ['purchasing','Purchasing','ক্রয়','shopping-bag',[
                ['suppliers','Suppliers','সরবরাহকারী','truck',[[ 'supplier-list','Supplier List','সরবরাহকারী তালিকা',null],['supplier-statements','Supplier Statements','সরবরাহকারীর হিসাব',null]]],
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
            ['crm','Customer Relations','কাস্টমার সম্পর্ক','contact-round',[
                ['contacts','Contacts','যোগাযোগ','contact',[[ 'contact-list','Contact List','যোগাযোগ তালিকা',null],['segments','Segments','সেগমেন্ট',null]]],
                ['leads','Leads & Pipeline','লিড ও পাইপলাইন','target',[[ 'lead-list','Lead List','লিড তালিকা',null],['pipeline','Pipeline','পাইপলাইন',null]]],
                ['followups','Follow-ups','ফলোআপ','calendar-check',[[ 'followup-list','Follow-up List','ফলোআপ তালিকা',null],['activities','Activities','কার্যক্রম',null]]],
                ['loyalty','Loyalty','লয়্যালটি','gift',[[ 'loyalty-points','Loyalty Points','লয়্যালটি পয়েন্ট',null],['loyalty-tiers','Loyalty Tiers','লয়্যালটি স্তর',null]]]
            ]],
            ['ecommerce','E-Commerce','ই-কমার্স','shopping-basket',[
                ['storefront','Storefront','অনলাইন স্টোর','store',[[ 'pages','Pages','পেজ',null],['theme','Theme','থিম',null],['navigation','Navigation','নেভিগেশন',null]]],
                ['catalog','Online Catalog','অনলাইন ক্যাটালগ','grid-2x2',[[ 'collections','Collections','কালেকশন',null],['featured-products','Featured Products','ফিচার্ড প্রোডাক্ট',null]]],
                ['online-orders','Online Orders','অনলাইন অর্ডার','package-search',[[ 'online-order-list','Order List','অর্ডার তালিকা',null],['fulfillment','Fulfillment','অর্ডার পূরণ',null]]],
                ['checkout','Checkout','চেকআউট','credit-card',[[ 'shipping-methods','Shipping Methods','ডেলিভারি পদ্ধতি',null],['payment-methods','Payment Methods','পেমেন্ট পদ্ধতি',null]]],
                ['online-marketing','Marketing','মার্কেটিং','megaphone',[[ 'campaigns','Campaigns','ক্যাম্পেইন',null],['abandoned-carts','Abandoned Carts','অসম্পূর্ণ কার্ট',null]]]
            ]],
            ['wholesale','Wholesale','পাইকারি বিক্রয়','container',[
                ['price-tiers','Price Tiers','দরের স্তর','tags',[[ 'price-lists','Price Lists','মূল্য তালিকা',null],['bulk-discounts','Bulk Discounts','বাল্ক ডিসকাউন্ট',null]]],
                ['distribution','Distribution','ডিস্ট্রিবিউশন','truck',[[ 'allocations','Allocations','বরাদ্দ',null],['dispatch','Dispatch','ডিসপ্যাচ',null]]],
                ['trade-credit','Trade Credit','বাকিতে বিক্রয়','wallet-cards',[[ 'credit-limits','Credit Limits','ক্রেডিট সীমা',null],['credit-statements','Credit Statements','বকেয়া বিবরণী',null]]]
            ]],
            ['restaurant','Restaurant Operations','রেস্টুরেন্ট পরিচালনা','utensils',[
                ['dining','Tables & Dining','টেবিল ও ডাইনিং','layout-grid',[[ 'table-map','Table Map','টেবিল ম্যাপ',null],['dining-areas','Dining Areas','ডাইনিং এরিয়া',null]]],
                ['food-menu','Food Menu','খাবারের মেনু','book-open',[[ 'menu-items','Menu Items','মেনু আইটেম',null],['combos','Combos','কম্বো',null],['modifiers','Modifiers','অপশন ও অ্যাডঅন',null]]],
                ['kitchen-display','Kitchen Display','কিচেন ডিসপ্লে','chef-hat',[[ 'kitchen-tickets','Kitchen Tickets','কিচেন টিকিট',null],['kitchen-stations','Kitchen Stations','কিচেন স্টেশন',null]]],
                ['table-bookings','Table Reservations','টেবিল রিজার্ভেশন','calendar-days',[[ 'reservation-list','Reservation List','রিজার্ভেশন তালিকা',null],['new-reservation','New Reservation','নতুন রিজার্ভেশন',null]]],
                ['buffet','Buffet','বুফে','salad',[[ 'buffet-sessions','Buffet Sessions','বুফে সেশন',null],['buffet-production','Buffet Production','বুফে প্রোডাকশন',null]]]
            ]],
            ['kitchen','Recipe & Production','রেসিপি ও উৎপাদন','cooking-pot',[
                ['recipes','Recipes','রেসিপি','notebook-tabs',[[ 'recipe-list','Recipe List','রেসিপি তালিকা',null],['ingredient-mapping','Ingredient Mapping','উপকরণ ম্যাপিং',null]]],
                ['preparation','Preparation','প্রস্তুতি','flame',[[ 'production-batches','Production Batches','উৎপাদন ব্যাচ',null],['ingredient-consumption','Ingredient Consumption','উপকরণ ব্যবহার',null]]],
                ['food-costing','Food Costing','খাবারের খরচ','calculator',[[ 'recipe-costing','Recipe Costing','রেসিপি কস্টিং',null],['wastage','Wastage','অপচয়',null]]]
            ]],
            ['hotel','Hotel Operations','হোটেল পরিচালনা','bed-double',[
                ['rooms','Rooms','রুম','bed',[[ 'room-list','Room List','রুম তালিকা',null],['room-types','Room Types','রুমের ধরন',null]]],
                ['rates','Room Rates','রুম ভাড়া','banknote',[[ 'rate-plans','Rate Plans','ভাড়ার প্ল্যান',null],['seasonal-rates','Seasonal Rates','মৌসুমি ভাড়া',null]]],
                ['room-reservations','Reservations','রিজার্ভেশন','calendar',[[ 'booking-list','Booking List','বুকিং তালিকা',null],['booking-calendar','Booking Calendar','বুকিং ক্যালেন্ডার',null]]],
                ['front-desk','Front Desk','ফ্রন্ট ডেস্ক','concierge-bell',[[ 'check-in','Check In','চেক ইন',null],['check-out','Check Out','চেক আউট',null],['guest-list','Guest List','অতিথি তালিকা',null]]],
                ['folios','Guest Folios','গেস্ট ফোলিও','files',[[ 'folio-list','Folio List','ফোলিও তালিকা',null],['folio-payments','Folio Payments','ফোলিও পেমেন্ট',null]]],
                ['housekeeping','Housekeeping','হাউসকিপিং','spray-can',[[ 'room-status','Room Status','রুম স্ট্যাটাস',null],['cleaning-tasks','Cleaning Tasks','পরিচ্ছন্নতার কাজ',null]]]
            ]],
            ['booking','Bookings & Services','বুকিং ও সেবা','calendar-clock',[
                ['services','Services','সেবাসমূহ','briefcase-business',[[ 'service-list','Service List','সেবার তালিকা',null],['service-categories','Service Categories','সেবার ক্যাটাগরি',null]]],
                ['appointments','Appointments','অ্যাপয়েন্টমেন্ট','calendar-check',[[ 'appointment-list','Appointment List','অ্যাপয়েন্টমেন্ট তালিকা',null],['appointment-calendar','Appointment Calendar','অ্যাপয়েন্টমেন্ট ক্যালেন্ডার',null]]],
                ['availability','Availability','সময় ও উপলব্ধতা','clock-4',[[ 'working-hours','Working Hours','কাজের সময়',null],['blocked-slots','Blocked Slots','বন্ধ স্লট',null]]],
                ['service-packages','Packages','প্যাকেজ','gift',[[ 'package-list','Package List','প্যাকেজ তালিকা',null],['gift-cards','Gift Cards','গিফট কার্ড',null]]]
            ]],
            ['beauty','Beauty & Wellness','বিউটি ও ওয়েলনেস','scissors',[
                ['treatments','Treatments','ট্রিটমেন্ট','sparkles',[[ 'treatment-list','Treatment List','ট্রিটমেন্ট তালিকা',null],['treatment-rooms','Treatment Rooms','ট্রিটমেন্ট রুম',null]]],
                ['professionals','Professionals','সেবা প্রদানকারী','users-round',[[ 'stylist-list','Staff List','কর্মী তালিকা',null],['commissions','Commissions','কমিশন',null]]]
            ]],
            ['fitness','Fitness Operations','ফিটনেস পরিচালনা','dumbbell',[
                ['classes','Classes','ক্লাস','calendar-range',[[ 'class-schedule','Class Schedule','ক্লাস সূচি',null],['class-attendance','Class Attendance','ক্লাস উপস্থিতি',null]]],
                ['training','Personal Training','ব্যক্তিগত প্রশিক্ষণ','activity',[[ 'pt-sessions','PT Sessions','প্রশিক্ষণ সেশন',null],['workout-plans','Workout Plans','ব্যায়াম পরিকল্পনা',null]]],
                ['gym-access','Gym Access','জিম প্রবেশ','scan-line',[[ 'member-checkin','Member Check-in','সদস্য চেক ইন',null]]]
            ]],
            ['membership','Membership','সদস্যপদ','id-card',[
                ['members','Members','সদস্য','users',[[ 'member-list','Member List','সদস্য তালিকা',null],['member-cards','Member Cards','সদস্য কার্ড',null]]],
                ['membership-plans','Membership Plans','সদস্যপদ প্ল্যান','layers-3',[[ 'plan-list','Plan List','প্ল্যান তালিকা',null],['renewals','Renewals','নবায়ন',null]]]
            ]],
            ['healthcare','Healthcare','স্বাস্থ্যসেবা','heart-pulse',[
                ['patients','Patients','রোগী','contact',[[ 'patient-list','Patient List','রোগীর তালিকা',null],['patient-history','Patient History','রোগীর ইতিহাস',null]]],
                ['consultations','Consultations','পরামর্শ','stethoscope',[[ 'visit-list','Visit List','ভিজিট তালিকা',null],['clinical-notes','Clinical Notes','চিকিৎসা নোট',null]]],
                ['prescriptions','Prescriptions','প্রেসক্রিপশন','pill',[[ 'prescription-list','Prescription List','প্রেসক্রিপশন তালিকা',null],['prescription-templates','Prescription Templates','প্রেসক্রিপশন টেমপ্লেট',null]]]
            ]],
            ['diagnostics','Diagnostics & Lab','ডায়াগনস্টিক ও ল্যাব','microscope',[
                ['lab-tests','Lab Tests','ল্যাব টেস্ট','flask-conical',[[ 'test-catalog','Test Catalog','টেস্ট ক্যাটালগ',null],['test-orders','Test Orders','টেস্ট অর্ডার',null]]],
                ['lab-processing','Lab Processing','ল্যাব প্রসেসিং','test-tube',[[ 'sample-collection','Sample Collection','নমুনা সংগ্রহ',null],['test-results','Test Results','টেস্ট ফলাফল',null]]]
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
            ['manufacturing','Manufacturing','উৎপাদন ব্যবস্থাপনা','factory',[
                ['bill-of-materials','Bill of Materials','উপকরণ তালিকা','list-check',[[ 'bom-list','BOM List','উপকরণ তালিকা',null],['bom-versions','BOM Versions','উপকরণ সংস্করণ',null]]],
                ['work-orders','Work Orders','কাজের আদেশ','clipboard-list',[[ 'work-order-list','Work Order List','কাজের আদেশ তালিকা',null],['new-work-order','New Work Order','নতুন কাজের আদেশ',null]]],
                ['production','Production','উৎপাদন','cog',[[ 'production-plan','Production Plan','উৎপাদন পরিকল্পনা',null],['production-entry','Production Entry','উৎপাদন এন্ট্রি',null]]],
                ['quality','Quality Control','মান নিয়ন্ত্রণ','shield-check',[[ 'inspections','Inspections','পরিদর্শন',null],['quality-issues','Quality Issues','মানগত সমস্যা',null]]]
            ]],
            ['projects','Project Management','প্রজেক্ট ব্যবস্থাপনা','folder-kanban',[
                ['projects','Projects','প্রজেক্ট','briefcase',[[ 'project-list','Project List','প্রজেক্ট তালিকা',null],['new-project','New Project','নতুন প্রজেক্ট',null]]],
                ['tasks','Tasks','কাজ','list-todo',[[ 'task-board','Task Board','টাস্ক বোর্ড',null],['task-list','Task List','কাজের তালিকা',null]]],
                ['timesheets','Timesheets','সময় হিসাব','timer',[[ 'time-entries','Time Entries','সময় এন্ট্রি',null],['time-approvals','Approvals','অনুমোদন',null]]],
                ['project-budget','Project Budget','প্রজেক্ট বাজেট','chart-pie',[[ 'project-costs','Project Costs','প্রজেক্ট খরচ',null],['milestones','Milestones','মাইলস্টোন',null]]]
            ]],
            ['construction','Construction','নির্মাণ ব্যবস্থাপনা','hard-hat',[
                ['sites','Sites','সাইট','map-pin',[[ 'site-list','Site List','সাইট তালিকা',null],['site-diary','Site Diary','সাইট ডায়েরি',null]]],
                ['boq','BOQ','কাজ ও উপকরণের হিসাব','ruler',[[ 'boq-list','BOQ List','বিওকিউ তালিকা',null],['estimates','Estimates','প্রাক্কলন',null]]],
                ['contractors','Contractors','ঠিকাদার','users',[[ 'contractor-list','Contractor List','ঠিকাদার তালিকা',null],['work-contracts','Work Contracts','কাজের চুক্তি',null]]],
                ['site-progress','Site Progress','কাজের অগ্রগতি','chart-no-axes-column',[[ 'daily-progress','Daily Progress','দৈনিক অগ্রগতি',null],['material-usage','Material Usage','উপকরণ ব্যবহার',null]]]
            ]],
            ['logistics','Logistics & Delivery','লজিস্টিকস ও ডেলিভারি','truck',[
                ['shipments','Shipments','শিপমেন্ট','package',[[ 'shipment-list','Shipment List','শিপমেন্ট তালিকা',null],['new-shipment','New Shipment','নতুন শিপমেন্ট',null]]],
                ['delivery','Delivery','ডেলিভারি','route',[[ 'delivery-runs','Delivery Runs','ডেলিভারি রুট',null],['proof-of-delivery','Proof of Delivery','ডেলিভারি প্রমাণ',null]]],
                ['fleet','Fleet','যানবাহন বহর','car-front',[[ 'vehicle-list','Vehicle List','যানবাহন তালিকা',null],['driver-list','Driver List','ড্রাইভার তালিকা',null]]],
                ['cod','Cash on Delivery','ক্যাশ অন ডেলিভারি','hand-coins',[[ 'cod-collections','COD Collections','সিওডি আদায়',null],['cod-settlements','COD Settlements','সিওডি নিষ্পত্তি',null]]]
            ]],
            ['property','Property Management','সম্পত্তি ব্যবস্থাপনা','building-2',[
                ['properties','Properties','সম্পত্তি','building',[[ 'property-list','Property List','সম্পত্তির তালিকা',null],['property-types','Property Types','সম্পত্তির ধরন',null]]],
                ['units','Units','ইউনিট','door-open',[[ 'unit-list','Unit List','ইউনিট তালিকা',null],['unit-availability','Availability','খালি ইউনিট',null]]],
                ['tenants','Tenants','ভাড়াটিয়া','users',[[ 'tenant-list','Tenant List','ভাড়াটিয়ার তালিকা',null],['tenant-documents','Documents','কাগজপত্র',null]]],
                ['leases','Leases','ভাড়া চুক্তি','file-signature',[[ 'lease-list','Lease List','চুক্তি তালিকা',null],['renewals','Renewals','নবায়ন',null]]],
                ['rent','Rent Collection','ভাড়া আদায়','banknote',[[ 'rent-invoices','Rent Invoices','ভাড়ার ইনভয়েস',null],['rent-payments','Rent Payments','ভাড়া পেমেন্ট',null]]]
            ]],
            ['automotive','Automotive','অটোমোটিভ','car',[
                ['vehicles','Vehicles','যানবাহন','car-front',[[ 'vehicle-records','Vehicle Records','যানবাহন রেকর্ড',null],['vehicle-inventory','Vehicle Inventory','যানবাহন স্টক',null]]],
                ['service-jobs','Service Jobs','সার্ভিস জব','wrench',[[ 'job-cards','Job Cards','জব কার্ড',null],['service-history','Service History','সার্ভিস ইতিহাস',null]]],
                ['spare-parts','Spare Parts','যন্ত্রাংশ','cog',[[ 'parts-catalog','Parts Catalog','যন্ত্রাংশ ক্যাটালগ',null],['parts-usage','Parts Usage','যন্ত্রাংশ ব্যবহার',null]]]
            ]],
            ['rental','Rentals','ভাড়া সেবা','calendar-range',[
                ['rental-items','Rental Items','ভাড়ার পণ্য','boxes',[[ 'item-list','Item List','পণ্য তালিকা',null],['availability-calendar','Availability Calendar','উপলব্ধতার ক্যালেন্ডার',null]]],
                ['rental-contracts','Rental Contracts','ভাড়া চুক্তি','file-text',[[ 'contract-list','Contract List','চুক্তি তালিকা',null],['new-contract','New Contract','নতুন চুক্তি',null]]],
                ['rental-returns','Rental Returns','ভাড়া ফেরত','undo-2',[[ 'due-returns','Due Returns','ফেরত বাকি',null],['damage-charges','Damage Charges','ক্ষতিপূরণ চার্জ',null]]]
            ]],
            ['events','Events','ইভেন্ট ব্যবস্থাপনা','party-popper',[
                ['event-listing','Events','ইভেন্ট','calendar-days',[[ 'event-list','Event List','ইভেন্ট তালিকা',null],['new-event','New Event','নতুন ইভেন্ট',null]]],
                ['tickets','Tickets','টিকিট','ticket',[[ 'ticket-types','Ticket Types','টিকিটের ধরন',null],['ticket-sales','Ticket Sales','টিকিট বিক্রয়',null]]],
                ['attendees','Attendees','অংশগ্রহণকারী','users',[[ 'attendee-list','Attendee List','অংশগ্রহণকারী তালিকা',null],['event-checkin','Check-in','চেক ইন',null]]],
                ['venues','Venues','ভেন্যু','map-pin',[[ 'venue-list','Venue List','ভেন্যু তালিকা',null],['venue-booking','Venue Booking','ভেন্যু বুকিং',null]]]
            ]],
            ['travel','Travel & Tours','ভ্রমণ ও ট্যুর','plane',[
                ['tour-packages','Tour Packages','ট্যুর প্যাকেজ','map',[[ 'package-list','Package List','প্যাকেজ তালিকা',null],['package-pricing','Package Pricing','প্যাকেজ মূল্য',null]]],
                ['itineraries','Itineraries','ভ্রমণসূচি','route',[[ 'itinerary-list','Itinerary List','ভ্রমণসূচি তালিকা',null]]],
                ['tour-bookings','Tour Bookings','ট্যুর বুকিং','calendar-check',[[ 'tour-booking-list','Booking List','বুকিং তালিকা',null],['travellers','Travellers','ভ্রমণকারী',null]]]
            ]],
            ['agriculture','Agriculture','কৃষি ব্যবস্থাপনা','sprout',[
                ['farms','Farms','খামার','map-pin',[[ 'farm-list','Farm List','খামার তালিকা',null],['plots','Plots','জমির প্লট',null]]],
                ['crops','Crops','ফসল','wheat',[[ 'crop-plans','Crop Plans','ফসল পরিকল্পনা',null],['crop-activities','Crop Activities','কৃষিকাজ',null]]],
                ['livestock','Livestock','পশুপালন','beef',[[ 'animal-records','Animal Records','পশুর রেকর্ড',null],['feed-management','Feed Management','খাদ্য ব্যবস্থাপনা',null]]],
                ['harvest','Harvest','ফসল সংগ্রহ','package-check',[[ 'harvest-entry','Harvest Entry','ফসল সংগ্রহ এন্ট্রি',null],['yield-reports','Yield Reports','ফলন রিপোর্ট',null]]]
            ]],
            ['laundry','Laundry','লন্ড্রি','shirt',[
                ['laundry-orders','Laundry Orders','লন্ড্রি অর্ডার','clipboard-list',[[ 'intake','Intake','কাপড় গ্রহণ',null],['order-tracking','Order Tracking','অর্ডার ট্র্যাকিং',null]]],
                ['processing','Processing','প্রসেসিং','washing-machine',[[ 'wash-queue','Wash Queue','ধোয়ার তালিকা',null],['quality-check','Quality Check','মান যাচাই',null]]],
                ['laundry-delivery','Delivery','ডেলিভারি','bike',[[ 'ready-orders','Ready Orders','প্রস্তুত অর্ডার',null],['delivered-orders','Delivered Orders','ডেলিভারি সম্পন্ন',null]]]
            ]],
            ['subscriptions','Subscriptions','সাবস্ক্রিপশন','repeat',[
                ['plans','Plans','প্ল্যান','layers',[[ 'plan-list','Plan List','প্ল্যান তালিকা',null],['plan-features','Plan Features','প্ল্যান ফিচার',null]]],
                ['subscribers','Subscribers','সাবস্ক্রাইবার','users',[[ 'subscriber-list','Subscriber List','সাবস্ক্রাইবার তালিকা',null],['subscription-renewals','Renewals','নবায়ন',null]]],
                ['recurring-billing','Recurring Billing','নিয়মিত বিলিং','receipt',[[ 'billing-cycles','Billing Cycles','বিলিং চক্র',null],['billing-history','Billing History','বিলিং ইতিহাস',null]]]
            ]],
            ['nonprofit','Nonprofit','অলাভজনক সংস্থা','hand-heart',[
                ['donors','Donors','দাতা','users',[[ 'donor-list','Donor List','দাতা তালিকা',null],['donor-groups','Donor Groups','দাতা গ্রুপ',null]]],
                ['fundraising','Fundraising','তহবিল সংগ্রহ','heart-handshake',[[ 'campaign-list','Campaign List','ক্যাম্পেইন তালিকা',null],['donations','Donations','অনুদান',null]]],
                ['programs','Programs','কর্মসূচি','folder-heart',[[ 'program-list','Program List','কর্মসূচি তালিকা',null],['beneficiaries','Beneficiaries','সুবিধাভোগী',null]]]
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
            ['analytics','Analytics & Reports','বিশ্লেষণ ও রিপোর্ট','chart-column',[
                ['overview','Overview','ওভারভিউ','layout-dashboard',[]],
                ['reports','Reports','রিপোর্ট','file-chart-column',[[ 'sales-report','Sales Report','বিক্রয় রিপোর্ট',null],['inventory-report','Inventory Report','ইনভেন্টরি রিপোর্ট',null],['financial-report','Financial Report','আর্থিক রিপোর্ট',null],['operations-report','Operations Report','অপারেশন রিপোর্ট',null]]]
            ]],
            ['authorization','Authorization','অনুমতি','shield',[
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
            'authorization'=>['roles'=>'*','permissions'=>'*','access-control'=>'*'],
            'settings'=>['navigation-management'=>'*','business'=>'*','team'=>'*','locations'=>'*','taxes'=>'*','integrations'=>'*','notifications'=>'*','security'=>'*','audit'=>'*'],
            'analytics'=>['overview'=>'*','reports'=>'*'],
            'support'=>['tickets'=>'*','feedback'=>'*'],
        ];
        $profiles=[
            'retail'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*','discounts'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'crm'=>['contacts'=>'*','loyalty'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*','online-marketing'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'wholesale'=>[
                'sales'=>['orders'=>'*','quotations'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*'],
                'wholesale'=>['price-tiers'=>'*','distribution'=>'*','trade-credit'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['suppliers'=>'*','requisitions'=>'*','purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'logistics'=>['shipments'=>'*','delivery'=>'*','fleet'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*']
            ],
            'online'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*','discounts'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*','online-marketing'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'finance'=>['receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'logistics'=>['shipments'=>'*','delivery'=>'*','cod'=>'*'],
                'crm'=>['contacts'=>'*','loyalty'=>'*']
            ],
            'food'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','customers'=>'*','discounts'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'restaurant'=>['dining'=>'*','food-menu'=>'*','kitchen-display'=>'*','table-bookings'=>'*','buffet'=>'*'],
                'kitchen'=>['recipes'=>'*','preparation'=>'*','food-costing'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['suppliers'=>'*','requisitions'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'finance'=>['payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'cloud-food'=>[
                'sales'=>['orders'=>'*','invoices'=>'*','customers'=>'*','discounts'=>'*'],
                'pos'=>['counter'=>'*','shifts'=>'*'],
                'restaurant'=>['food-menu'=>'*','kitchen-display'=>'*'],
                'kitchen'=>['recipes'=>'*','preparation'=>'*','food-costing'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'ecommerce'=>['storefront'=>'*','catalog'=>'*','online-orders'=>'*','checkout'=>'*'],
                'logistics'=>['delivery'=>'*','cod'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'hospitality'=>[
                'sales'=>['invoices'=>'*','customers'=>'*'],
                'hotel'=>['rooms'=>'*','rates'=>'*','room-reservations'=>'*','front-desk'=>'*','folios'=>'*','housekeeping'=>'*'],
                'restaurant'=>['dining'=>'*','food-menu'=>'*','kitchen-display'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','leave'=>'*','payroll'=>'*'],
                'assets'=>['fixed-assets'=>'*','maintenance'=>'*']
            ],
            'beauty'=>[
                'booking'=>['services'=>'*','appointments'=>'*','availability'=>'*','service-packages'=>'*'],
                'beauty'=>['treatments'=>'*','professionals'=>'*'],
                'sales'=>['orders'=>'*','invoices'=>'*','customers'=>'*'],
                'pos'=>['counter'=>'*','shifts'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*'],
                'membership'=>['members'=>'*','membership-plans'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'fitness'=>[
                'booking'=>['services'=>'*','appointments'=>'*','availability'=>'*'],
                'fitness'=>['classes'=>'*','training'=>'*','gym-access'=>'*'],
                'membership'=>['members'=>'*','membership-plans'=>'*'],
                'sales'=>['invoices'=>'*','customers'=>'*'],
                'pos'=>['counter'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'clinic'=>[
                'healthcare'=>['patients'=>'*','consultations'=>'*','prescriptions'=>'*'],
                'booking'=>['services'=>'*','appointments'=>'*','availability'=>'*'],
                'sales'=>['invoices'=>'*','customers'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'diagnostic'=>[
                'healthcare'=>['patients'=>'*','consultations'=>'*'],
                'diagnostics'=>['lab-tests'=>'*','lab-processing'=>'*'],
                'booking'=>['services'=>'*','appointments'=>'*'],
                'sales'=>['invoices'=>'*','customers'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'pharmacy'=>[
                'pharmacy'=>['medicines'=>'*','medicine-batches'=>'*','dispensing'=>'*'],
                'sales'=>['orders'=>'*','invoices'=>'*','returns'=>'*','customers'=>'*'],
                'pos'=>['counter'=>'*','registers'=>'*','shifts'=>'*'],
                'inventory'=>['stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*','purchase-returns'=>'*'],
                'finance'=>['payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'education'=>[
                'education'=>['students'=>'*','courses'=>'*','classes'=>'*','exams'=>'*','student-fees'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','leave'=>'*','payroll'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*','followups'=>'*']
            ],
            'factory'=>[
                'manufacturing'=>['bill-of-materials'=>'*','work-orders'=>'*','production'=>'*','quality'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*','masters'=>'*'],
                'purchasing'=>['suppliers'=>'*','requisitions'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'sales'=>['orders'=>'*','quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'assets'=>['fixed-assets'=>'*','maintenance'=>'*','depreciation'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','leave'=>'*','payroll'=>'*']
            ],
            'construction'=>[
                'construction'=>['sites'=>'*','boq'=>'*','contractors'=>'*','site-progress'=>'*'],
                'projects'=>['projects'=>'*','tasks'=>'*','timesheets'=>'*','project-budget'=>'*'],
                'purchasing'=>['suppliers'=>'*','requisitions'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*'],
                'assets'=>['fixed-assets'=>'*','maintenance'=>'*']
            ],
            'property'=>[
                'property'=>['properties'=>'*','units'=>'*','tenants'=>'*','leases'=>'*','rent'=>'*'],
                'sales'=>['quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*','followups'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'assets'=>['fixed-assets'=>'*','maintenance'=>'*']
            ],
            'logistics'=>[
                'logistics'=>['shipments'=>'*','delivery'=>'*','fleet'=>'*','cod'=>'*'],
                'sales'=>['orders'=>'*','quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'finance'=>['receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*'],
                'assets'=>['fixed-assets'=>'*','maintenance'=>'*']
            ],
            'garage'=>[
                'automotive'=>['vehicles'=>'*','service-jobs'=>'*','spare-parts'=>'*'],
                'booking'=>['services'=>'*','appointments'=>'*','availability'=>'*'],
                'sales'=>['orders'=>'*','quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'pos'=>['counter'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'vehicle-sales'=>[
                'automotive'=>['vehicles'=>'*','spare-parts'=>'*'],
                'sales'=>['orders'=>'*','quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*','followups'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*'],
                'finance'=>['receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'rental'=>[
                'rental'=>['rental-items'=>'*','rental-contracts'=>'*','rental-returns'=>'*'],
                'booking'=>['availability'=>'*','appointments'=>'*'],
                'sales'=>['invoices'=>'*','customers'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'assets'=>['fixed-assets'=>'*','maintenance'=>'*']
            ],
            'events'=>[
                'events'=>['event-listing'=>'*','tickets'=>'*','attendees'=>'*','venues'=>'*'],
                'booking'=>['services'=>'*','availability'=>'*'],
                'sales'=>['quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'projects'=>['projects'=>'*','tasks'=>'*','project-budget'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'travel'=>[
                'travel'=>['tour-packages'=>'*','itineraries'=>'*','tour-bookings'=>'*'],
                'booking'=>['services'=>'*','availability'=>'*'],
                'sales'=>['quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*','followups'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'agriculture'=>[
                'agriculture'=>['farms'=>'*','crops'=>'*','livestock'=>'*','harvest'=>'*'],
                'inventory'=>['products'=>'*','stock'=>'*','movements'=>'*'],
                'purchasing'=>['suppliers'=>'*','purchase-orders'=>'*','receiving'=>'*'],
                'sales'=>['orders'=>'*','invoices'=>'*','customers'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'laundry'=>[
                'laundry'=>['laundry-orders'=>'*','processing'=>'*','laundry-delivery'=>'*'],
                'booking'=>['services'=>'*','availability'=>'*'],
                'sales'=>['invoices'=>'*','customers'=>'*'],
                'pos'=>['counter'=>'*'],
                'logistics'=>['delivery'=>'*'],
                'finance'=>['banking'=>'*','expenses'=>'*','financial-reports'=>'*']
            ],
            'services'=>[
                'booking'=>['services'=>'*','appointments'=>'*','availability'=>'*','service-packages'=>'*'],
                'projects'=>['projects'=>'*','tasks'=>'*','timesheets'=>'*','project-budget'=>'*'],
                'sales'=>['quotations'=>'*','invoices'=>'*','customers'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*','followups'=>'*'],
                'finance'=>['receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ],
            'subscriptions'=>[
                'subscriptions'=>['plans'=>'*','subscribers'=>'*','recurring-billing'=>'*'],
                'crm'=>['contacts'=>'*','leads'=>'*','followups'=>'*'],
                'sales'=>['invoices'=>'*','customers'=>'*','discounts'=>'*'],
                'finance'=>['receivables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'support'=>['tickets'=>'*','feedback'=>'*']
            ],
            'nonprofit'=>[
                'nonprofit'=>['donors'=>'*','fundraising'=>'*','programs'=>'*'],
                'projects'=>['projects'=>'*','tasks'=>'*','project-budget'=>'*'],
                'finance'=>['chart-of-accounts'=>'*','ledger'=>'*','receivables'=>'*','payables'=>'*','banking'=>'*','expenses'=>'*','financial-reports'=>'*'],
                'hr'=>['employees'=>'*','attendance'=>'*','payroll'=>'*']
            ]
        ];

        // Category slug => [English, বাংলা, profile]. Add new categories here.
        $categories=[
            'retail'=>['Retail Shop','খুচরা দোকান','retail'],
            'grocery'=>['Grocery','মুদি দোকান','retail'],
            'supermarket'=>['Supermarket','সুপারশপ','retail'],
            'fashion'=>['Fashion & Clothing','ফ্যাশন ও পোশাক','retail'],
            'electronics'=>['Electronics','ইলেকট্রনিকস','retail'],
            'hardware'=>['Hardware Store','হার্ডওয়্যার দোকান','retail'],
            'furniture'=>['Furniture Store','আসবাবপত্রের দোকান','retail'],
            'bookstore'=>['Bookstore','বইয়ের দোকান','retail'],
            'jewelry'=>['Jewelry Store','গয়নার দোকান','retail'],
            'pet-shop'=>['Pet Shop','পোষাপ্রাণীর দোকান','retail'],
            'wholesale'=>['Wholesale','পাইকারি ব্যবসা','wholesale'],
            'distribution'=>['Distribution Business','ডিস্ট্রিবিউশন ব্যবসা','wholesale'],
            'ecommerce'=>['E-Commerce','ই-কমার্স','online'],
            'online-store'=>['Online Store','অনলাইন দোকান','online'],
            'restaurant'=>['Restaurant','রেস্টুরেন্ট','food'],
            'cafe'=>['Cafe','ক্যাফে','food'],
            'bakery'=>['Bakery','বেকারি','food'],
            'fast-food'=>['Fast Food','ফাস্ট ফুড','food'],
            'catering'=>['Catering','ক্যাটারিং','food'],
            'cloud-kitchen'=>['Cloud Kitchen','ক্লাউড কিচেন','cloud-food'],
            'hotel'=>['Hotel','হোটেল','hospitality'],
            'resort'=>['Resort','রিসোর্ট','hospitality'],
            'guesthouse'=>['Guesthouse','গেস্টহাউস','hospitality'],
            'salon'=>['Beauty Salon','বিউটি সেলুন','beauty'],
            'barbershop'=>['Barbershop','বারবার শপ','beauty'],
            'spa'=>['Spa & Wellness','স্পা ও ওয়েলনেস','beauty'],
            'gym'=>['Gym','জিম','fitness'],
            'fitness-center'=>['Fitness Center','ফিটনেস সেন্টার','fitness'],
            'yoga-studio'=>['Yoga Studio','যোগব্যায়াম স্টুডিও','fitness'],
            'clinic'=>['Clinic','ক্লিনিক','clinic'],
            'dental-clinic'=>['Dental Clinic','ডেন্টাল ক্লিনিক','clinic'],
            'physiotherapy'=>['Physiotherapy','ফিজিওথেরাপি','clinic'],
            'diagnostic-center'=>['Diagnostic Center','ডায়াগনস্টিক সেন্টার','diagnostic'],
            'pharmacy'=>['Pharmacy','ফার্মেসি','pharmacy'],
            'school'=>['School','স্কুল','education'],
            'college'=>['College','কলেজ','education'],
            'coaching-center'=>['Coaching Center','কোচিং সেন্টার','education'],
            'training-center'=>['Training Center','প্রশিক্ষণ কেন্দ্র','education'],
            'manufacturing'=>['Manufacturing','উৎপাদন শিল্প','factory'],
            'garments-factory'=>['Garments Factory','পোশাক কারখানা','factory'],
            'food-factory'=>['Food Processing Factory','খাদ্য প্রক্রিয়াজাত কারখানা','factory'],
            'construction'=>['Construction Company','নির্মাণ প্রতিষ্ঠান','construction'],
            'contractor'=>['Contractor','ঠিকাদারি প্রতিষ্ঠান','construction'],
            'real-estate'=>['Real Estate','রিয়েল এস্টেট','property'],
            'property-management'=>['Property Management','সম্পত্তি ব্যবস্থাপনা','property'],
            'logistics'=>['Logistics','লজিস্টিকস','logistics'],
            'courier'=>['Courier Service','কুরিয়ার সেবা','logistics'],
            'transport'=>['Transport Service','পরিবহন সেবা','logistics'],
            'car-workshop'=>['Car Workshop','গাড়ির ওয়ার্কশপ','garage'],
            'auto-dealership'=>['Auto Dealership','গাড়ি বিক্রয় কেন্দ্র','vehicle-sales'],
            'vehicle-rental'=>['Vehicle Rental','যানবাহন ভাড়া','rental'],
            'equipment-rental'=>['Equipment Rental','যন্ত্রপাতি ভাড়া','rental'],
            'event-management'=>['Event Management','ইভেন্ট ম্যানেজমেন্ট','events'],
            'event-venue'=>['Event Venue','ইভেন্ট ভেন্যু','events'],
            'travel-agency'=>['Travel Agency','ট্রাভেল এজেন্সি','travel'],
            'tour-operator'=>['Tour Operator','ট্যুর অপারেটর','travel'],
            'agriculture'=>['Agriculture','কৃষি ব্যবসা','agriculture'],
            'dairy-farm'=>['Dairy Farm','দুগ্ধ খামার','agriculture'],
            'livestock-farm'=>['Livestock Farm','পশুর খামার','agriculture'],
            'laundry'=>['Laundry','লন্ড্রি','laundry'],
            'dry-cleaning'=>['Dry Cleaning','ড্রাই ক্লিনিং','laundry'],
            'service'=>['Service Business','সেবা ব্যবসা','services'],
            'repair-service'=>['Repair Service','মেরামত সেবা','services'],
            'it-service'=>['IT Services','আইটি সেবা','services'],
            'consulting'=>['Consulting','পরামর্শ সেবা','services'],
            'marketing-agency'=>['Marketing Agency','মার্কেটিং এজেন্সি','services'],
            'law-firm'=>['Law Firm','আইন সেবা প্রতিষ্ঠান','services'],
            'subscription-business'=>['Subscription Business','সাবস্ক্রিপশন ব্যবসা','subscriptions'],
            'saas'=>['SaaS Business','সাস ব্যবসা','subscriptions'],
            'nonprofit'=>['Nonprofit Organization','অলাভজনক সংস্থা','nonprofit'],
            'ngo'=>['NGO','এনজিও','nonprofit']
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
                foreach(array_unique($moduleIds) as $moduleId)BusinessCategoryModule::firstOrCreate(['business_category_id'=>$id,'module_id'=>$moduleId]);
                foreach(array_unique($menuIds) as $menuId)BusinessCategoryMenu::firstOrCreate(['business_category_id'=>$id,'menu_id'=>$menuId]);
                foreach(array_unique($childIds) as $childId)BusinessCategoryChildMenu::firstOrCreate(['business_category_id'=>$id,'child_menu_id'=>$childId]);
            }
        });
    }
}
