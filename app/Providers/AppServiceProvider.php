<?php

namespace App\Providers;

use App\Models\AdminActivityLog;
use App\Models\ContentPage;
use App\Models\DiscountCode;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Receipt;
use App\Models\ReturnRequest;
use App\Models\SeoRedirect;
use App\Models\StockMovement;
use App\Support\Storefront\StorefrontCart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('storefront.*', function ($view): void {
            $view->with('cartCount', app(StorefrontCart::class)->count());
        });

        $this->registerAdminActivityLogging();
    }

    private function registerAdminActivityLogging(): void
    {
        foreach ([
            ContentPage::class,
            DiscountCode::class,
            FinancialTransaction::class,
            Order::class,
            PaymentTransaction::class,
            Product::class,
            ProductVariant::class,
            PurchaseOrder::class,
            Receipt::class,
            ReturnRequest::class,
            SeoRedirect::class,
            StockMovement::class,
        ] as $modelClass) {
            $modelClass::created(fn (Model $model) => $this->recordAdminActivity('created', $model));
            $modelClass::updated(fn (Model $model) => $this->recordAdminActivity('updated', $model));
            $modelClass::deleted(fn (Model $model) => $this->recordAdminActivity('deleted', $model));
        }
    }

    private function recordAdminActivity(string $event, Model $model): void
    {
        if (! auth()->check() || ! request()->is('admin/*')) {
            return;
        }

        AdminActivityLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'properties' => [
                'changes' => $event === 'updated' ? $model->getChanges() : $model->getAttributes(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
