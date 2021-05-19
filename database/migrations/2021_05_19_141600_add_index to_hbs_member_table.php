<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToHbsMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hbs_member', function (Blueprint $table) {
            $table->index('company');
            $table->index('pocy_no');
            $table->index('pocy_ref_no');
            $table->index('mbr_no');
            $table->index('memb_ref_no');
            $table->index('email');
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
            $table->dropIndex('hbs_member_company_index');
            $table->dropIndex('hbs_member_pocy_no_index');
            $table->dropIndex('hbs_member_pocy_ref_no_index');
            $table->dropIndex('hbs_member_mbr_no_index');
            $table->dropIndex('hbs_member_memb_ref_no_index');
            $table->dropIndex('hbs_member_email_index');
        });
    }
}
