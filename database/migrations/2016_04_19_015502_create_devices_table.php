<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('SN', 13)->comment('设备号'); // 设备号
            $table->string('Stamp', 11)->comment('设备考勤时间戳'); // 设备考勤时间戳
            $table->string('OpStamp', 11)->comment('设备操作日志时间戳'); // 设备操作日志时间戳
            $table->string('version', 30)->comment('固件版本号'); // 固件版本号
            $table->ipAddress('ip')->comment('考勤机 IP 地址'); // 考勤机 IP 地址
            $table->integer('userCounts')->comment('登记用户'); // 登记用户
            $table->integer('FPcounts')->comment('数登记指纹数'); // 数登记指纹数
            $table->integer('attCounts')->comment('数登记签到数'); // 数登记指纹数
            $table->string('FPAlgoVersion', 10)->comment('指纹算法版本'); // 指纹算法版本
            $table->string('FACEAlgoVersion', 10)->comment('人脸算法版本'); // 人脸算法版本
            $table->integer('FACEModelCounts')->comment('注册人脸时所需模板个数'); // 注册人脸时所需模板个数
            $table->integer('FACECounts')->comment('设备已登记人脸数'); // 设备已登记人脸数
            $table->string('SupportFlag', 3)->comment('设备支持功能标识'); // 设备支持功能标识
            // 序号 含义                           默认值
            // 1    FP,是否支持指纹下载            1，支持
            // 2    FACE，是否支持人脸下载         0，不支持
            // 3    USERPIC，是否支持用户照片下载  0，不支持
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
        Schema::drop('devices');
    }
}
