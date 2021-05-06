<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanHbsConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_hbs_config', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');
            $table->string('plan_desc')->nullable();
            $table->integer('rev_no')->nullable();
            $table->string('url')->nullable();
            $table->integer('ready')->default(1);
            
            $table->string('company')->default('pcv');
            $table->integer('is_deleted')->default('0');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE plan_hbs_config CHANGE plan_id plan_id INT(4) UNSIGNED ZEROFILL NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_hbs_config');
    }
}
