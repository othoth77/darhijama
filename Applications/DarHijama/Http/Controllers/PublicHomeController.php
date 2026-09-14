<?php

namespace Applications\DarHijama\Http\Controllers;

use Illuminate\View\View;
use Mythos\Core\WhatsApp\WhatsAppLinkBuilder;

class PublicHomeController
{
    public function __invoke(): View
    {
        return view('dar-hijama::public.home', [
            'whatsappBookingUrl' => WhatsAppLinkBuilder::make()->link(
                'السلام عليكم، أرغب في حجز موعد حجامة في دار الحجامة.'
            ),
        ]);
    }
}
