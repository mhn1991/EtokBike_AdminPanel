<?php

namespace App\Providers\Filament;

use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('مدیریت EtokBike')
            ->font('Tahoma', provider: LocalFontProvider::class)
            ->sidebarWidth('18rem')
            ->colors([
                'primary' => Color::hex('#D71920'),
            ])
            ->navigationGroups([
                NavigationGroup::make('پیام‌ها'),
                NavigationGroup::make('SEO'),
                NavigationGroup::make('انبار'),
                NavigationGroup::make('خرید'),
                NavigationGroup::make('مالی'),
                NavigationGroup::make('سفارش‌ها'),
                NavigationGroup::make('ارسال'),
                NavigationGroup::make('بازاریابی'),
                NavigationGroup::make('اعلان‌ها'),
                NavigationGroup::make('گزارش‌ها'),
                NavigationGroup::make('ممیزی'),
                NavigationGroup::make('کاتالوگ'),
                NavigationGroup::make('خدمات'),
                NavigationGroup::make('برنامه‌ها'),
                NavigationGroup::make('محتوای اپ موبایل'),
                NavigationGroup::make('مشتریان'),
                NavigationGroup::make('تنظیمات'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
