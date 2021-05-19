<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider', function (Blueprint $table) {
            $table->id();
            $table->string('code','10')->nullable();
            $table->string('name',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('phone',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('email',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('website',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('address',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('city',50)->collation('utf8_unicode_ci')->nullable();
            $table->string('district',50)->collation('utf8_unicode_ci')->nullable();
            $table->string('country',50)->collation('utf8_unicode_ci')->nullable();
            $table->string('latitude',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('longitude',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('day_from_1',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('day_to_1',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('day_from_2',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('day_to_2',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('hour_open_1',100)->collation('utf8_unicode_ci')->nullable();
            $table->string('hour_close_1',100)->collation('utf8_unicode_ci')->nullable();
            $table->string('hour_open_2',100)->collation('utf8_unicode_ci')->nullable();
            $table->string('hour_close_2',100)->collation('utf8_unicode_ci')->nullable();
            $table->string('emergency_services',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('emergency_phone',50)->collation('utf8_unicode_ci')->nullable();
            $table->string('direct_billing',50)->collation('utf8_unicode_ci')->nullable();
            $table->string('medical_type',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('medical_services',255)->collation('utf8_unicode_ci')->nullable();
            $table->string('price_from',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('price_to',20)->collation('utf8_unicode_ci')->nullable();
            $table->string('lang',20)->collation('utf8_unicode_ci')->nullable();
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
        Schema::dropIfExists('provider');
    }
}
