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
            $table->unsignedBigInteger('layout_itemable_id');
            $table->unsignedSmallInteger('id_nb');
            $table->string('layout_itemable_type', 255);
            $table->string('type', 50);
            $table->text('value')->nullable();
            $table->unsignedTinyInteger('order');
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
