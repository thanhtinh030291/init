<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToHbsMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('hbs_member', function (Blueprint $table) {
        //     $table->integer('plan_id')->nullable();
        //     $table->integer('rev_no')->nullable();
        // });
        
        // Schema::table('hbs_member2', function (Blueprint $table) {
        //     $table->integer('plan_id')->nullable();
        //     $table->integer('rev_no')->nullable();
        // });
        // DB::statement('ALTER TABLE hbs_member CHANGE plan_id plan_id INT(4) UNSIGNED ZEROFILL NOT NULL');
        // DB::statement('ALTER TABLE hbs_member2 CHANGE plan_id plan_id INT(4) UNSIGNED ZEROFILL NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('hbs_member', function (Blueprint $table) {
        //     //
        //     $table->dropColumn('plan_id');
        //     $table->dropColumn('rev_no');
        // });
        // Schema::table('hbs_member2', function (Blueprint $table) {
        //     //
        //     $table->dropColumn('plan_id');
        //     $table->dropColumn('rev_no');
        // });
    }
}
