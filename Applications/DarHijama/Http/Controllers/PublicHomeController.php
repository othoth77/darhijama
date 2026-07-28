<?php

namespace Applications\DarHijama\Http\Controllers;

use Illuminate\View\View;

class PublicHomeController
{
    public function __invoke(): View
    {
        return view('dar-hijama::public.home');
    }
}
