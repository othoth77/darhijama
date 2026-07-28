<?php

namespace Applications\DarHijama\Domain;

enum AppointmentType: string
{
    case InitialConsultation = 'initial_consultation';
    case HijamaSession = 'hijama_session';
    case FollowUp = 'follow_up';
    case HomeVisit = 'home_visit';
}
