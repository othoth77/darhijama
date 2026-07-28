<?php

namespace Modules\Invitations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeExternalUrl implements ValidationRule
{
    /**
     * @param  array<int, string>  $allowedHosts
     */
    public function __construct(private readonly array $allowedHosts = []) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            $fail('Cette URL externe est invalide.');

            return;
        }

        $parts = parse_url($value);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (($parts['scheme'] ?? null) !== 'https' || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            $fail('Cette URL externe doit utiliser HTTPS sans identifiants.');

            return;
        }

        if ($this->allowedHosts !== [] && ! $this->hostIsAllowed($host)) {
            $fail('Le domaine de cette URL externe n’est pas autorisé.');
        }
    }

    private function hostIsAllowed(string $host): bool
    {
        foreach ($this->allowedHosts as $allowedHost) {
            $allowedHost = strtolower($allowedHost);

            if ($host === $allowedHost || str_ends_with($host, '.'.$allowedHost)) {
                return true;
            }
        }

        return false;
    }
}
