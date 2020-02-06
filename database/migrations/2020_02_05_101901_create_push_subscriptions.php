<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->charset ='utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->increments('id');
            $table->integer('guest_id')->unsigned()->index();
            $table->string('endpoint', 255)->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
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
        Schema::dropIfExists('push_subscriptions');
    }
}
