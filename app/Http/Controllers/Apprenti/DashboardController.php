<?php

namespace App\Http\Controllers\Apprenti;

use App\Http\Controllers\Controller;
use App\Models\FormationApprenti;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('fr');

        $user = auth()->user();

        FormationApprenti::syncForUser($user->id);

        $inscriptions = FormationApprenti::query()
            ->where('apprenti_id', $user->id)
            ->with([
                'formation.artisan.user',
                'formation.formateurs.user',
                'formation.etapes',
            ])
            ->latest('date_inscription')
            ->get();

        $total       = $inscriptions->count();
        $terminees   = $inscriptions->where('statut', FormationApprenti::STATUT_TERMINEE)->count();
        $enCours     = $inscriptions->where('statut', FormationApprenti::STATUT_EN_COURS)->count();
        $certificats = $inscriptions->filter(fn (FormationApprenti $i) => $i->aCertificat())->count();

        $progressionGlobale = $total > 0
            ? (int) round(($terminees / $total) * 100)
            : 0;

        $stats = compact('total', 'terminees', 'enCours', 'certificats', 'progressionGlobale');

        $activites = $this->buildActivites($inscriptions, $user->id);

        return view('apprenti.dashboard', [
            'user'         => $user,
            'formations'   => $inscriptions,
            'stats'        => $stats,
            'activites'    => $activites,
            'dateActuelle' => now()->translatedFormat('l j F Y'),
        ]);
    }

    private function buildActivites(Collection $inscriptions, int $userId): Collection
    {
        $activites = collect();

        foreach ($inscriptions as $inscription) {
            $formation = $inscription->formation;
            if (! $formation) {
                continue;
            }

            if ($inscription->estTerminee()) {
                $activites->push([
                    'texte' => "Vous avez terminé la formation « {$formation->titre} »",
                    'date'  => $inscription->date_completion ?? $inscription->updated_at,
                ]);
            }

            $etapes = $formation->etapes ?? collect();
            if ($etapes->isNotEmpty() && $inscription->progression > 0) {
                $index = min(
                    $etapes->count() - 1,
                    (int) floor(($inscription->progression / 100) * $etapes->count())
                );
                $etape = $etapes->values()[$index] ?? null;

                if ($etape) {
                    $activites->push([
                        'texte' => "Vous avez complété le module « {$etape->titre} » de la formation « {$formation->titre} »",
                        'date'  => $inscription->updated_at,
                    ]);
                }
            }
        }

        Notification::query()
            ->where('user_id', $userId)
            ->whereIn('type', ['formation', 'certificat_disponible', 'progression'])
            ->latest()
            ->take(5)
            ->get()
            ->each(function (Notification $notif) use ($activites) {
                $activites->push([
                    'texte' => $notif->message ?? $notif->titre ?? 'Activité formation',
                    'date'  => $notif->created_at,
                ]);
            });

        return $activites
            ->sortByDesc(fn ($a) => $a['date']?->timestamp ?? 0)
            ->take(8)
            ->values();
    }
}
