<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToMobileClaimTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mobile_claim', function (Blueprint $table) {
            //
            $table->string('pocy_no', 20)->nullable();
            $table->string('mbr_no', 20)->nullable();
            $table->string('company', 20)->default('pcv');
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_address', 255)->nullable();
            $table->string('bank_acc_no', 50)->nullable();
            $table->string('bank_acc_name', 255)->nullable();
            $table->tinyInteger('is_read')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mobile_claim', function (Blueprint $table) {
            //
            $table->dropColumn('pocy_no');
            $table->dropColumn('mbr_no');
            $table->dropColumn('company');
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_address');
            $table->dropColumn('bank_acc_no');
            $table->dropColumn('bank_acc_name');
            $table->dropColumn('is_read');
        });
    }
}
