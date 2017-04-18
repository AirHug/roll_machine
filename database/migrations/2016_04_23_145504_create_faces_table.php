<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faces', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('num');
            $table->integer('usernum');
            $table->boolean('FaceValid');
            $table->bigInteger('FaceSize');
            $table->longText('FaceTMP');
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
        Schema::drop('faces');
    }
}
