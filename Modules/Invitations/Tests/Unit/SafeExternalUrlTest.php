<?php

namespace Modules\Invitations\Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Modules\Invitations\Rules\SafeExternalUrl;
use Tests\TestCase;

class SafeExternalUrlTest extends TestCase
{
    public function test_it_accepts_https_urls_from_allowed_hosts_and_subdomains(): void
    {
        $rule = new SafeExternalUrl(['google.com']);

        $this->assertTrue(Validator::make(['url' => 'https://maps.google.com/place'], ['url' => [$rule]])->passes());
    }

    public function test_it_rejects_http_credentials_and_unapproved_hosts(): void
    {
        $rule = new SafeExternalUrl(['google.com']);

        $this->assertFalse(Validator::make(['url' => 'http://google.com'], ['url' => [$rule]])->passes());
        $this->assertFalse(Validator::make(['url' => 'https://user:pass@google.com'], ['url' => [$rule]])->passes());
        $this->assertFalse(Validator::make(['url' => 'https://google.com.evil.example'], ['url' => [$rule]])->passes());
    }
}
