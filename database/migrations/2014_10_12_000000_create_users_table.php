<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('num')->comment('设备上的用户id');
            $table->string('Name')->comment('设备上的用户名');
            $table->string('Pri')->comment('文档中未说明属性');
            $table->string('Passwd')->comment('设备上的用户名');
            $table->string('Card')->comment(' ID 卡号码');
            $table->string('Grp')->comment('用户组，测试阶段');
            $table->string('TZ')->comment('文档中未说明属性');
            $table->string('PicFile')->comment('图片文件');
            $table->bigInteger('PicSize')->comment('图片文件大小');
            $table->longText('PicTMP')->comment('文件 BASE64 编码数据');
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
