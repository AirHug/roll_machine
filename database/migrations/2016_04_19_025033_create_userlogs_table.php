<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userlogs', function (Blueprint $table) {
            $table->increments('id'); //日志id
            $table->integer('PIN'); //考勤用户id
            $table->string('Name', 10); //姓名
            $table->string('Passwd', 10); //密码
            $table->string('Card'); //ID 卡号码
            // ID 卡号码格式：使用“[”,“]”括号时，其内容是 HEX 格式表示的完整卡号码数据；否则同刷卡显示的号码一致。
            $table->integer('Grp'); //文档中未说明
            $table->string('TZ', 100); //文档中未说明
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
        Schema::drop('userlogs');
    }
}
