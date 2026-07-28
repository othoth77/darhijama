<?php

namespace App\Http\Controllers;

use App\Analytics\VisitorFingerprint;
use App\Events\WhatsappClicked;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AnalyticsController extends Controller
{
    public function whatsappClick(Request $request, VisitorFingerprint $fingerprint): Response
    {
        if (! $request->has('source')) {
            $request->merge((array) json_decode($request->getContent(), true));
        }

        $data = $request->validate([
            'source' => ['required', 'string', 'max:64'],
            'invitation_id' => ['sometimes', 'integer', 'min:1'],
            'template_id' => ['sometimes', 'integer', 'min:1'],
        ]);

        WhatsappClicked::dispatch(
            $data['source'],
            $fingerprint->fromRequest($request),
            array_filter([
                'invitation_id' => $data['invitation_id'] ?? null,
                'template_id' => $data['template_id'] ?? null,
            ]),
        );

        return response()->noContent();
    }
}
