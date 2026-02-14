<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);

        $this->seedDemoData();
    }

    private function seedDemoData(): void
    {
        // إنشاء الملاك
        $owner1 = \App\Models\Owner::create([
            'name' => 'محمد عبدالرحمن الشمري',
            'phone' => '0501234567',
            'email' => 'mohammed@example.com',
            'identity_number' => '1098765432',
            'address' => 'الرياض، حي النخيل',
        ]);

        $owner2 = \App\Models\Owner::create([
            'name' => 'فهد سعود العتيبي',
            'phone' => '0509876543',
            'email' => 'fahad@example.com',
            'identity_number' => '1087654321',
            'address' => 'الرياض، حي الصحافة',
        ]);

        // إنشاء العقارات
        $property1 = \App\Models\Property::create([
            'code' => 'P-001',
            'name' => 'عمارة النخيل السكنية',
            'type' => 'سكني',
            'address' => 'الدرعية، شارع الملك فهد',
            'area' => 500,
            'description' => 'عمارة سكنية حديثة تحتوي على 10 وحدات',
            'owner_id' => $owner1->id,
        ]);

        $property2 = \App\Models\Property::create([
            'code' => 'P-002',
            'name' => 'مجمع الفيصل التجاري',
            'type' => 'تجاري',
            'address' => 'حي الصحافة، طريق الملك عبدالعزيز',
            'area' => 800,
            'description' => 'مجمع تجاري يحتوي على محلات ومكاتب',
            'owner_id' => $owner2->id,
        ]);

        // إنشاء الوحدات
        $unit1 = \App\Models\Unit::create([
            'code' => 'U-001',
            'property_id' => $property1->id,
            'type' => 'شقة',
            'floor' => 1,
            'number' => '101',
            'area' => 120,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'status' => 'مؤجرة',
            'rent_price' => 25000,
        ]);

        $unit2 = \App\Models\Unit::create([
            'code' => 'U-002',
            'property_id' => $property1->id,
            'type' => 'شقة',
            'floor' => 2,
            'number' => '201',
            'area' => 120,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'status' => 'متاحة',
            'rent_price' => 25000,
        ]);

        $unit3 = \App\Models\Unit::create([
            'code' => 'U-003',
            'property_id' => $property2->id,
            'type' => 'محل',
            'floor' => 0,
            'number' => 'M01',
            'area' => 60,
            'status' => 'مؤجرة',
            'rent_price' => 30000,
        ]);

        // إنشاء العملاء
        $customer1 = \App\Models\Customer::create([
            'name' => 'عبدالله محمد القحطاني',
            'phone' => '0551234567',
            'email' => 'abdullah@example.com',
            'id_number' => '1122334455',
            'nationality' => 'سعودي',
            'address' => 'الرياض',
        ]);

        $customer2 = \App\Models\Customer::create([
            'name' => 'خالد أحمد المطيري',
            'phone' => '0559876543',
            'email' => 'khaled@example.com',
            'id_number' => '5544332211',
            'nationality' => 'سعودي',
            'address' => 'الرياض',
        ]);

        // إنشاء العقود
        $contract1 = \App\Models\Contract::create([
            'code' => 'C-001',
            'unit_id' => $unit1->id,
            'customer_id' => $customer1->id,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(9),
            'rent_amount' => 25000,
            'payment_frequency' => 'شهري',
            'deposit' => 5000,
            'status' => 'نشط',
        ]);

        $contract2 = \App\Models\Contract::create([
            'code' => 'C-002',
            'unit_id' => $unit3->id,
            'customer_id' => $customer2->id,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addMonths(6),
            'rent_amount' => 30000,
            'payment_frequency' => 'شهري',
            'deposit' => 10000,
            'status' => 'نشط',
        ]);

        // إنشاء الإيرادات
        \App\Models\Revenue::create([
            'code' => 'R-001',
            'type' => 'إيجار',
            'amount' => 25000,
            'date' => now()->subMonths(3),
            'description' => 'دفعة إيجار شهر ' . now()->subMonths(3)->format('m/Y'),
            'property_id' => $property1->id,
            'contract_id' => $contract1->id,
            'status' => 'مدفوعة',
        ]);

        \App\Models\Revenue::create([
            'code' => 'R-002',
            'type' => 'إيجار',
            'amount' => 25000,
            'date' => now()->subMonths(2),
            'description' => 'دفعة إيجار شهر ' . now()->subMonths(2)->format('m/Y'),
            'property_id' => $property1->id,
            'contract_id' => $contract1->id,
            'status' => 'مدفوعة',
        ]);

        // إنشاء المصروفات
        \App\Models\Expense::create([
            'code' => 'E-001',
            'type' => 'صيانة',
            'amount' => 2000,
            'date' => now()->subMonth(),
            'description' => 'صيانة دورية للمصاعد',
            'property_id' => $property1->id,
            'is_recurring' => true,
            'frequency' => 'شهري',
        ]);

        \App\Models\Expense::create([
            'code' => 'E-002',
            'type' => 'كهرباء',
            'amount' => 3500,
            'date' => now()->subMonth(),
            'description' => 'فاتورة كهرباء',
            'property_id' => $property1->id,
            'is_recurring' => true,
            'frequency' => 'شهري',
        ]);

        // إنشاء الصيانة
        \App\Models\Maintenance::create([
            'code' => 'M-001',
            'property_id' => $property1->id,
            'unit_id' => $unit1->id,
            'type' => 'تكييف',
            'description' => 'إصلاح مكيف الوحدة 101',
            'status' => 'مكتملة',
            'priority' => 'عالية',
            'cost' => 1500,
            'date' => now()->subDays(10),
            'completed_at' => now()->subDays(5),
        ]);

        \App\Models\Maintenance::create([
            'code' => 'M-002',
            'property_id' => $property2->id,
            'unit_id' => null,
            'type' => 'سباكة',
            'description' => 'تسريب في خزان المياه',
            'status' => 'قيد التنفيذ',
            'priority' => 'عاجلة',
            'cost' => 2500,
            'date' => now()->subDays(2),
        ]);

        // إنشاء الإشعارات
        \App\Models\Notification::create([
            'type' => 'contract_expiry',
            'title' => 'عقد قارب على الانتهاء',
            'message' => 'عقد الوحدة 101 سينتهي خلال 30 يوم',
            'related_type' => 'App\\Models\\Contract',
            'related_id' => $contract1->id,
            'is_read' => false,
        ]);

        \App\Models\Notification::create([
            'type' => 'maintenance_urgent',
            'title' => 'طلب صيانة عاجل',
            'message' => 'تسريب في خزان المياه - مجمع الفيصل التجاري',
            'related_type' => 'App\\Models\\Maintenance',
            'related_id' => 2,
            'is_read' => false,
        ]);
    }
}
