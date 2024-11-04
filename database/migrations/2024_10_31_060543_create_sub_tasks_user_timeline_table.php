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
        Schema::create('sub_tasks_user_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Ensure users table exists
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Ensure products table exists
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Ensure projects table exists
            $table->foreignId('task_id')->constrained()->onDelete('cascade'); // Ensure tasks table exists
            $table->foreignId('subtask_id')->constrained('sub_tasks')->onDelete('cascade'); // Ensure subtasks table exists
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_tasks_user_timeline');
    }
};
