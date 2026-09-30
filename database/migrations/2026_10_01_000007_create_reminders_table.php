<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Tepat satu dari dua kolom ini terisi (dijaga lewat validasi API)
            $table->foreignId('task_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('agenda_id')->nullable()->constrained('agendas')->cascadeOnDelete();
            $table->enum('offset', ['7d', '3d', '1d', '3h', '1h']);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            // Satu target tidak boleh punya pengingat dengan offset yang sama dua kali
            $table->unique(['task_id', 'offset']);
            $table->unique(['agenda_id', 'offset']);
            $table->index(['user_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};