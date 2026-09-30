<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('university', 100)->nullable()->after('email');
            $table->string('program', 100)->nullable()->after('university');
            $table->unsignedTinyInteger('semester')->nullable()->after('program');
            $table->unsignedSmallInteger('entry_year')->nullable()->after('semester'); // angkatan
            $table->string('avatar')->nullable()->after('entry_year');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['university', 'program', 'semester', 'entry_year', 'avatar']);
        });
    }
};