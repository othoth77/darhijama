<?php

namespace Mythos\Core\Support\Api;

enum ApiStatus: string
{
    case Stable = 'stable';
    case Experimental = 'experimental';
    case Internal = 'internal';
}
