<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileClaimTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mobile_claim')) {
            Schema::create('mobile_claim', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->integer('mantis_id');
                $table->uuid('mobile_user_id');
                $table->string('pay_type', 30);
                $table->integer('pres_amt');
                $table->uuid('mobile_user_bank_account_id')->nullable();
                $table->uuid('mobile_claim_status_id');
                $table->string('reason', 30);
                $table->dateTime('symtom_time')->nullable();
                $table->dateTime('occur_time')->nullable();
                $table->string('body_part', 300)->nullable();
                $table->text('incident_detail')->nullable();
                $table->text('note');
                $table->string('dependent_memb_no', 30)->nullable();
                $table->string('fullname', 50)->nullable();
                $table->longText('extra')->nullable();
                $table->string('crt_by', 50)->nullable();
                $table->string('upd_by', 50)->nullable();
                $table->timestamps();
            });
            DB::unprepared("
                CREATE TRIGGER `mobile_claim__id` 
                BEFORE INSERT ON `mobile_claim`
                FOR EACH ROW 
                    BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); 
                    END IF; 
                    SET @last_uuid = NEW.id
                    ; END
                ");
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_claim');
        DB::unprepared('DROP TRIGGER `mobile_claim__id`');
    }
}
