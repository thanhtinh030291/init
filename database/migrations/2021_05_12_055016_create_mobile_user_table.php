<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pocy_no',20);
            $table->string('mbr_no',20);
            $table->text('password')->nullable();
            $table->string('fullname',255)->nullable();
            $table->string('address',255)->nullable();
            $table->longText('photo')->nullable();
            $table->string('tel',30)->nullable();
            $table->string('email',255)->nullable();
            $table->string('language',10)->nullable();
            $table->tinyInteger('enabled')->default(1);
            $table->tinyInteger('is_policy_holder')->nullable();
            $table->tinyInteger('member_type')->default(1);
            $table->string('fb_id',255)->nullable();
            $table->string('gg_id',255)->nullable();
            $table->char('company',20)->default('pcv');
            $table->char('crt_by', 50)->nullable();
            $table->char('upd_by', 50)->nullable();
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
        Schema::dropIfExists('mobile_user');
    }
}
