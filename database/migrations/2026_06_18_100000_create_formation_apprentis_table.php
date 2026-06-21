<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_apprentis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprenti_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $table->enum('statut', ['non_commencee', 'en_cours', 'terminee'])->default('non_commencee');
            $table->unsignedTinyInteger('progression')->default(0);
            $table->timestamp('date_inscription')->nullable();
            $table->timestamp('date_completion')->nullable();
            $table->string('certificat_url')->nullable();
            $table->timestamps();

            $table->unique(['apprenti_id', 'formation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_apprentis');
    }
};
