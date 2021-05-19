<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanOidToPlanHbsConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_hbs_config', function (Blueprint $table) {
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
        Schema::table('plan_hbs_config', function (Blueprint $table) {
            $table->dropColumn('plan_oid');
        });
    }
}
