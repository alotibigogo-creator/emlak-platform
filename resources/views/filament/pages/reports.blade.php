<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Report Form --}}
        <x-filament::section>
            <x-slot name="heading">
                إعدادات التقرير
            </x-slot>

            <form wire:submit.prevent="submit">
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Report Preview --}}
        <x-filament::section>
            <x-slot name="heading">
                معاينة التقرير
            </x-slot>

            <x-slot name="headerEnd">
                <div class="flex gap-2">
                    <x-filament::button
                        wire:click="exportPdf"
                        color="danger"
                        icon="heroicon-o-document-arrow-down"
                    >
                        تصدير PDF
                    </x-filament::button>
                </div>
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                @php
                    $data = $this->getReportData();
                @endphp

                @if($reportType === 'financial')
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">التقرير المالي</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            من {{ $fromDate }} إلى {{ $toDate }}
                        </p>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الإيرادات</div>
                                <div class="text-2xl font-bold">{{ number_format($data['total_revenue'], 2) }} ر.س</div>
                            </div>
                            <div class="p-4 bg-red-100 dark:bg-red-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي المصروفات</div>
                                <div class="text-2xl font-bold">{{ number_format($data['total_expense'], 2) }} ر.س</div>
                            </div>
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">صافي الدخل</div>
                                <div class="text-2xl font-bold">{{ number_format($data['net_income'], 2) }} ر.س</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-bold mb-2">تفاصيل الإيرادات</h4>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-right">الرمز</th>
                                        <th class="px-4 py-2 text-right">النوع</th>
                                        <th class="px-4 py-2 text-right">المبلغ</th>
                                        <th class="px-4 py-2 text-right">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($data['revenues'] as $revenue)
                                        <tr>
                                            <td class="px-4 py-2">{{ $revenue->code }}</td>
                                            <td class="px-4 py-2">{{ $revenue->type }}</td>
                                            <td class="px-4 py-2">{{ number_format($revenue->amount, 2) }} ر.س</td>
                                            <td class="px-4 py-2">{{ $revenue->date->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-bold mb-2">تفاصيل المصروفات</h4>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-right">الرمز</th>
                                        <th class="px-4 py-2 text-right">النوع</th>
                                        <th class="px-4 py-2 text-right">المبلغ</th>
                                        <th class="px-4 py-2 text-right">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($data['expenses'] as $expense)
                                        <tr>
                                            <td class="px-4 py-2">{{ $expense->code }}</td>
                                            <td class="px-4 py-2">{{ $expense->type }}</td>
                                            <td class="px-4 py-2">{{ number_format($expense->amount, 2) }} ر.س</td>
                                            <td class="px-4 py-2">{{ $expense->date->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                @elseif($reportType === 'occupancy')
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">تقرير الإشغال</h3>

                        <div class="grid grid-cols-4 gap-4">
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الوحدات</div>
                                <div class="text-2xl font-bold">{{ $data['total_units'] }}</div>
                            </div>
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">وحدات مؤجرة</div>
                                <div class="text-2xl font-bold">{{ $data['occupied_units'] }}</div>
                            </div>
                            <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">وحدات متاحة</div>
                                <div class="text-2xl font-bold">{{ $data['available_units'] }}</div>
                            </div>
                            <div class="p-4 bg-purple-100 dark:bg-purple-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">نسبة الإشغال</div>
                                <div class="text-2xl font-bold">{{ $data['occupancy_rate'] }}%</div>
                            </div>
                        </div>
                    </div>

                @elseif($reportType === 'contracts')
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">تقرير العقود</h3>

                        <div class="grid grid-cols-4 gap-4">
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">عقود نشطة</div>
                                <div class="text-2xl font-bold">{{ $data['active_contracts'] }}</div>
                            </div>
                            <div class="p-4 bg-gray-100 dark:bg-gray-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">عقود منتهية</div>
                                <div class="text-2xl font-bold">{{ $data['expired_contracts'] }}</div>
                            </div>
                            <div class="p-4 bg-red-100 dark:bg-red-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">عقود ملغاة</div>
                                <div class="text-2xl font-bold">{{ $data['canceled_contracts'] }}</div>
                            </div>
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">قيمة الإيجارات</div>
                                <div class="text-2xl font-bold">{{ number_format($data['total_rent_value'], 2) }}</div>
                            </div>
                        </div>
                    </div>

                @elseif($reportType === 'maintenance')
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">تقرير الصيانة</h3>

                        <div class="grid grid-cols-4 gap-4">
                            <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">معلقة</div>
                                <div class="text-2xl font-bold">{{ $data['pending'] }}</div>
                            </div>
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">قيد التنفيذ</div>
                                <div class="text-2xl font-bold">{{ $data['in_progress'] }}</div>
                            </div>
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">مكتملة</div>
                                <div class="text-2xl font-bold">{{ $data['completed'] }}</div>
                            </div>
                            <div class="p-4 bg-red-100 dark:bg-red-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي التكلفة</div>
                                <div class="text-2xl font-bold">{{ number_format($data['total_cost'], 2) }}</div>
                            </div>
                        </div>
                    </div>

                @elseif($reportType === 'customers')
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">تقرير العملاء</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي العملاء</div>
                                <div class="text-2xl font-bold">{{ $data['total_customers'] }}</div>
                            </div>
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">عملاء نشطين</div>
                                <div class="text-2xl font-bold">{{ $data['active_customers'] }}</div>
                            </div>
                        </div>
                    </div>

                @elseif($reportType === 'properties')
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">تقرير العقارات</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي العقارات</div>
                                <div class="text-2xl font-bold">{{ $data['total_properties'] }}</div>
                            </div>
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الوحدات</div>
                                <div class="text-2xl font-bold">{{ $data['total_units'] }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
