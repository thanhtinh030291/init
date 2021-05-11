<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileClaimFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_claim_file', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mobile_claim_id');
            $table->mediumText('note');
            $table->string('filename',255)->nullable();
            $table->string('filetype',50)->nullable();
            $table->integer('filesize')->nullable();
            $table->string('checksum',32)->nullable();
            $table->binary('contents')->nullable();
            $table->string('url')->nullable();
            $table->string('crt_by', 50)->nullable();
            $table->string('upd_by', 50)->nullable();
            $table->timestamps();
        });
        DB::unprepared("
            CREATE TRIGGER `mobile_claim_file__id` 
            BEFORE INSERT ON `mobile_claim_file`
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
        Schema::dropIfExists('mobile_claim_file');
        DB::unprepared('DROP TRIGGER `mobile_claim_file__id`');
    }
}
