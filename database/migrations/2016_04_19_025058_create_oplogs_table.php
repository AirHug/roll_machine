<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOplogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oplogs', function (Blueprint $table) {
            $table->increments('id'); //日志id
            $table->integer('code'); //操作代码
            $table->integer('adminid'); //管理员id
            $table->timestamp('time'); //操作时间
            $table->string('param0')->default(0); //操作对象1
            $table->string('param1')->default(0); //操作对象2
            $table->string('param2')->default(0); //操作对象3
            $table->string('param3')->default(0); //操作对象4
            $table->timestamps();
        });
    }
    
// 操作代码和操作对象意义
//    
// 0 开机
// 
// 1 关机
// 
// 2 验证失败 如果用户进行 1：1 验证，“操作对象 1”表示用户 PIN 号码
// 
// 3 报警
// “操作对象 1” 表示具体的原因，可能值为：
// 50: Door Close Detected
// 51: Door Open Detected
// 55: Machine Been Broken
// 53: Out Door Button
// 54: Door Broken Accidentally
// 58: Try Invalid Verification
// 65535: Alarm Cancelled
// 
// 4 进入菜单
// 
// 5 更改设置
// “操作对象 1” 表示被修改的设置项的序号
// “操作对象 2” 表示新修改后的值
// 
// 6 登记指纹
// “操作对象 1” 表示用户的 ID
// “操作对象 2” 表示指纹的序号
// “操作对象 3” 表示指纹模板的长度
// 
// 7 登记密码
// 
// 8 登记 HID 卡
// 
// 9 删除用户 “操作对象 1” 表示用户的 ID
// 
// 10 删除指纹 “操作对象 1” 表示用户的 ID
// 
// 11 删除密码 “操作对象 1” 表示用户的 ID
// 
// 12 删除射频卡 “操作对象 1” 表示用户的 ID
// 
// 13 清除数据
// 
// 14 创建 MF 卡
// 
// 15 登记 MF 卡
// 
// 16 注册 MF 卡
// 
// 17 删除 MF 卡注册
// 
// 18 清除 MF 卡内容
// 
// 19 把登记数据移到卡中
// 
// 20 把卡中的数据复制到机器中
// 
// 21 设置时间
// 
// 22 出厂设置
// 
// 23 删除进出记录
// 
// 24 清除管理员权限
// 
// 25 修改门禁组设置
// 
// 26 修改用户门禁设置
// 
// 27 修改门禁时间段
// 
// 28 修改开锁组合设置
// 
// 29 开锁
// 
// 30 登记新用户
// 
// 31 更改指纹属性
// 
// 32 胁迫报警
// 
// 65 注册用户人脸 “操作对象 1” 表示用户的 ID
// 
// 66 修改用户人脸 “操作对象 1” 表示用户的 ID
// 
// 68 注册用户照片 “操作对象 1” 表示用户的 ID
// 
// 69 修改用户照片 “操作对象 1” 表示用户的 ID
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oplogs');
    }
}
