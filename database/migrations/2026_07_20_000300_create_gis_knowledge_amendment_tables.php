<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status')->default('Planned');
            $table->unsignedTinyInteger('priority')->default(3);
            $table->date('target_completion_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'status', 'priority']);
        });

        Schema::create('certification_specialisation', function (Blueprint $table) {
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialisation_id')->constrained('specialisations')->cascadeOnDelete();

            $table->primary(['certification_id', 'specialisation_id'], 'cert_specialisation_primary');
        });

        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialisation_id')->constrained('specialisations')->cascadeOnDelete();
            $table->string('name');
            $table->string('dataset_type');
            $table->text('source_url')->nullable();
            $table->string('licence')->nullable();
            $table->text('description')->nullable();
            $table->text('storage_path')->nullable();
            $table->json('schema_metadata_json')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dataset_type']);
            $table->index(['certification_id', 'specialisation_id'], 'datasets_cert_specialisation_idx');
        });

        Schema::create('ontology_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialisation_id')->constrained('specialisations')->cascadeOnDelete();
            $table->string('name');
            $table->string('resource_type');
            $table->text('namespace_uri')->nullable();
            $table->text('source_url')->nullable();
            $table->string('version')->nullable();
            $table->string('licence')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resource_type']);
        });

        Schema::create('search_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('engine');
            $table->string('index_name');
            $table->string('status')->default('Planned');
            $table->unsignedInteger('document_count')->default(0);
            $table->json('configuration_json')->nullable();
            $table->dateTime('last_indexed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'engine', 'index_name'], 'search_indexes_unique_user_engine_name');
            $table->index(['project_id', 'status']);
        });

        Schema::create('analytics_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('property_name');
            $table->text('property_identifier_encrypted')->nullable();
            $table->string('status')->default('Planned');
            $table->json('configuration_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'provider', 'status']);
            $table->index(['project_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_properties');
        Schema::dropIfExists('search_indexes');
        Schema::dropIfExists('ontology_resources');
        Schema::dropIfExists('datasets');
        Schema::dropIfExists('certification_specialisation');
        Schema::dropIfExists('specialisations');
    }
};
