<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
	{
            $table->unsignedBigInteger('checked_out')->nullable()->after('remember_token');
            $table->timestamp('checked_out_time')->nullable()->after('checked_out');
            $table->unsignedBigInteger('updated_by')->nullable()->after('checked_out_time');
	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
	{
	    $table->dropColumn('checked_out');
	    $table->dropColumn('checked_out_time');
	    $table->dropColumn('updated_by');
	});
    }
}
