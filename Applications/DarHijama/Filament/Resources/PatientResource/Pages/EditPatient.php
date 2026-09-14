<?php

namespace Applications\DarHijama\Filament\Resources\PatientResource\Pages;

use Applications\DarHijama\Application\Services\PatientService;
use Applications\DarHijama\Domain\Patient;
use Applications\DarHijama\Filament\Resources\PatientResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPatient extends EditRecord
{
    protected static string $resource = PatientResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        abort_unless($record instanceof Patient, 404);

        return app(PatientService::class)->update(
            $record,
            $data,
            (int) auth()->id(),
        );
    }
}
