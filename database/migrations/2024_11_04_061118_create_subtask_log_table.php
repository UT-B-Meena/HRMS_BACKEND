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
            $table->unsignedBigInteger('subtask_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->text('log');
            $table->timestamps();

            $table->foreign('subtask_id')->references('id')->on('subtasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subtask_log');
    }
};
