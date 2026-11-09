<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('etudiant')->whereNull('filiere_id')->exists()) {
            throw new RuntimeException(
                'Chaque étudiant doit être affecté à une filière avant cette migration.'
            );
        }

        Schema::table('etudiant', function (Blueprint $table): void {
            $table->dropForeign(['filiere_id']);
        });

        Schema::table('etudiant', function (Blueprint $table): void {
            $table->foreignId('filiere_id')->nullable(false)->change();
            $table->foreign('filiere_id')->references('id')->on('filiere')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('etudiant', function (Blueprint $table): void {
            $table->dropForeign(['filiere_id']);
        });

        Schema::table('etudiant', function (Blueprint $table): void {
            $table->foreignId('filiere_id')->nullable()->change();
            $table->foreign('filiere_id')->references('id')->on('filiere')->nullOnDelete();
        });
    }
};
