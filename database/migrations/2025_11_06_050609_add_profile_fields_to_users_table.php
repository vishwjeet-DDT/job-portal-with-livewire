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
        Schema::table('users', function (Blueprint $table) {
           $table->string('first_name')->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('mobile_number', 20)->nullable()->after('email');
            $table->string('profile_img')->nullable()->after('mobile_number');
            $table->string('country_id')->nullable()->after('mobile_number');
            $table->string('state_id')->nullable()->after('country_id');
            $table->string('city_id')->nullable()->after('state_id');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('password');
            $table->enum('industry_type', ['it', 'non_it'])->nullable()->after('gender');
            $table->enum('experience_type', ['experienced', 'fresher'])->default('fresher')->after('industry_type');
            $table->enum('status',['active','inactive'])->default('active')->after('experience_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'mobile_number',
                'profile_img',
                'country_id',
                'state_id',
                'city_id',
                'gender',
                'industry_type',
                'experience_type',
                'status'
            ]);
        });
    }
};
