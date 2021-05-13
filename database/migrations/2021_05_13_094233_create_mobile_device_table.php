<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_device', function (Blueprint $table) {
            $table->id();
            $table->uuid('mobile_user_id');
            $table->char('crt_by', 50)->nullable();
            $table->char('upd_by', 50)->nullable();
            $table->string('device_type',10)->nullable();
            $table->string('device_token',255)->nullable();
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
        Schema::dropIfExists('mobile_device');
    }
}
