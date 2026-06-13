<?php

namespace Tests\Feature;

use App\Models\Usuario_Sefuridad_y_Auditoria\autenticacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    /** @test */
    public function guests_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_dashboard_with_correct_variables()
    {
        // Fetch first user from DB (or create one)
        $user = autenticacion::first();

        if (!$user) {
            // Skip if no user seeded yet in testing db
            $this->markTestSkipped('No user found to authenticate.');
        }

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas([
            'postulantesTotal',
            'postulantesCambioTexto',
            'inscripcionesTotal',
            'inscripcionesCambioTexto',
            'pagosTotal',
            'pagosCambioTexto',
            'gruposAsignadosTotal',
            'grupoCambioTexto',
            'cantInscritos',
            'cantEnProceso',
            'cantPendientes',
            'cantObservados',
            'chartMesLabels',
            'chartMesData',
        ]);
    }
}
