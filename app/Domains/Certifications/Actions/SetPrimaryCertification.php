<?php

namespace App\Domains\Certifications\Actions;

use App\Domains\Certifications\Enums\CertificationTrack;
use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SetPrimaryCertification
{
    public function execute(User $user, Certification $certification): void
    {
        if ($certification->user_id !== $user->id) {
            throw new InvalidArgumentException('The certification does not belong to this user.');
        }

        if ($certification->track_type !== CertificationTrack::PaidProfessional) {
            throw new InvalidArgumentException('Only paid professional certifications can be primary.');
        }

        if (in_array($certification->status, ['Completed', 'Archived'], true)) {
            throw new InvalidArgumentException('Completed or archived certifications cannot be activated.');
        }

        DB::transaction(function () use ($user, $certification): void {
            Certification::query()
                ->where('user_id', $user->id)
                ->where('track_type', CertificationTrack::PaidProfessional->value)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            $certification->forceFill([
                'is_primary' => true,
                'status' => 'Active',
            ])->save();
        });
    }
}
