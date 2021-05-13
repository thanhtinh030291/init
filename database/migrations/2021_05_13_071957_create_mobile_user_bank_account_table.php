<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUserBankAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_user_bank_account', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mobile_user_id');
            $table->string('bank_name',255)->nullable();
            $table->string('bank_address',255)->nullable();
            $table->string('bank_acc_no',50)->nullable();
            $table->string('bank_acc_name',255)->nullable();
            $table->char('crt_by', 50)->nullable();
            $table->char('upd_by', 50)->nullable();
            $table->timestamps();
        });
        DB::unprepared("
            CREATE TRIGGER `mobile_user_bank_account__id` 
            BEFORE INSERT ON `mobile_user_bank_account`
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
        DB::unprepared('DROP TRIGGER `mobile_user_bank_account__id`');
        Schema::dropIfExists('mobile_user_bank_account');
    }
}
