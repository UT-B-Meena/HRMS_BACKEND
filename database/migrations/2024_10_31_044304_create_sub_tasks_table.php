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
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing unsigned big integer (bigint unsigned) column as primary key
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->time('estimated_hours')->default('00:00');
            $table->date('dead_line');
            $table->integer('extended_status')->default(0)->comment('0 = not extended, 1 = extended');
            $table->time('extended_hours')->default('00:00')->comment('Time in HH:MM format, updated when task is extended');
            $table->integer('active_status')->default(1)->comment('0 = hold, 1 = active');
            $table->integer('status')->comment('0 - pending, 1 - In Progress, 2 - Pending For Approval, 3 - Completed');

            $table->time('total_hours_worked')->default('00:00:00');
            $table->integer('rating')->default(0);
            $table->string('command')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('cascade');;
            $table->text('remark')->nullable();
            $table->integer('reopen_status')->default(0)->comment('0 = not reopen, 1 = reopen');
            $table->text(column: 'description')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->string('priority')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes(); // Adds a deleted_at column for soft deleting
            $table->timestamps();  // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_tasks');
    }
};
