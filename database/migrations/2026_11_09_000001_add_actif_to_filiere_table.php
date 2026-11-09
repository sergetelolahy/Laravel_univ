<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filiere', function (Blueprint $table): void {
            $table->boolean('actif')->default(true);
        });

        DB::table('filiere')
            ->whereIn('code', ['M1-IG', 'L1-GB'])
            ->update([
                'actif' => false,
                'nom' => DB::raw("CONCAT('Archive - ', nom)"),
            ]);
    }

    public function down(): void
    {
        DB::table('filiere')
            ->whereIn('code', ['M1-IG', 'L1-GB'])
            ->where('nom', 'like', 'Archive - %')
            ->update([
                'actif' => true,
                'nom' => DB::raw("SUBSTRING(nom, 11)"),
            ]);

        Schema::table('filiere', function (Blueprint $table): void {
            $table->dropColumn('actif');
        });
    }
};
