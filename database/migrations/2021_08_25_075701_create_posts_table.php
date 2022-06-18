<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('slug', 100);
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->char('status', 12);
            $table->unsignedBigInteger('main_cat_id')->nullable();
            $table->char('access_level', 10);
            $table->string('alt_img', 250)->nullable();
            $table->json('extra_fields')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('settings');
            $table->unsignedBigInteger('checked_out')->nullable();
            $table->timestamp('checked_out_time')->nullable();
            $table->unsignedBigInteger('owned_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
