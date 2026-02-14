<?php

namespace App\Filament\Pages;

use App\Models\Property;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\Revenue;
use App\Models\Expense;
use App\Models\Maintenance;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationLabel = 'التقارير';

    protected static ?string $title = 'التقارير';

    protected static ?string $navigationGroup = 'العقارات';

    protected static ?int $navigationSort = 8;

    public ?string $reportType = 'financial';
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public ?int $propertyId = null;

    public function mount(): void
    {
        $this->fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->form->fill([
            'report_type' => 'financial',
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_type')
                    ->label('نوع التقرير')
                    ->required()
                    ->options([
                        'financial' => 'التقرير المالي (إيرادات ومصروفات)',
                        'occupancy' => 'تقرير الإشغال',
                        'contracts' => 'تقرير العقود',
                        'maintenance' => 'تقرير الصيانة',
                        'customers' => 'تقرير العملاء',
                        'properties' => 'تقرير العقارات',
                    ])
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->reportType = $state),
                DatePicker::make('from_date')
                    ->label('من تاريخ')
                    ->required()
                    ->native(false)
                    ->maxDate(now())
                    ->afterStateUpdated(fn ($state) => $this->fromDate = $state),
                DatePicker::make('to_date')
                    ->label('إلى تاريخ')
                    ->required()
                    ->native(false)
                    ->maxDate(now())
                    ->after('from_date')
                    ->afterStateUpdated(fn ($state) => $this->toDate = $state),
                Select::make('property_id')
                    ->label('العقار (اختياري)')
                    ->options(Property::pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn (callable $get) => in_array($get('report_type'), ['financial', 'occupancy', 'maintenance']))
                    ->afterStateUpdated(fn ($state) => $this->propertyId = $state),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function getReportData(): array
    {
        $from = Carbon::parse($this->fromDate);
        $to = Carbon::parse($this->toDate);

        return match ($this->reportType) {
            'financial' => $this->getFinancialReport($from, $to),
            'occupancy' => $this->getOccupancyReport($from, $to),
            'contracts' => $this->getContractsReport($from, $to),
            'maintenance' => $this->getMaintenanceReport($from, $to),
            'customers' => $this->getCustomersReport($from, $to),
            'properties' => $this->getPropertiesReport($from, $to),
            default => [],
        };
    }

    protected function getFinancialReport($from, $to): array
    {
        $query = Revenue::whereBetween('date', [$from, $to]);
        if ($this->propertyId) {
            $query->where('property_id', $this->propertyId);
        }
        $revenues = $query->where('status', 'مدفوعة')->get();

        $expenseQuery = Expense::whereBetween('date', [$from, $to]);
        if ($this->propertyId) {
            $expenseQuery->where('property_id', $this->propertyId);
        }
        $expenses = $expenseQuery->get();

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $revenues->sum('amount'),
            'total_expense' => $expenses->sum('amount'),
            'net_income' => $revenues->sum('amount') - $expenses->sum('amount'),
        ];
    }

    protected function getOccupancyReport($from, $to): array
    {
        $query = Unit::query();
        if ($this->propertyId) {
            $query->where('property_id', $this->propertyId);
        }
        $units = $query->get();

        return [
            'total_units' => $units->count(),
            'occupied_units' => $units->where('status', 'مؤجرة')->count(),
            'available_units' => $units->where('status', 'متاحة')->count(),
            'maintenance_units' => $units->where('status', 'صيانة')->count(),
            'occupancy_rate' => $units->count() > 0 ? round(($units->where('status', 'مؤجرة')->count() / $units->count()) * 100, 2) : 0,
        ];
    }

    protected function getContractsReport($from, $to): array
    {
        $contracts = Contract::whereBetween('start_date', [$from, $to])->get();

        return [
            'contracts' => $contracts,
            'active_contracts' => $contracts->where('status', 'نشط')->count(),
            'expired_contracts' => $contracts->where('status', 'منتهي')->count(),
            'canceled_contracts' => $contracts->where('status', 'ملغي')->count(),
            'total_rent_value' => $contracts->where('status', 'نشط')->sum('rent_amount'),
        ];
    }

    protected function getMaintenanceReport($from, $to): array
    {
        $query = Maintenance::whereBetween('date', [$from, $to]);
        if ($this->propertyId) {
            $query->where('property_id', $this->propertyId);
        }
        $maintenances = $query->get();

        return [
            'maintenances' => $maintenances,
            'pending' => $maintenances->where('status', 'معلقة')->count(),
            'in_progress' => $maintenances->where('status', 'قيد التنفيذ')->count(),
            'completed' => $maintenances->where('status', 'مكتملة')->count(),
            'total_cost' => $maintenances->sum('cost'),
        ];
    }

    protected function getCustomersReport($from, $to): array
    {
        $customers = Customer::with(['contracts' => function ($query) use ($from, $to) {
            $query->whereBetween('start_date', [$from, $to]);
        }])->get();

        return [
            'customers' => $customers,
            'total_customers' => $customers->count(),
            'active_customers' => $customers->filter(fn ($c) => $c->contracts->where('status', 'نشط')->count() > 0)->count(),
        ];
    }

    protected function getPropertiesReport($from, $to): array
    {
        $properties = Property::with(['units', 'contracts', 'revenues', 'expenses'])->get();

        return [
            'properties' => $properties,
            'total_properties' => $properties->count(),
            'total_units' => $properties->sum(fn ($p) => $p->units->count()),
        ];
    }

    public function exportPdf()
    {
        $data = $this->getReportData();
        $data['from_date'] = $this->fromDate;
        $data['to_date'] = $this->toDate;
        $data['report_type'] = $this->reportType;
        $data['report_title'] = $this->getReportTitle();

        $pdf = Pdf::loadView('reports.pdf.' . $this->reportType, $data)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'report-' . $this->reportType . '-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    protected function getReportTitle(): string
    {
        return match ($this->reportType) {
            'financial' => 'التقرير المالي',
            'occupancy' => 'تقرير الإشغال',
            'contracts' => 'تقرير العقود',
            'maintenance' => 'تقرير الصيانة',
            'customers' => 'تقرير العملاء',
            'properties' => 'تقرير العقارات',
            default => 'تقرير',
        };
    }
}
