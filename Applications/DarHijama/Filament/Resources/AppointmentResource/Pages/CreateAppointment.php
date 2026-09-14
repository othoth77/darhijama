<?php

namespace Applications\DarHijama\Filament\Resources\AppointmentResource\Pages;

use Applications\DarHijama\Application\Services\AppointmentWorkflowService;
use Applications\DarHijama\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AppointmentWorkflowService::class)->book($data, (int) auth()->id());
    }
}
