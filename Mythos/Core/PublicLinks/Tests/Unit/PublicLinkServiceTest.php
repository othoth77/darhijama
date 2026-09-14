<?php

namespace Mythos\Core\PublicLinks\Tests\Unit;

use InvalidArgumentException;
use Mythos\Core\PublicLinks\PublicLinkService;
use Tests\TestCase;

class PublicLinkServiceTest extends TestCase
{
    public function test_it_generates_the_existing_token_based_public_link(): void
    {
        $url = app(PublicLinkService::class)->forToken(
            'invitations.public.show',
            '01TESTTOKEN000000000000000',
        );

        $this->assertSame(
            url('/i/01TESTTOKEN000000000000000'),
            $url,
        );
    }

    public function test_it_rejects_an_unsafe_token(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PublicLinkService::class)->forToken(
            'invitations.public.show',
            '../invitation',
        );
    }
}
