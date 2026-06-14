<?php

namespace App\Filament\Pages;

use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class CommerceReports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Commerce reports';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Commerce reports';

    protected string $view = 'filament.pages.commerce-reports';

    /**
     * @return array<string, string>
     */
    public function metrics(): array
    {
        $postedIncome = FinancialTransaction::query()
            ->where('direction', 'income')
            ->where('status', 'posted')
            ->sum('amount');

        $postedExpenses = FinancialTransaction::query()
            ->where('direction', 'expense')
            ->where('status', 'posted')
            ->sum('amount');

        return [
            'Open orders' => number_format(Order::query()->whereIn('status', ['pending', 'confirmed', 'processing', 'ready'])->count()),
            'Inventory value' => number_format(Product::query()->sum(DB::raw('stock_quantity * price_value'))),
            'Open purchase orders' => number_format(PurchaseOrder::query()->whereIn('status', ['draft', 'ordered', 'partially_received'])->count()),
            'Pending returns' => number_format(ReturnRequest::query()->whereIn('status', ['requested', 'approved', 'received'])->count()),
            'Active shipments' => number_format(Shipment::query()->whereNotIn('status', ['delivered', 'failed', 'returned'])->count()),
            'Posted net finance' => number_format(max(0, $postedIncome - $postedExpenses)),
        ];
    }
}
