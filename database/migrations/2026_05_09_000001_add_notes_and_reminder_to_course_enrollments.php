<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status');
            $table->boolean('reminder_enabled')->default(false)->after('notes');
            $table->string('reminder_day', 10)->nullable()->after('reminder_enabled'); // Monday–Sunday
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['notes', 'reminder_enabled', 'reminder_day']);
        });
    }
};
