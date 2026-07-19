<?php

namespace App\Domains\Certifications\Enums;

enum CertificationTrack: string
{
    case PaidProfessional = 'paid_professional';
    case FreeCredential = 'free_credential';
    case SkillSpecialisation = 'skill_specialisation';

    public function label(): string
    {
        return match ($this) {
            self::PaidProfessional => 'Paid professional',
            self::FreeCredential => 'Free credential',
            self::SkillSpecialisation => 'Skill specialisation',
        };
    }
}
