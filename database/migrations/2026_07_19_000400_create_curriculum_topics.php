<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained('certification_domains')->cascadeOnDelete();
            $table->string('name');
            $table->text('prerequisites')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedTinyInteger('mastery_percent')->default(0);
            $table->timestamps();

            $table->unique(['domain_id', 'name']);
            $table->index(['certification_id', 'position']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('domain_id')->constrained('topics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });

        Schema::dropIfExists('topics');
    }
};
