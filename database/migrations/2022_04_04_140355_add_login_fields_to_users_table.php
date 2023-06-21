<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_logged_in_at')->after('updated_by')->nullable();
            $table->string('last_logged_in_ip')->after('last_logged_in_at')->nullable();
            $table->timestamp('last_seen_at')->after('last_logged_in_ip')->nullable();
            $table->timestamp('last_access_at')->after('last_seen_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_logged_in_at');
            $table->dropColumn('last_logged_in_ip');
            $table->dropColumn('last_seen_at');
            $table->dropColumn('last_access_at');
        });
    }
}
