<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->increments('id'); //id
            $table->string('DeviceSN', 13); //对应设备号
            $table->integer('ErrorDelay')->default(60); //联网失败后重新联接服务器的间隔时间（秒）
            $table->integer('Delay')->default(30); //正常联网时联接服务器的间隔时间（秒）
            $table->string('TransTimes', 60)->default('00:00;12:00;');
            //定时检查并传送新数据时间（时:分，24小时格式），多个时间用分号分开，最多支持10个时间
            $table->integer('TransInterval')->default(1); //检查并传送新数据间隔时间（分钟）
            $table->string('TransFlag', 10)->default('1111000000');
            //向服务器传送哪些新数据标记, 请返回“1111000000”
            // TransFlag各个位代表含义(共10位)
            // 字符串标识    字符数组    标识
            // AttLog        0          考勤记录
            // OpLog         1          操作日志
            // AttPhoto      2          考勤照片
            // EnrollFP      3          登记新指纹
            // EnrollUser    4          登记新用户
            // FPImag        5          指纹图片
            // ChgUser       6          修改用户信息
            // ChgFP         7          修改指纹
            // FACE          8          人脸登记
            // UserPic       9          用户照片
            $table->integer('Realtime')->default('1');
            //是否实时传送新记录。 为1表示有新数据就传送到服务器，为0表示按照TransTimes 和 TransInterval 规定的时间传送
            $table->integer('Encrypt')->default('0'); //是否加密传送数据（加密传送使用中控专门的加密算法） ，请返回0
            $table->string('ServerVer', 20)->default('3.4.1 2010-06-07'); //服务器版本号及时间 例:3.4.1 2010-06-07
            $table->string('ATTLOGStamp', 11)->default(time(Carbon::now())); //考勤记录时间戳
            $table->string('OPERLOGStamp', 11)->default(time(Carbon::now())); //操作日志时间戳
            $table->string('ATTPHOTOStamp', 11)->default(time(Carbon::now())); //考勤照片时间戳
            $table->string('SMSStamp', 11)->default(time(Carbon::now())); //短消息时间戳
            $table->string('USER_SMSStamp', 11)->default(time(Carbon::now())); //个人短消息用户列表时间戳
            $table->string('USERINFOStamp', 11)->default(time(Carbon::now())); //用户信息时间戳
            $table->string('FINGERTMPStamp', 11)->default(time(Carbon::now())); //指纹模板时间戳
            $table->string('FACEStamp', 11)->default(time(Carbon::now())); //人脸模板时间戳
            $table->string('USERPICStamp', 11)->default(time(Carbon::now())); //用户照片时间戳
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
        Schema::drop('configs');
    }
}
