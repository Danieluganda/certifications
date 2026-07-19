<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->foreignId('domain_id')->nullable()->after('certification_id')->constrained('certification_domains')->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->after('domain_id')->constrained('topics')->nullOnDelete();
            $table->text('file_path')->nullable()->after('url');
            $table->text('copyright_note')->nullable()->after('copyright_status');
            $table->unsignedTinyInteger('rating')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('domain_id');
            $table->dropConstrainedForeignId('topic_id');
            $table->dropColumn(['file_path', 'copyright_note', 'rating']);
        });
    }
};
