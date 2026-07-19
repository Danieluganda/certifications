<?php

namespace App\Domains\Certifications\Enums;

enum CertificationStatus: string
{
    case Planned = 'Planned';
    case Active = 'Active';
    case Completed = 'Completed';
    case LongTerm = 'Long-term';
}
