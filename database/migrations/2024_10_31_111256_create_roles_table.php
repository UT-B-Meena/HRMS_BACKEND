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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('reporting_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Insert static roles
        DB::table('roles')->insert([
            ['name' => 'Team Lead'],
            ['name' => 'Manager'],
            ['name' => 'Employee'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
