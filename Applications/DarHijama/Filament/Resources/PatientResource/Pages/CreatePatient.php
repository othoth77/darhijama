<?php

namespace Applications\DarHijama\Filament\Resources\PatientResource\Pages;

use Applications\DarHijama\Application\Services\PatientService;
use Applications\DarHijama\Filament\Resources\PatientResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PatientService::class)->create($data, (int) auth()->id());
    }
}
