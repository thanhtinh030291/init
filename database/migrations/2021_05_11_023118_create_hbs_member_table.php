<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHbsMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hbs_member', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('company',20)->default('pcv');
            $table->mediumText('note')->nullable();
            $table->string('pocy_no',20);
            $table->string('pocy_ref_no',30)->nullable();
            $table->string('mbr_no',20);
            $table->string('memb_ref_no',30)->nullable();
            $table->string('mbr_last_name',255)->nullable();
            $table->string('mbr_mid_name',255)->nullable();
            $table->string('mbr_first_name',255)->nullable();
            $table->date('dob')->nullable();
            $table->char('gender',10)->default('M');
            $table->char('email',255)->nullable();
            $table->char('tel',30)->nullable();
            $table->string('address',500)->nullable();
            $table->string('payment_mode',500)->nullable();
            $table->integer('mepl_oid');
            $table->date('memb_eff_date');
            $table->date('memb_exp_date');
            $table->date('term_date')->nullable();
            $table->date('reinst_date')->nullable();
            $table->date('min_pocy_eff_date')->nullable();
            $table->date('min_memb_eff_date')->nullable();
            $table->string('insured_periods',500)->nullable();
            $table->enum('wait_period', ['Yes', 'No']);
            $table->enum('spec_dis_period', ['Yes', 'No']);
            $table->string('product',10);
            $table->char('plan_desc',255)->nullable();
            $table->text('plan_excls')->nullable();
            $table->text('plan_excls_vi')->nullable();
            $table->text('memb_rstr')->nullable();
            $table->text('memb_rstr_vi')->nullable();
            $table->text('mepl_incls')->nullable();
            $table->text('mepl_incls_vi')->nullable();
            $table->text('mepl_excls')->nullable();
            $table->text('mepl_excls_vi')->nullable();
            $table->string('policy_status',255)->nullable();
            $table->enum('is_renew', ['Yes', 'No']);
            $table->enum('op_ind', ['Yes', 'No']);
            $table->enum('dt_ind', ['Yes', 'No']);
            $table->enum('has_debit_note', ['Yes', 'No']);
            $table->string('ben_schedule', 10000)->nullable();
            $table->text('benefit_en')->nullable();
            $table->text('benefit_vi')->nullable();
            $table->text('children')->nullable();
            $table->tinyInteger('is_policy_holder')->nullable();
            $table->char('crt_by', 50)->nullable();
            $table->char('upd_by', 50)->nullable();
            $table->smallInteger('plan_id');
            $table->tinyInteger('rev_no');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE hbs_member CHANGE plan_id plan_id SMALLINT(4) UNSIGNED ZEROFILL NOT NULL');
        DB::statement('ALTER TABLE hbs_member CHANGE rev_no rev_no TINYINT(2) UNSIGNED ZEROFILL NOT NULL');
        DB::unprepared("
            CREATE TRIGGER `hbs_member__id` 
            BEFORE INSERT ON `hbs_member`
            FOR EACH ROW 
                BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); 
                END IF; 
                SET @last_uuid = NEW.id
                ; END
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hbs_member');
        DB::unprepared('DROP TRIGGER `hbs_member__id`');
    }
}
