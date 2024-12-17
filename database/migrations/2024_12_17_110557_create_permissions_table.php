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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique()->nullable();
            $table->string('action', 255)->nullable();
            $table->string('permission_code', 255)->nullable();
            $table->string('name_group', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('show_menu')->nullable();
            $table->integer('status')->notNull();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
