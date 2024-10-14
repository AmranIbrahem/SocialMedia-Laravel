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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('current_location')->nullable();
            $table->string('hometown')->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Engaged', 'Widowed', 'Divorced'])->nullable();
            $table->string('education')->nullable();
            $table->json('social_accounts')->nullable();
            $table->integer('followers_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
