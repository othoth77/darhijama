<?php

namespace Modules\Invitations\Services;

use Illuminate\Support\Facades\Validator;
use Modules\Invitations\Rules\SafeExternalUrl;

class InvitationExternalUrlService
{
    public function maps(?string $url): ?string
    {
        return $this->safe($url, ['google.com', 'google.tn']);
    }

    public function video(?string $url): ?string
    {
        return $this->safe($url, [
            'youtube.com',
            'youtu.be',
            'youtube-nocookie.com',
            'vimeo.com',
        ]);
    }

    public function audio(?string $url): ?string
    {
        return $this->safe($url, [
            'soundcloud.com',
            'spotify.com',
            'cdn.notrejour.tn',
        ]);
    }

    public function social(?string $url): ?string
    {
        return $this->safe($url, [
            'facebook.com',
            'instagram.com',
        ]);
    }

    private function safe(?string $url, array $hosts): ?string
    {
        if (blank($url)) {
            return null;
        }

        return Validator::make(
            ['url' => $url],
            ['url' => [new SafeExternalUrl($hosts)]],
        )->passes() ? $url : null;
    }
}
