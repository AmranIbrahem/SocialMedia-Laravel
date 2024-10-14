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
        Schema::create('page_post_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_post_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->integer('count_of_Reply')->default(0);
            $table->integer('count_of_Interaction')->default(0);
            $table->timestamps();

            $table->foreign('page_post_id')->references('id')->on('page_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_post_comments');
    }
};
