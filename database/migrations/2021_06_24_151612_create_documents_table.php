<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
	    $table->string('disk_name')->nullable();
	    $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size')->nullable();
	    $table->string('content_type')->nullable();
	    $table->string('field', 30)->nullable();
            $table->unsignedInteger('documentable_id')->nullable();
            $table->string('documentable_type')->nullable();
            $table->boolean('is_public')->nullable();
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
        Schema::dropIfExists('documents');
    }
}
