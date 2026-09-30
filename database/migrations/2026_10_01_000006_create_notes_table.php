<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 100);
            $table->longText('content')->nullable();
            $table->date('date');
            $table->enum('tag', ['kuliah', 'project', 'ide', 'pkm', 'pribadi'])->default('kuliah');
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
