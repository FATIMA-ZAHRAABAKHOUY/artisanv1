<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

/**
 * Service métier du tableau de bord — agrège stats, KPI, graphiques et widgets.
 */
class DashboardService
{
    public function __construct(
        protected DashboardRepository $repository
    ) {}

    public function getDashboardData(): array
    {
        return [
            'stats'    => $this->repository->stats(),
            'kpis'     => $this->repository->kpis(),
            'charts'   => $this->prepareChartsForJs($this->repository->charts()),
            'widgets'  => $this->repository->widgets(),
            'timeline' => $this->repository->timeline(),
        ];
    }

    public function getChartsJson(): array
    {
        return $this->prepareChartsForJs($this->repository->charts());
    }

    /** Formate les collections Eloquent en tableaux Chart.js. */
    protected function prepareChartsForJs(array $charts): array
    {
        return [
            'commandesMois' => [
                'labels' => $charts['commandesParMois']->pluck('mois')->values(),
                'data'   => $charts['commandesParMois']->pluck('total')->values(),
            ],
            'revenusMois' => [
                'labels' => $charts['revenusParMois']->pluck('mois')->values(),
                'data'   => $charts['revenusParMois']->pluck('total')->map(fn ($v) => (float) $v)->values(),
            ],
            'produitsCategorie' => [
                'labels' => $charts['produitsParCategorie']->pluck('label')->values(),
                'data'   => $charts['produitsParCategorie']->pluck('total')->values(),
            ],
            'usersRole' => [
                'labels' => $charts['usersParRole']->pluck('role')->values(),
                'data'   => $charts['usersParRole']->pluck('total')->values(),
            ],
            'commandesStatut' => [
                'labels' => $charts['commandesParStatut']->pluck('statut')->values(),
                'data'   => $charts['commandesParStatut']->pluck('total')->values(),
            ],
            'livraisonsStatut' => [
                'labels' => $charts['livraisonsParStatut']->pluck('statut')->values(),
                'data'   => $charts['livraisonsParStatut']->pluck('total')->values(),
            ],
            'paiementsStatut' => [
                'labels' => $charts['paiementsParStatut']->pluck('statut')->values(),
                'data'   => $charts['paiementsParStatut']->pluck('total')->values(),
            ],
            'progressionFormations' => [
                'labels' => $charts['progressionFormations']->pluck('titre')->values(),
                'data'   => $charts['progressionFormations']->pluck('progression_avg')->map(fn ($v) => round((float) $v, 1))->values(),
            ],
            'topArtisans' => [
                'labels' => $charts['topArtisans']->pluck('nom')->values(),
                'data'   => $charts['topArtisans']->pluck('ca')->map(fn ($v) => (float) $v)->values(),
            ],
            'topProduits' => [
                'labels' => $charts['topProduits']->pluck('nom')->values(),
                'data'   => $charts['topProduits']->pluck('qte')->values(),
            ],
            'topFournisseurs' => [
                'labels' => $charts['topFournisseurs']->pluck('nom')->values(),
                'data'   => $charts['topFournisseurs']->pluck('nb')->values(),
            ],
            'inscriptionsFormation' => [
                'labels' => $charts['inscriptionsParFormation']->pluck('titre')->values(),
                'data'   => $charts['inscriptionsParFormation']->pluck('inscrits')->values(),
            ],
        ];
    }
}
