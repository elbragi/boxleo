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
        Schema::table('payslips', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->boolean('is_rider')->default(false)->after('user_id');
            $table->string('rider_name')->nullable()->after('is_rider');
            $table->integer('deliveries_count')->nullable()->after('rider_name');
            $table->decimal('rate_per_delivery', 10, 2)->nullable()->after('deliveries_count');
            $table->date('start_date')->nullable()->after('rate_per_delivery');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn(['is_rider', 'rider_name', 'deliveries_count', 'rate_per_delivery', 'start_date', 'end_date']);
        });
    }
};
