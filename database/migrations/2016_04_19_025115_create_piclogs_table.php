<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePiclogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piclogs', function (Blueprint $table) {
            $table->increments('id'); //日志id
            $table->integer('PIN'); //考勤用户id
            $table->string('FileName', 50); //照片文件名
            $table->integer('Size'); //照片文件大小
            $table->text('Content'); //照片文件 base64 的编码内容
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
        Schema::drop('piclogs');
    }
}
