<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facelogs', function (Blueprint $table) {
            $table->increments('id'); //日志id
            $table->integer('PIN'); //考勤用户id
            $table->integer('FID'); //用户的指纹序号
            $table->integer('SIZE'); //TMP 字段数据长度
            $table->integer('Valid'); //当前指纹是否可以用
            $table->text('TMP'); //BASE64 指纹编码的模板
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
        Schema::drop('facelogs');
    }
}
