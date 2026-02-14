<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\PaymentSchedule;
use App\Models\Maintenance;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckNotifications extends Command
{
    protected $signature = 'notifications:check';

    protected $description = 'فحص وإنشاء الإشعارات للعقود المنتهية والمدفوعات المستحقة والصيانة العاجلة';

    public function handle()
    {
        $this->info('بدء فحص الإشعارات...');

        $this->checkContractExpiry();
        $this->checkPaymentDue();
        $this->checkUrgentMaintenance();

        $this->info('تم إنشاء الإشعارات بنجاح.');
    }

    protected function checkContractExpiry()
    {
        // العقود المنتهية خلال 30 يوم
        $expiringContracts = Contract::where('status', 'نشط')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->get();

        foreach ($expiringContracts as $contract) {
            // التحقق من عدم وجود إشعار سابق لنفس العقد
            $exists = Notification::where('type', 'contract_expiry')
                ->where('related_type', 'App\\Models\\Contract')
                ->where('related_id', $contract->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if (!$exists) {
                $daysLeft = Carbon::parse($contract->end_date)->diffInDays(now());

                Notification::create([
                    'type' => 'contract_expiry',
                    'title' => 'عقد قارب على الانتهاء',
                    'message' => sprintf(
                        'عقد %s للوحدة %s سينتهي خلال %d يوم (تاريخ الانتهاء: %s)',
                        $contract->code,
                        $contract->unit->code,
                        $daysLeft,
                        $contract->end_date->format('Y-m-d')
                    ),
                    'related_type' => 'App\\Models\\Contract',
                    'related_id' => $contract->id,
                    'is_read' => false,
                ]);

                $this->info("✓ إشعار انتهاء عقد: {$contract->code}");
            }
        }
    }

    protected function checkPaymentDue()
    {
        // المدفوعات المستحقة خلال 7 أيام
        $duePayments = PaymentSchedule::where('status', 'معلقة')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->get();

        foreach ($duePayments as $payment) {
            // التحقق من عدم وجود إشعار سابق
            $exists = Notification::where('type', 'payment_due')
                ->where('message', 'like', '%' . $payment->contract->code . '%')
                ->where('created_at', '>=', now()->subDays(3))
                ->exists();

            if (!$exists) {
                $daysLeft = Carbon::parse($payment->due_date)->diffInDays(now());

                Notification::create([
                    'type' => 'payment_due',
                    'title' => 'دفعة مستحقة',
                    'message' => sprintf(
                        'دفعة بمبلغ %s ر.س للعقد %s مستحقة خلال %d يوم (تاريخ الاستحقاق: %s)',
                        number_format($payment->amount, 2),
                        $payment->contract->code,
                        $daysLeft,
                        $payment->due_date->format('Y-m-d')
                    ),
                    'related_type' => 'App\\Models\\PaymentSchedule',
                    'related_id' => $payment->id,
                    'is_read' => false,
                ]);

                $this->info("✓ إشعار دفعة مستحقة: {$payment->contract->code}");
            }
        }
    }

    protected function checkUrgentMaintenance()
    {
        // طلبات الصيانة العاجلة والمعلقة
        $urgentMaintenance = Maintenance::where('priority', 'عاجلة')
            ->whereIn('status', ['معلقة', 'قيد التنفيذ'])
            ->get();

        foreach ($urgentMaintenance as $maintenance) {
            // التحقق من عدم وجود إشعار سابق
            $exists = Notification::where('type', 'maintenance_urgent')
                ->where('related_type', 'App\\Models\\Maintenance')
                ->where('related_id', $maintenance->id)
                ->where('created_at', '>=', now()->subDays(1))
                ->exists();

            if (!$exists) {
                Notification::create([
                    'type' => 'maintenance_urgent',
                    'title' => 'طلب صيانة عاجل',
                    'message' => sprintf(
                        '%s - %s (العقار: %s)',
                        $maintenance->code,
                        $maintenance->description,
                        $maintenance->property->name
                    ),
                    'related_type' => 'App\\Models\\Maintenance',
                    'related_id' => $maintenance->id,
                    'is_read' => false,
                ]);

                $this->info("✓ إشعار صيانة عاجلة: {$maintenance->code}");
            }
        }
    }
}
