<?php

namespace Modules\Invitations\Http\Controllers;

use App\Analytics\VisitorFingerprint;
use App\Events\InvitationViewed;
use App\Events\PublicPageViewed;
use App\Support\WhatsApp\WhatsAppLinkBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Enums\RsvpStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\InvitationExternalUrlService;
use Modules\Invitations\Services\InvitationPublicCache;
use Modules\Invitations\Services\InvitationPublicLinkService;
use Modules\Invitations\Services\InvitationQrCodeService;
use Modules\Invitations\Services\SubmitRsvpService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contrôleur 100% public (visiteurs, sans authentification — pas de comptes
 * clients dans le MVP, voir ARCHITECTURE.md). Seules les invitations dont le
 * statut est InvitationStatus::Publie sont accessibles ; toute autre valeur
 * (brouillon, archivée) ou tout token inconnu renvoie un 404 générique, afin
 * de ne jamais laisser fuiter l'existence d'une invitation non publiée.
 */
class InvitationPublicController extends Controller
{
    public function __construct(
        private readonly InvitationPublicLinkService $publicLinkService,
        private readonly InvitationQrCodeService $qrCodeService,
        private readonly InvitationPublicCache $publicCache,
        private readonly InvitationExternalUrlService $externalUrls,
        private readonly SubmitRsvpService $submitRsvp,
        private readonly VisitorFingerprint $fingerprint,
    ) {}

    public function show(Request $request, string $token): View
    {
        $invitation = $this->findPublishedOrFail($token);

        $visitorHash = $this->fingerprint->fromRequest($request);
        PublicPageViewed::dispatch('invitation', $visitorHash, 'invitation', $invitation->id);
        InvitationViewed::dispatch($invitation->id, $visitorHash);

        return $this->render($invitation);
    }

    public function preview(Request $request, string $token): View
    {
        $invitation = Invitation::query()->where('public_token', $token)->firstOrFail();
        abort_unless($request->user()?->can('view', $invitation), 403);

        return $this->render($invitation);
    }

    private function render(Invitation $invitation): View
    {
        $cached = $this->publicCache->data($invitation);
        $publicUrl = $this->publicLinkService->show($invitation);

        return view('invitations::public.show', [
            'invitation' => $invitation,
            'heroUrl' => $cached['images']->first()['url'] ?? null,
            'gallery' => $cached['images'],
            'videoUrl' => $cached['video_url'],
            'musicUrl' => $cached['audio_url'],
            'externalVideoUrl' => $this->externalUrls->video($invitation->external_video_url),
            'externalAudioUrl' => $this->externalUrls->audio($invitation->external_audio_url),
            'mapsEmbedUrl' => $this->externalUrls->maps($invitation->maps_embed_url),
            'facebookUrl' => $this->externalUrls->facebook($invitation->facebook_url),
            'instagramUrl' => $this->externalUrls->instagram($invitation->instagram_url),
            'programSteps' => $cached['program_steps'],
            'publicUrl' => $publicUrl,
            'qrUrl' => $this->publicLinkService->qr($invitation),
            'rsvpUrl' => $this->publicLinkService->rsvp($invitation),
            'whatsappShareUrl' => WhatsAppLinkBuilder::make()->link(
                "Vous êtes invités au mariage de {$invitation->groom_name} & {$invitation->bride_name} ! {$publicUrl}"
            ),
        ]);
    }

    public function rsvp(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->findPublishedOrFail($token);

        $data = $request->validate([
            'status' => ['required', Rule::enum(RsvpStatus::class)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'guests_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'max:0'],
        ]);

        $response = $this->submitRsvp->execute(
            $invitation,
            $data,
            $this->fingerprint->fromRequest($request),
            $request->session()->get("rsvp_correction.{$invitation->id}"),
        );
        $request->session()->put("rsvp_correction.{$invitation->id}", $response->correction_token);

        return back()->with('rsvp_success', true)->withFragment('rsvp');
    }

    public function qrCode(string $token): StreamedResponse
    {
        return $this->qrCodeService->response(
            $this->findPublishedOrFail($token),
        );
    }

    protected function findPublishedOrFail(string $token): Invitation
    {
        return Invitation::query()
            ->where('public_token', $token)
            ->where('status', InvitationStatus::Publie)
            ->firstOrFail();
    }
}
