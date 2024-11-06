<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubtaskLogTable extends Migration
{
    public function up()
    {
        Schema::table('subtask_log', function (Blueprint $table) {
            $table->dropColumn('action');
            $table->dropColumn('logged_at');
            $table->string('status');
            $table->text('log');
        });
    }

    public function down()
    {
        Schema::table('subtask_log', function (Blueprint $table) {
            $table->text('action');
            $table->timestamp('logged_at')->useCurrent();
            $table->dropColumn('status');
            $table->dropColumn('log');
        });
    }
}

