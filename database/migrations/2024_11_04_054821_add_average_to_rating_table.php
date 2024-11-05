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
    Schema::table('ratings', function (Blueprint $table) {
        $table->decimal('average', 5, 2)->nullable()->after('rating');
    });
}

public function down()
{
    Schema::table('rating', function (Blueprint $table) {
        $table->dropColumn('average');
    });
}

};
