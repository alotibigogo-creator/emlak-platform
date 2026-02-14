<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->path('')
            ->brandName('منصة املاك')
            ->brandLogo(asset('images/logo.png'))
            ->darkModeBrandLogo(asset('images/logo.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('images/logo.png'))
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => [
                    50 => '240, 249, 251',
                    100 => '224, 242, 247',
                    200 => '188, 229, 238',
                    300 => '152, 216, 229',
                    400 => '116, 203, 220',
                    500 => '92, 139, 152',
                    600 => '73, 111, 122',
                    700 => '55, 83, 91',
                    800 => '37, 56, 61',
                    900 => '18, 28, 30',
                    950 => '9, 14, 15',
                ],
            ])
            ->font('Cairo')
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                'panels::styles.after',
                fn () => '<style>@import url("https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap");*{direction:rtl!important;font-family:"Cairo",sans-serif!important}body{direction:rtl!important}[dir="ltr"]{direction:rtl!important}</style>'
            )
            ->navigationGroups([
                NavigationGroup::make('الرئيسية'),
                NavigationGroup::make('العملاء'),
                NavigationGroup::make('العقارات')
                    ->icon('heroicon-o-building-office-2')
                    ->collapsible(),
                NavigationGroup::make('النظام'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
