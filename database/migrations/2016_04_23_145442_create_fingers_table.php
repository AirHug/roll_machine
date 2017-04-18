<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFingersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fingers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('num');
            $table->integer('usernum');
            $table->boolean('FingerValid');
            $table->longText('FingerTMP');
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
        Schema::drop('fingers');
    }
}
