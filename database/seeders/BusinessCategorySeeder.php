<?php
namespace Database\Seeders;
use App\Enums\Status;
use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;

class BusinessCategorySeeder extends Seeder
{
    public function run(): void
    {
        $business_category = [
            'general-retail' => ['General / Retail','সাধারণ রিটেইল'],
            'clothing' => ['Clothing & Fashion','পোশাক ও ফ্যাশন'],
            'grocery' => ['Grocery','মুদি দোকান'],
            'pharmacy' => ['Pharmacy','ফার্মেসি'],
            'restaurant' => ['Restaurant','রেস্তোরাঁ'],
        ];
        foreach ($business_category as $slug => [$name,$nameBn]) {
            BusinessCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'name_bn' => $nameBn, 'approval_status' => Status::APPROVED]);
        }
    }
}
