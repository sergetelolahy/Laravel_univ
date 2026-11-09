<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DonneesPedagogiquesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_preparent_des_donnees_de_demo_coherentes_et_relancables(): void
    {
        $this->seed();
        $premierComptage = $this->comptagesDemo();

        $this->seed();

        $this->assertSame($premierComptage, $this->comptagesDemo());
        $this->assertSame(48, $premierComptage['cours']);
        $this->assertSame(33, $premierComptage['inscriptions']);
        $this->assertSame(24, $premierComptage['evaluations']);
        $this->assertSame(33, $premierComptage['notes']);
        $this->assertSame(24, $premierComptage['seances']);
        $this->assertSame(1, $premierComptage['absences']);
        $this->assertSame(48, $premierComptage['affectations_enseignant']);
        $this->assertSame(8, $premierComptage['groupes']);
        $this->assertSame(48, $premierComptage['cours_identifies_demo']);
    }

    private function comptagesDemo(): array
    {
        $coursDemo = DB::table('cours')
            ->where('intitule', 'like', '[DEMO]%')
            ->count();

        return [
            'cours' => DB::table('cours')->count(),
            'inscriptions' => DB::table('inscription_cours')->count(),
            'evaluations' => DB::table('evaluation')->where('type', 'DEMO_CC')->count(),
            'notes' => DB::table('note')->count(),
            'seances' => DB::table('seance')
                ->whereIn('cours_id', DB::table('cours')->where('intitule', 'like', '[DEMO]%')->select('id'))
                ->count(),
            'absences' => DB::table('absence')->count(),
            'affectations_enseignant' => DB::table('enseignant_cours')->count(),
            'groupes' => DB::table('groupe')->where('nom', 'G1')->count(),
            'cours_identifies_demo' => $coursDemo,
        ];
    }
}
