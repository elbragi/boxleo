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
        Schema::table('system_requests', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->string('developer_name')->nullable()->after('effort_hours');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('system_requests', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('developer_name');
        });
    }
};
