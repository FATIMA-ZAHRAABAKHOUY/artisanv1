<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Fournisseur;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$email = 'sim-fourn-' . time() . '@test.local';

DB::beginTransaction();
try {
    $user = User::create([
        'nom'       => 'Sim',
        'prenom'    => 'Fourn',
        'email'     => $email,
        'password'  => bcrypt('password123'),
        'telephone' => '0612345678',
        'ville'     => 'Fes',
        'role'      => 'fournisseur',
        'statut'    => 'actif',
    ]);
    echo "User created: {$user->id}\n";

    $fournisseur = Fournisseur::create([
        'user_id'            => $user->id,
        'nom'                => 'Test Entreprise',
        'type'               => 'local',
        'statut'             => 'inactif',
        'email'              => $email,
        'telephone'          => '0612345678',
        'ville'              => 'Fes',
        'site_web'           => null,
        'remise_cooperative' => 0,
    ]);
    echo "Fournisseur created: {$fournisseur->id}\n";

    Notification::envoyer(
        $user->id,
        'fournisseur_en_attente',
        'Test',
        'Test message',
        ['user_id' => $user->id]
    );
    echo "Notification sent\n";

    DB::rollBack();
    echo "Rolled back (simulation only).\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "FAILED at step: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
