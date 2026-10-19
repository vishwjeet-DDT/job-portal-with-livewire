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
        Schema::table('industries', function (Blueprint $table) {
            $table->unsignedBigInteger('industry_types_id')->nullable()->after('id');
            $table->foreign('industry_types_id')
                  ->references('id')
                  ->on('industry_types')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            $table->dropForeign(['industry_types_id']);
            $table->dropColumn('industry_types_id');
        });
    }
};
