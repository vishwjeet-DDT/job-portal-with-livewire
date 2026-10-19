<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->enum('employment_type', ['contract', 'permanent', 'hourly']);
            $table->enum('work_mode', ['onsite', 'hybrid', 'remote']);
            $table->enum('gender', ['male', 'female', 'both']);
            $table->bigInteger('industry_type_id');
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->bigInteger('funcational_area_id');
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId(column: 'city_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->string('currency', 10)->default('INR');
            $table->string('experience_level')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('number_of_vacancy')->default(1);
            $table->date('deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'expired'])->default('draft');
            $table->string('shift')->default('morning');

            $table->boolean('is_featured')->default(false);
            $table->integer('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
