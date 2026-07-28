<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que la page d'accueil publique (module Landing) répond correctement
 * et affiche l'offre commerciale (49 DT) et le lien WhatsApp. Aucune donnée
 * métier lue en base — pas besoin de RefreshDatabase ici.
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Notre Jour');
        $response->assertSee((string) config('whatsapp.offer.price'));
    }

    public function test_homepage_contains_a_working_whatsapp_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://wa.me/'.config('whatsapp.phone_e164'), false);
    }

    /**
     * Vérifie que les sections ajoutées en Phase 2 (refonte premium) sont
     * bien présentes : avantages, étapes, modèles, FAQ.
     */
    public function test_homepage_contains_the_premium_redesign_sections(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Comment ça marche');
        $response->assertSee('Nos modèles');
        $response->assertSee('Vous vous posez peut-être ces questions');
        $response->assertSee('En promotion');
    }
}
