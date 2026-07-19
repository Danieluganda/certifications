<?php

namespace App\Domains\Certifications\Enums;

enum CertificationTrack: string
{
    case PaidProfessional = 'paid_professional';
    case FreeCredential = 'free_credential';

    public function label(): string
    {
        return match ($this) {
            self::PaidProfessional => 'Paid professional',
            self::FreeCredential => 'Free credential',
        };
    }
}
