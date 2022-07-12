<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordering_category_post', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
	    $table->unsignedBigInteger('post_id');
            $table->string('title')->nullable();
	    $table->unsignedBigInteger('post_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordering_category_post');
    }
};
