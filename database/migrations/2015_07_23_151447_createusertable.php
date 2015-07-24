<?php

use App\Twitter\TwitterAccountStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Createusertable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->string('profile_pic')->nullable();
            $table->integer('status')->default(TwitterAccountStatus::NOT_RETRIEVED);
            $table->timestamp('date_registered')->nullable();
            $table->timestamp('last_checked')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('twitter_users');
    }
}
