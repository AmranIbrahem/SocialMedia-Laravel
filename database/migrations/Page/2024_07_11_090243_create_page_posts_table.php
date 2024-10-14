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
        Schema::create('page_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->longText('Text')->nullable();
            $table->json('files')->nullable();
            $table->integer('count_of_Comment')->default(0);
            $table->integer('count_of_Interaction')->default(0);
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_posts');
    }
};
