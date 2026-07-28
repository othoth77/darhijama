<?php

namespace Modules\Orders\Filament\Resources\ClientResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Orders\Filament\Resources\ClientResource;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;
}
