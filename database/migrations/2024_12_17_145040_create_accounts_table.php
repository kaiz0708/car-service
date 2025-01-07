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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->integer('kind')->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('nickname', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('avatar_path', 255)->nullable();
            $table->dateTime('last_login')->nullable();
            $table->string('reset_pwd_code', 255)->nullable();
            $table->dateTime('reset_pwd_time')->nullable();
            $table->integer('attempt_forget_pwd')->nullable();
            $table->integer('attempt_login')->nullable();
            $table->boolean('is_super_admin')->nullable();
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->integer('status')->nullable(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
