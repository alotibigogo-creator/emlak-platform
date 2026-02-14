<?php

namespace App\Console\Commands;

use App\Models\Expense;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateRecurringExpenses extends Command
{
    protected $signature = 'expenses:generate-recurring';

    protected $description = 'توليد المصروفات المتكررة تلقائياً';

    public function handle()
    {
        $this->info('بدء توليد المصروفات المتكررة...');

        $recurringExpenses = Expense::where('is_recurring', true)->get();

        foreach ($recurringExpenses as $expense) {
            $this->generateNextExpense($expense);
        }

        $this->info('تم توليد المصروفات المتكررة بنجاح.');
    }

    protected function generateNextExpense(Expense $expense)
    {
        if (!$expense->frequency) {
            return;
        }

        // حساب التاريخ التالي بناءً على التكرار
        $lastDate = Carbon::parse($expense->date);
        $nextDate = match ($expense->frequency) {
            'شهري' => $lastDate->copy()->addMonth(),
            'ربع سنوي' => $lastDate->copy()->addMonths(3),
            'نصف سنوي' => $lastDate->copy()->addMonths(6),
            'سنوي' => $lastDate->copy()->addYear(),
            default => null,
        };

        if (!$nextDate || $nextDate->isFuture()) {
            return; // التاريخ التالي لم يحن بعد
        }

        // التحقق من عدم إنشاء مصروف مكرر لنفس الشهر
        $exists = Expense::where('property_id', $expense->property_id)
            ->where('type', $expense->type)
            ->where('is_recurring', true)
            ->whereYear('date', $nextDate->year)
            ->whereMonth('date', $nextDate->month)
            ->exists();

        if ($exists) {
            return; // المصروف موجود بالفعل
        }

        // إنشاء المصروف الجديد
        $newExpense = $expense->replicate();
        $newExpense->date = $nextDate;
        $newExpense->description = $expense->description . ' (متكرر - ' . $nextDate->format('m/Y') . ')';
        $newExpense->save();

        $this->info("✓ تم إنشاء مصروف متكرر: {$newExpense->code} - {$newExpense->type}");

        // تحديث تاريخ المصروف الأصلي
        $expense->update(['date' => $nextDate]);
    }
}
