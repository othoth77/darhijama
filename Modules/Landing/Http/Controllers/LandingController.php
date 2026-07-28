<?php

namespace Modules\Landing\Http\Controllers;

use App\Contracts\Templates\TemplateCatalog;
use App\Events\PublicPageViewed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mythos\Core\Analytics\VisitorFingerprint;
use Mythos\Core\WhatsApp\WhatsAppLinkBuilder;

/**
 * Page d'accueil publique. Le contenu éditorial reste local au module Landing,
 * tandis que la sélection de modèles passe par le contrat partagé TemplateCatalog.
 */ class LandingController extends Controller
{
    public function index(
        Request $request,
        VisitorFingerprint $fingerprint,
        TemplateCatalog $templates,
    ): View {
        PublicPageViewed::dispatch('landing', $fingerprint->fromRequest($request));

        return view('landing::index', [
            'offerPrice' => config('whatsapp.offer.price'),
            'offerCurrency' => config('whatsapp.offer.currency'),
            'whatsappUrl' => WhatsAppLinkBuilder::make()->link(),
            'navLinks' => $this->navLinks(),
            'features' => $this->features(),
            'steps' => $this->steps(),
            'gallery' => $templates->featured(),
            'faqs' => $this->faqs(),
        ]);
    }

    /**
     * @return array<int, array{label: string, href: string}>
     */
    protected function navLinks(): array
    {
        return [
            ['label' => 'Accueil', 'href' => '#accueil'],
            ['label' => 'Nos modèles', 'href' => '#modeles'],
            ['label' => 'Comment ça marche', 'href' => '#comment-ca-marche'],
            ['label' => 'FAQ', 'href' => '#faq'],
            ['label' => 'Contact', 'href' => '#contact'],
        ];
    }

    /**
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    protected function features(): array
    {
        return [
            ['icon' => 'sparkles', 'title' => 'Invitation personnalisée', 'description' => 'Chaque détail est conçu sur mesure, selon votre histoire et votre style.'],
            ['icon' => 'chat-bubble-left-right', 'title' => 'Partage WhatsApp instantané', 'description' => 'Un lien unique, envoyé en un instant à tous vos invités.'],
            ['icon' => 'device-phone-mobile', 'title' => 'Compatible mobile', 'description' => 'Une expérience fluide et élégante, sur tous les écrans.'],
            ['icon' => 'photo', 'title' => 'Galerie photo', 'description' => 'Racontez votre histoire à travers une galerie soignée.'],
            ['icon' => 'musical-note', 'title' => 'Musique', 'description' => 'Ajoutez une bande sonore qui accompagne vos invités.'],
            ['icon' => 'map-pin', 'title' => 'Google Maps', 'description' => 'Un itinéraire clair vers le lieu de votre mariage.'],
            ['icon' => 'check-circle', 'title' => 'RSVP', 'description' => 'Vos invités confirment leur présence en un clic.'],
            ['icon' => 'clock', 'title' => 'Compte à rebours', 'description' => 'Le temps qui s\'écoule jusqu\'au grand jour, en direct.'],
            ['icon' => 'calendar-days', 'title' => 'Programme de mariage', 'description' => 'Le déroulé de votre journée, présenté avec élégance.'],
            ['icon' => 'bolt', 'title' => 'Support rapide', 'description' => 'Une équipe disponible sur WhatsApp pour vous accompagner.'],
        ];
    }

    /**
     * @return array<int, array{number: string, title: string, description: string}>
     */
    protected function steps(): array
    {
        return [
            ['number' => '01', 'title' => 'Contactez-nous', 'description' => 'Un message WhatsApp suffit pour démarrer, sans compte à créer.'],
            ['number' => '02', 'title' => 'Nous créons votre invitation', 'description' => 'Selon vos souhaits : couleurs, textes, photos, ambiance.'],
            ['number' => '03', 'title' => 'Vous recevez votre lien', 'description' => 'Une invitation digitale prête, personnalisée et élégante.'],
            ['number' => '04', 'title' => 'Vous partagez', 'description' => 'Envoyez votre invitation à tous vos invités, en un instant.'],
        ];
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    protected function faqs(): array
    {
        return [
            ['question' => 'Puis-je personnaliser entièrement mon invitation ?', 'answer' => 'Oui. Couleurs, textes, photos, musique, programme — chaque élément est adapté à votre histoire.'],
            ['question' => 'Puis-je ajouter ma musique ?', 'answer' => 'Oui, vous pouvez intégrer une bande sonore qui accompagne vos invités durant leur visite.'],
            ['question' => 'Puis-je modifier après livraison ?', 'answer' => 'Oui, des ajustements restent possibles après la livraison — contactez-nous simplement sur WhatsApp.'],
            ['question' => 'Combien de temps faut-il ?', 'answer' => 'Le délai dépend du niveau de personnalisation souhaité ; notre équipe vous communique une estimation précise dès le premier échange.'],
        ];
    }
}
