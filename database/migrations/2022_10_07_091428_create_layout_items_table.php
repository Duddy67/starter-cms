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
        Schema::create('layout_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layout_itemable_id')->nullable();
            $table->string('layout_itemable_type', 255)->nullable();
            $table->unsignedSmallInteger('id_nb');
            $table->string('type', 50);
            $table->json('data')->nullable();
            $table->unsignedTinyInteger('order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('layout_items');
    }
};
