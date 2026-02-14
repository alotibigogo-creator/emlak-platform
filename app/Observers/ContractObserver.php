<?php

namespace App\Observers;

use App\Models\Contract;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $this->generatePaymentSchedules($contract);
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        // إذا تم تغيير التواريخ أو دورية الدفع، نعيد إنشاء جدول السداد
        if ($contract->isDirty(['start_date', 'end_date', 'payment_frequency', 'rent_amount'])) {
            // حذف الجداول القديمة التي لم تُدفع
            $contract->paymentSchedules()->where('status', 'معلقة')->delete();
            // إنشاء جداول جديدة
            $this->generatePaymentSchedules($contract);
        }
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        // حذف جداول السداد المرتبطة
        $contract->paymentSchedules()->delete();
    }

    /**
     * توليد جدول السداد للعقد
     */
    protected function generatePaymentSchedules(Contract $contract): void
    {
        $startDate = \Carbon\Carbon::parse($contract->start_date);
        $endDate = \Carbon\Carbon::parse($contract->end_date);
        $amount = $contract->rent_amount;

        // تحديد عدد الأشهر بين كل دفعة
        $monthsInterval = match ($contract->payment_frequency) {
            'شهري' => 1,
            'ربع سنوي' => 3,
            'نصف سنوي' => 6,
            'سنوي' => 12,
            default => 1,
        };

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // التحقق من عدم وجود جدول سداد لنفس التاريخ
            $exists = $contract->paymentSchedules()
                ->where('due_date', $currentDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                \App\Models\PaymentSchedule::create([
                    'contract_id' => $contract->id,
                    'due_date' => $currentDate->format('Y-m-d'),
                    'amount' => $amount,
                    'status' => 'معلقة',
                ]);
            }

            $currentDate->addMonths($monthsInterval);
        }
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        //
    }
}
