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
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('functional_area_id')->nullable();
            $table->bigInteger('industry_id')->nullable();
            $table->text('ceo_name')->nullable();
            $table->text('company_name')->nullable();
            $table->text('ownership_type')->nullable();
            $table->text('company_size')->nullable();
            $table->text('established_year')->nullable();
            $table->string('logo')->nullable();
            $table->text('location')->nullable();
            $table->text('second_office_location')->nullable();
            $table->text('description')->nullable();
            $table->text('website')->nullable();
            $table->text('facebook_url')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->boolean('status')->default(1);
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
