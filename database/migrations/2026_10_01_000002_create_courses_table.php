<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 20);
            $table->unsignedTinyInteger('sks')->default(3);
            $table->string('lecturer', 100)->nullable();
            $table->unsignedTinyInteger('day');          // 0 = Minggu ... 6 = Sabtu
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 30)->default('book');
            $table->string('tone', 10)->default('primary');
            $table->string('stripe', 9)->default('#5B3DE0');
            $table->timestamps();

            $table->unique(['user_id', 'code']);
            $table->index(['user_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
