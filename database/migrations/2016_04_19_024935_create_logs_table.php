<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id'); //id
            $table->string('type'); //起始标记
            // 起始标记     记录内容        对应日志表名
            // USER         用户基本信息    userlogs
            // FP           用户的指纹模板  fplogs
            // OPLOG        管理员操作记录  oplogs
            // USERPIC      用户照片        piclogs
            // FACE         用户人脸模板    facelogs
            $table->integer('logid'); //记录id
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
        Schema::drop('logs');
    }
}
