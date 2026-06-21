<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    $user = User::create([
        'nom' => 'T', 'prenom' => 'T', 'email' => 'ocl-test-' . time() . '@t.local',
        'password' => bcrypt('x'), 'role' => 'fournisseur', 'statut' => 'actif',
    ]);
    Fournisseur::create([
        'user_id' => $user->id, 'nom' => 'OCL Test', 'type' => 'en_ligne',
        'statut' => 'inactif', 'email' => $user->email, 'site_web' => null,
        'remise_cooperative' => 0,
    ]);
    echo "Unexpected success without site_web\n";
    DB::rollBack();
} catch (Throwable $e) {
    DB::rollBack();
    echo "Expected OCL failure: " . $e->getMessage() . "\n";
}
