<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;
use App\Models\User;

$admins = User::where('role', 'admin')->get();
echo 'Admin count: ' . $admins->count() . "\n";

foreach ($admins as $admin) {
    try {
        Notification::envoyer(
            $admin->id,
            'fournisseur_en_attente',
            'Test',
            'Test',
            ['user_id' => 1]
        );
        echo "OK admin {$admin->id}\n";
    } catch (Throwable $e) {
        echo "FAIL admin {$admin->id}: {$e->getMessage()}\n";
    }
}
