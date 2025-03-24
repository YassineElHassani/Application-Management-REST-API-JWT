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
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->enum('contract_type', ['full-time', 'part-time', 'freelance']);
            $table->decimal('salary', 10, 2)->nullable();
            $table->dateTime('posted_at')->useCurrent();
            $table->foreignId('recruiter_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            $table->dropForeign(['recruiter_id']);
            $table->dropColumn(['recruiter_id', 'status']);
        });
    }
};
