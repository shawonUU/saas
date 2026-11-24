<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Accounts\{Account, Journal, JournalLine};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Accounting
{

    public const DEFAULTS = [
        // ASSETS: 1000 - 1999
        '1000' => ['Cash in Hand', 'asset'],
        '1010' => ['Petty Cash', 'asset'],
        '1100' => ['Bank Account', 'asset'],
        '1110' => ['Mobile Banking', 'asset'],
        '1120' => ['Payment Gateway Balance', 'asset'],
        '1150' => ['Gateway Clearing', 'asset'],

        '1200' => ['Accounts Receivable', 'asset'],
        '1210' => ['Customer Advances', 'asset'],
        '1220' => ['Employee Advances', 'asset'],
        '1230' => ['Supplier Advances', 'asset'],
        '1240' => ['Other Receivables', 'asset'],

        '1300' => ['Inventory', 'asset'],
        '1310' => ['Raw Materials Inventory', 'asset'],
        '1320' => ['Work in Progress Inventory', 'asset'],
        '1330' => ['Finished Goods Inventory', 'asset'],
        '1340' => ['Packaging Materials Inventory', 'asset'],
        '1350' => ['Spare Parts Inventory', 'asset'],
        '1360' => ['Stationery Inventory', 'asset'],
        '1370' => ['Medicine Inventory', 'asset'],

        '1400' => ['Prepaid Expenses', 'asset'],
        '1410' => ['Prepaid Rent', 'asset'],
        '1420' => ['Security Deposits', 'asset'],
        '1430' => ['Tax Receivable', 'asset'],
        '1440' => ['VAT Input Receivable', 'asset'],

        '1500' => ['Land', 'asset'],
        '1510' => ['Building', 'asset'],
        '1520' => ['Furniture and Fixtures', 'asset'],
        '1530' => ['Computer and IT Equipment', 'asset'],
        '1540' => ['Machinery and Equipment', 'asset'],
        '1550' => ['Vehicles', 'asset'],
        '1560' => ['Office Equipment', 'asset'],
        '1570' => ['Accumulated Depreciation', 'asset'],

        // LIABILITIES: 2000 - 2999
        '2000' => ['Accounts Payable', 'liability'],
        '2010' => ['Supplier Payables', 'liability'],
        '2020' => ['Employee Payables', 'liability'],
        '2030' => ['Salary Payable', 'liability'],
        '2040' => ['Utility Bills Payable', 'liability'],

        '2100' => ['VAT Payable', 'liability'],
        '2110' => ['Tax Payable', 'liability'],
        '2120' => ['Withholding Tax Payable', 'liability'],

        '2200' => ['Customer Advances Received', 'liability'],
        '2210' => ['Unearned Revenue', 'liability'],
        '2220' => ['Student Fees Received in Advance', 'liability'],
        '2230' => ['Customer Refund Payable', 'liability'],

        '2300' => ['Short Term Loan', 'liability'],
        '2310' => ['Long Term Loan', 'liability'],
        '2320' => ['Lease Liabilities', 'liability'],

        '2400' => ['Accrued Expenses', 'liability'],
        '2410' => ['Interest Payable', 'liability'],
        '2420' => ['Other Liabilities', 'liability'],

        // EQUITY: 3000 - 3999
        '3000' => ['Owner Capital', 'equity'],
        '3010' => ['Opening Equity', 'equity'],
        '3020' => ['Additional Capital', 'equity'],
        '3030' => ['Owner Drawings', 'equity'],
        '3040' => ['Retained Earnings', 'equity'],
        '3050' => ['Current Year Earnings', 'equity'],
        '3060' => ['General Fund', 'equity'],
        '3070' => ['Development Fund', 'equity'],
        '3080' => ['Scholarship Fund', 'equity'],

        // INCOME: 4000 - 4999
        '4000' => ['Sales Revenue', 'income'],
        '4010' => ['Product Sales Revenue', 'income'],
        '4020' => ['Service Revenue', 'income'],
        '4030' => ['Food and Beverage Revenue', 'income'],
        '4040' => ['Tuition Fee Income', 'income'],
        '4050' => ['Admission Fee Income', 'income'],
        '4060' => ['Examination Fee Income', 'income'],
        '4070' => ['Consultation Fee Income', 'income'],
        '4080' => ['Medicine Sales Revenue', 'income'],
        '4090' => ['Manufacturing Sales Revenue', 'income'],

        '4100' => ['Delivery Revenue', 'income'],
        '4110' => ['Transport Fee Income', 'income'],
        '4120' => ['Hostel Fee Income', 'income'],
        '4130' => ['Laboratory Fee Income', 'income'],
        '4140' => ['Registration Fee Income', 'income'],
        '4150' => ['Book and Stationery Sales', 'income'],
        '4160' => ['Membership Fee Income', 'income'],
        '4170' => ['Rental Income', 'income'],
        '4180' => ['Commission Income', 'income'],
        '4190' => ['Donation and Grant Income', 'income'],

        '4200' => ['Sales Returns', 'income'],
        '4210' => ['Sales Discounts', 'income'],
        '4220' => ['Purchase Discounts Received', 'income'],
        '4230' => ['Other Operating Income', 'income'],

        '4300' => ['Interest Income', 'income'],
        '4310' => ['Other Non-Operating Income', 'income'],

        // COST OF SALES: 5000 - 5999
        '5000' => ['Cost of Goods Sold', 'expense'],
        '5010' => ['Raw Materials Consumed', 'expense'],
        '5020' => ['Direct Labor Cost', 'expense'],
        '5030' => ['Manufacturing Overhead', 'expense'],
        '5040' => ['Food and Beverage Cost', 'expense'],
        '5050' => ['Medicine Purchase Cost', 'expense'],
        '5060' => ['Packaging Cost', 'expense'],
        '5070' => ['Freight and Carriage Inward', 'expense'],
        '5080' => ['Inventory Write-off', 'expense'],
        '5090' => ['Inventory Damage and Expiry', 'expense'],

        '5100' => ['Purchase Price Variance', 'expense'],
        '5110' => ['Stock Adjustment Loss', 'expense'],
        '5120' => ['Production Wastage', 'expense'],

        // OPERATING EXPENSES: 6000 - 6999
        '6000' => ['Operating Expenses', 'expense'],
        '6010' => ['Salary and Wages Expense', 'expense'],
        '6020' => ['Rent Expense', 'expense'],
        '6030' => ['Electricity Expense', 'expense'],
        '6040' => ['Water and Gas Expense', 'expense'],
        '6050' => ['Internet and Telephone Expense', 'expense'],
        '6060' => ['Office Supplies Expense', 'expense'],
        '6070' => ['Stationery Expense', 'expense'],
        '6080' => ['Transport and Fuel Expense', 'expense'],
        '6090' => ['Repair and Maintenance Expense', 'expense'],

        '6100' => ['Marketing and Advertising Expense', 'expense'],
        '6110' => ['Delivery and Courier Expense', 'expense'],
        '6120' => ['Commission Expense', 'expense'],
        '6130' => ['Payment Gateway Fees', 'expense'],
        '6140' => ['Bank Charges', 'expense'],
        '6150' => ['Software and IT Expense', 'expense'],
        '6160' => ['Training and Development Expense', 'expense'],
        '6170' => ['Travel and Entertainment Expense', 'expense'],
        '6180' => ['Insurance Expense', 'expense'],
        '6190' => ['Security Expense', 'expense'],

        '6200' => ['Scholarship and Financial Aid Expense', 'expense'],
        '6210' => ['Examination Expense', 'expense'],
        '6220' => ['Laboratory Expense', 'expense'],
        '6230' => ['Cleaning Expense', 'expense'],
        '6240' => ['Event and Sports Expense', 'expense'],
        '6250' => ['Depreciation Expense', 'expense'],

        '6300' => ['Bad Debt Expense', 'expense'],
        '6310' => ['Interest Expense', 'expense'],
        '6320' => ['Tax Expense', 'expense'],
        '6330' => ['Other Administrative Expense', 'expense'],
        '6340' => ['Other Operating Expense', 'expense'],
    ];


    public function ensure(Business $business): void
    {
        foreach (self::DEFAULTS as $code => [$name, $type]) {
            Account::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'code' => (string) $code,
                ],
                [
                    'name' => $name,
                    'type' => $type,
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    public function post(
        Business $business,
        string $source,
        int $sourceId,
        string $memo,
        array $entries
    ): void {
        DB::transaction(function () use ($business, $source, $sourceId, $memo, $entries) {
            $this->ensure($business);

            $debit = 0;
            $credit = 0;
            $filtered = [];

            foreach ($entries as $code => $amounts) {
                $d = round((float) ($amounts[0] ?? 0), 2);
                $c = round((float) ($amounts[1] ?? 0), 2);

                if ($d < 0 || $c < 0) {
                    throw ValidationException::withMessages([
                        'journal' => 'Negative debit/credit is not allowed.',
                    ]);
                }

                if ($d == 0 && $c == 0) {
                    continue;
                }

                if ($d > 0 && $c > 0) {
                    throw ValidationException::withMessages([
                        'journal' => 'One account cannot be both debit and credit on the same line.',
                    ]);
                }

                $debit += (int) round($d * 100);
                $credit += (int) round($c * 100);

                $filtered[] = [
                    (string) $code,
                    $d,
                    $c,
                ];
            }

            if ($debit !== $credit || $debit === 0) {
                throw ValidationException::withMessages([
                    'journal' => 'Unbalanced transaction.',
                ]);
            }

            $exists = Journal::where('business_id', $business->id)
                ->where('source_type', $source)
                ->where('source_id', $sourceId)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'journal' => 'Transaction already posted.',
                ]);
            }

            $journal = Journal::create([
                'business_id' => $business->id,
                'source_type' => $source,
                'source_id' => $sourceId,
                'memo' => $memo,
                'posted_at' => now(),
            ]);

            foreach ($filtered as [$code, $d, $c]) {
                $account = Account::where('business_id', $business->id)
                    ->where('code', $code)
                    ->where('is_active', true)
                    ->firstOrFail();

                JournalLine::create([
                    'business_id' => $business->id,
                    'journal_id' => $journal->id,
                    'account_id' => $account->id,
                    'debit' => $d,
                    'credit' => $c,
                ]);
            }
        });
    }

    public function lines(Business $business, $query)
    {
        return $query->where(
            'journal_lines.business_id',
            $business->id
        );
    }
}
