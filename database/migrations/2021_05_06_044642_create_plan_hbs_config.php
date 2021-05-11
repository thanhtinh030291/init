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
            $table->increments('id');
            $table->smallInteger('plan_id');
            $table->tinyInteger('rev_no');
            $table->string('plan_desc')->nullable();
            $table->string('filename_vi')->nullable();
            $table->string('filename_en')->nullable();
            $table->integer('is_benefit_ready')->default(1);
            $table->string('company')->default('pcv');
            
            $table->integer('is_deleted')->default('0');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE plan_hbs_config CHANGE plan_id plan_id SMALLINT(4) UNSIGNED ZEROFILL NOT NULL');
        DB::statement('ALTER TABLE plan_hbs_config CHANGE rev_no rev_no TINYINT(2) UNSIGNED ZEROFILL NOT NULL');
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
