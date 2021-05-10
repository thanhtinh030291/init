<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileClaimStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (!Schema::hasTable('mobile_claim_status')) {
            Schema::create('mobile_claim_status', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 20)->nullable();
                $table->string('name_vi', 20);
                $table->tinyInteger('code');
                $table->char('crt_by', 50)->nullable();
                $table->char('upd_by', 50)->nullable();
                $table->timestamps();
            });
            DB::unprepared("
                CREATE TRIGGER `mobile_claim_status__id` 
                BEFORE INSERT ON `mobile_claim_status`
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
        Schema::dropIfExists('mobile_claim_status');
    }
}
