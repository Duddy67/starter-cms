<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function(Blueprint $table)
	{
            $table->char('access_level', 10)->nullable()->after('guard_name');
            $table->tinyInteger('role_level')->nullable()->after('access_level');
            $table->char('role_type', 11)->nullable()->after('role_level');
            $table->unsignedBigInteger('checked_out')->nullable()->after('role_type');
            $table->timestamp('checked_out_time')->nullable()->after('checked_out');
            $table->unsignedBigInteger('owned_by')->after('checked_out_time');
            $table->unsignedBigInteger('updated_by')->nullable()->after('owned_by');
	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function(Blueprint $table)
	{
	    $table->dropColumn('access_level');
	    $table->dropColumn('role_level');
	    $table->dropColumn('role_type');
	    $table->dropColumn('checked_out');
	    $table->dropColumn('checked_out_time');
	    $table->dropColumn('owned_by');
	    $table->dropColumn('updated_by');
	});
    }
}
