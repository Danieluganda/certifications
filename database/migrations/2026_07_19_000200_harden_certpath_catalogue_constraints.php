<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE UNIQUE INDEX certifications_one_primary_paid_per_user ON certifications (user_id) WHERE is_primary = 1 AND track_type = 'paid_professional'");
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX certifications_one_primary_paid_per_user ON certifications (user_id) WHERE is_primary = true AND track_type = 'paid_professional'");
        }

        if (DB::getDriverName() === 'mysql') {
            // MySQL has no partial indexes. Application actions still enforce the rule.
            // A generated-column strategy can be added when MySQL becomes a supported target.
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['sqlite', 'pgsql'], true)) {
            DB::statement('DROP INDEX IF EXISTS certifications_one_primary_paid_per_user');
        }
    }
};
