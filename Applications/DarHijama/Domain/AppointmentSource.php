<?php

namespace Applications\DarHijama\Domain;

enum AppointmentSource: string
{
    case Administration = 'administration';
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
    case Website = 'website';
    case WalkIn = 'walk_in';
}
