<?php

namespace Mythos\Core\PublicLinks;

use InvalidArgumentException;
use Mythos\Core\PublicLinks\Contracts\PublicLinkGenerator;

/**
 * Génère les liens publics à token sans exposer la convention de routage aux
 * contrôleurs et services consommateurs.
 */
class PublicLinkService implements PublicLinkGenerator
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function forToken(
        string $routeName,
        string $token,
        string $parameter = 'token',
        array $parameters = [],
    ): string {
        return route(
            $routeName,
            array_merge($parameters, [$parameter => $this->normalizeToken($token)]),
        );
    }

    protected function normalizeToken(string $token): string
    {
        $token = trim($token);

        if (
            $token === ''
            || str_contains($token, '/')
            || str_contains($token, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $token) === 1
        ) {
            throw new InvalidArgumentException('Token de lien public invalide.');
        }

        return $token;
    }
}
