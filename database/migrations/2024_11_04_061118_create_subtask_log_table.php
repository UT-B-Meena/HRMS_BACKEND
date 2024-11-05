<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('subtask_log', function (Blueprint $table) {
        $table->id();
        $table->foreignId('subtask_id')->constrained('subtasks')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->text('action');
        $table->timestamp('logged_at')->useCurrent();
        $table->timestamps();
    });
}

    public function down()
    {
        Schema::dropIfExists('subtask_log');
    }
};
