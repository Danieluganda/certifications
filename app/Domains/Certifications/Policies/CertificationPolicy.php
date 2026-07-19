<?php

namespace App\Domains\Certifications\Policies;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;

class CertificationPolicy
{
    public function view(User $user, Certification $certification): bool
    {
        return $certification->user_id === $user->id;
    }
}
