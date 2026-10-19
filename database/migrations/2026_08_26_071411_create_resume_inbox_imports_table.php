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
        Schema::create('resume_inbox_imports', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable()->index();
            $table->unsignedInteger('uid')->nullable();
            $table->string('attachment_filename');
            $table->string('attachment_hash', 64)->index();
            $table->foreignId('imported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_inbox_imports');
    }
};
