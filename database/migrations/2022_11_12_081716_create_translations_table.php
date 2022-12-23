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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translatable_id')->nullable();
            $table->string('translatable_type', 255)->nullable();
            $table->char('locale', 2)->index();
            $table->string('title', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('slug', 100)->nullable();
            $table->text('content')->nullable();
            $table->text('raw_content')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->text('text')->nullable();
            $table->string('url')->nullable();
            $table->string('alt_img', 250)->nullable();
            $table->string('subject')->nullable();
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('extra_fields')->nullable();
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
        Schema::dropIfExists('translations');
    }
};
