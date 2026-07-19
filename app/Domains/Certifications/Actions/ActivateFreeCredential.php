<?php

namespace App\Domains\Certifications\Actions;

use App\Domains\Certifications\Enums\CertificationTrack;
use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use InvalidArgumentException;

final class ActivateFreeCredential
{
    public function execute(User $user, Certification $certification): void
    {
        if ($certification->user_id !== $user->id) {
            throw new InvalidArgumentException('The credential does not belong to this user.');
        }

        if ($certification->track_type !== CertificationTrack::FreeCredential) {
            throw new InvalidArgumentException('Only free credentials can be activated through this action.');
        }

        if (in_array($certification->status, ['Completed', 'Archived'], true)) {
            throw new InvalidArgumentException('Completed or archived credentials cannot be activated.');
        }

        $maxActive = $user->profile?->max_active_free_credentials ?? 2;
        $activeCount = Certification::query()
            ->where('user_id', $user->id)
            ->where('track_type', CertificationTrack::FreeCredential->value)
            ->where('status', 'Active')
            ->whereKeyNot($certification->id)
            ->count();

        if ($activeCount >= $maxActive) {
            throw new InvalidArgumentException('The active free credential limit has been reached.');
        }

        $certification->forceFill(['status' => 'Active'])->save();
    }
}
