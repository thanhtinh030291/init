<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToHbsMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hbs_member', function (Blueprint $table) {
            $table->string('prod_type', 10)->nullable();
            $table->string('is_vip', 1)->nullable();
            $table->string('mobile_level', 2)->nullable();
            $table->unsignedInteger('plan_oid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hbs_member', function (Blueprint $table) {
            $table->dropColumn('prod_type');
            $table->dropColumn('is_vip');
            $table->dropColumn('mobile_level');
            $table->dropColumn('plan_oid');
        });
    }
}
