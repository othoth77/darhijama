<?php

namespace Mythos\Core\PublicLinks\Contracts;

use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface PublicLinkGenerator
{
    public function forToken(
        string $routeName,
        string $token,
        string $parameter = 'token',
        array $parameters = [],
    ): string;
}
