<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Domain\Article;
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
            'whatsappContactUrl' => WhatsAppLinkBuilder::make()->link(
                'السلام عليكم، لدي استفسار حول خدمة الحجامة المنزلية.'
            ),
            'latestArticles' => Article::query()
                ->published()
                ->where('noindex', false)
                ->with('category')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
