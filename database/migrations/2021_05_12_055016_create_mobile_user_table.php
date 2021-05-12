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
            $table->string('apple_id',255)->nullable();
            $table->string('card_id',255)->nullable();
            $table->string('front_card_url',255)->nullable();
            $table->string('back_card_url',255)->nullable();
            $table->char('resrouce', 5)->nullable();
            $table->timestamp('first_login')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->char('company',20)->default('pcv');
            $table->char('crt_by', 50)->nullable();
            $table->char('upd_by', 50)->nullable();
            
            $table->timestamps();
        });
        DB::unprepared("
            CREATE TRIGGER `mobile_user__id` 
            BEFORE INSERT ON `mobile_user`
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
        Schema::dropIfExists('mobile_user');
        DB::unprepared('DROP TRIGGER `mobile_user_id`');
    }
}
