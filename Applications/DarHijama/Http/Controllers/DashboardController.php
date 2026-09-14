<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\Patient;
use Applications\DarHijama\Domain\Practitioner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request, DarHijamaOperations $operations): View
    {
        $operations->recordDashboardView(hash('sha256', implode('|', [
            (string) $request->user()?->getAuthIdentifier(),
            (string) $request->ip(),
        ])));

        return view('dar-hijama::dashboard', [
            'patientCount' => Patient::query()->count(),
            'practitionerCount' => Practitioner::query()->where('active', true)->count(),
            'upcomingAppointmentCount' => Appointment::query()
                ->where('starts_at', '>=', now())
                ->count(),
        ]);
    }
}
