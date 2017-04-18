<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->increments('id'); //记录id
            $table->integer('userId')->comment('考勤用户id'); //考勤用户id
            $table->timestamp('time')->comment('考勤时间'); //考勤时间
            $table->string('status', 1)->comment('考勤状态'); //考勤状态
            // 0—— 上班签到
            // 1—— 下班签退
            // 2—— 外出
            // 3—— 外出返回
            // 4—— 加班签到
            // 5—— 加班签退
            // 8—— 就餐开始
            // 9—— 就餐结束
            $table->string('verify', 1)->comment('验证方式'); //验证方式
            // 0—— 密码
            // 1—— 指纹
            // 2—— 卡
            // 9—— 其它
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
        Schema::drop('attendances');
    }
}
