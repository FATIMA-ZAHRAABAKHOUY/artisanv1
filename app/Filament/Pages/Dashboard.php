<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RevenusChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord';

    public function getHeading(): string
    {
        return 'Tableau de bord';
    }

    public function getSubheading(): ?string
    {
        Carbon::setLocale('fr');

        return now()->translatedFormat('l j F Y');
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            RevenusChart::class,
        ];
    }
}
