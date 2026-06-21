<?php

namespace App\Filament\Widgets;

use App\Models\Paiement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenusChart extends ChartWidget
{
    protected static ?string $heading = 'Revenus 6 derniers mois (MAD)';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(
            fn (int $offset) => now()->subMonths($offset)->startOfMonth()
        );

        $revenues = Paiement::query()
            ->join('commandes', 'paiements.commande_id', '=', 'commandes.id')
            ->where('paiements.statut', 'paid')
            ->where('paiements.created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("TO_CHAR(DATE_TRUNC('month', paiements.created_at), 'YYYY-MM') as month_key, SUM(paiements.montant) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $labels = $months->map(fn (Carbon $month) => $month->translatedFormat('M Y'))->all();

        $data = $months->map(
            fn (Carbon $month) => (float) ($revenues[$month->format('Y-m')] ?? 0)
        )->all();

        return [
            'datasets' => [
                [
                    'label'           => 'Revenus (MAD)',
                    'data'            => $data,
                    'backgroundColor' => '#C8913A',
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
