<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class PostMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:message {timeFlag=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wechat message post task';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();
        $url = "";
        $post_data = "";
        switch ($this->argument("timeFlag")) {
            case "1"://早上上班
                $post_data = array(
                    'Datetime' => "08:30",
                    'TimeStamp' => time(Carbon::now()),
                    'Type' => '1',
                );
                $url = "http://localhost:8001/remind";
                break;
            case "2"://中午上班
                if ($now->month >= 5 &&  $now->month < 10) {
                    $post_data = array(
                        'Datetime' => "13:30",
                        'TimeStamp' => time(Carbon::now()),
                        'Type' => '2',
                    );
                } else {
                    $post_data = array(
                        'Datetime' => "13:00",
                        'TimeStamp' => time(Carbon::now()),
                        'Type' => '2',
                    );
                }
                $url = "http://localhost:8001/remind";
                break;
            case "3"://下班后10分钟
                if ($now->month >= 5 && $now->month < 10) {
                    $post_data = array(
                        'Datetime' => "17:00",
                        'TimeStamp' => time(Carbon::now()),
                        'Type' => '3',
                    );
                } else {
                    $post_data = array(
                        'Datetime' => "16:30",
                        'TimeStamp' => time(Carbon::now()),
                        'Type' => '3',
                    );
                }
                $url = "http://localhost:8001/remind";
                break;
            case "4"://下班前10分钟
                if ($now->month >= 5 && $now->month < 10) {
                    $post_data = array(
                        'Datetime' => Carbon::now()->toDateString(),
                        'TimeStamp' => time(Carbon::now()),
                    );
                } else {
                    $post_data = array(
                        'Datetime' => Carbon::now()->toDateString(),
                        'TimeStamp' => time(Carbon::now()),
                    );
                }
                $url = "http://localhost:8001/remind_";
                break;
            case "5"://矫正
                if ($now->month >= 5 &&  $now->month < 10) {
                    $post_data = array(
                        'Datetime' => Carbon::now()->toDateString(),
                        'TimeStamp' => time(Carbon::now()),
                    );
                } else {
                    $post_data = array(
                        'Datetime' => Carbon::now()->toDateString(),
                        'TimeStamp' => time(Carbon::now()),
                    );
                }
                $url = "http://localhost:8001/correctleave";
                break;
            default:
                break;
        }

        if ($this->isHoliday(date("Ymd")) == 0) {
            $this->send_post($url, $post_data);
            $this->info("success! " . Carbon::now()->toDateTimeString());
            \Log::info("success! " . Carbon::now()->toDateTimeString());
        } else {
            $this->info("fail! " . Carbon::now()->toDateTimeString() . Carbon::now()->toDateString());
            \Log::info("fail! " . Carbon::now()->toDateTimeString());
        }

    }


    private function send_post($url, $post_data)
    {

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    private function isHoliday($date)
    {
        $ch = curl_init();
        $url = 'http://apis.baidu.com/xiaogg/holiday/holiday?d=' . $date;
        $header = array(
            'apikey: 8d1f17226daf80f758e369923c2114e0',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);

        return strlen(json_decode($res)) > 0 ? json_decode($res) : 0;
    }
}
