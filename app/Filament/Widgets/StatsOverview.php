<?php

namespace App\Filament\Widgets;

use App\Services\DashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $data  = app(DashboardService::class)->getDashboardData();
        $stats = $data['stats'];
        $kpis  = $data['kpis'];

        return [
            Stat::make('Utilisateurs', $stats['users_total'])
                ->description('Clients : '.$stats['users_clients'])
                ->color('primary')
                ->url(route('admin.dashboard')),

            Stat::make('Artisans actifs', $stats['artisans_actifs'])
                ->description($stats['artisans_en_attente'].' en attente')
                ->color('warning'),

            Stat::make('Chiffre d\'affaires', number_format($stats['ca_total'], 2, ',', ' ').' MAD')
                ->description('Panier moy. : '.number_format($kpis['panier_moyen'], 2, ',', ' ').' MAD')
                ->color('success'),

            Stat::make('Commandes', $stats['commandes_total'])
                ->description($stats['commandes_pending'].' en attente')
                ->color('info'),

            Stat::make('Produits actifs', $stats['produits_actifs'])
                ->description($stats['produits_rupture'].' en rupture')
                ->color('primary'),

            Stat::make('Formations', $stats['formations_actives'])
                ->description($stats['inscriptions_total'].' inscriptions')
                ->color('gray'),
        ];
    }
}
