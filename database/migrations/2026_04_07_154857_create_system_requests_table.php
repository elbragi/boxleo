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
        Schema::create('system_requests', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('pending');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('requested_by')->nullable();
            $table->date('reported_at')->nullable();
            $table->date('target_due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->decimal('effort_hours', 8, 2)->nullable();
            $table->foreignId('developer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_requests');
    }
};
