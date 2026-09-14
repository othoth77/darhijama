<?php

namespace Mythos\Core\Analytics;

use Illuminate\Http\Request;

class VisitorFingerprint
{
    public function fromRequest(Request $request): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [(string) $request->ip(), (string) $request->userAgent()]),
            (string) config('app.key'),
        );
    }
}
