<?php

namespace App\Console\Commands;

use App\Collection;
use App\User;
use Carbon\Carbon;
use App\Attendance;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:status {date=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update status column of attendances table';

    /**
     * Test constructor.
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
        //抽取所有参与签到用户
        $userIds = User::orderBy("num", "asc")
            ->where("gid", "!=", 1)
            ->get(['num'])
            ->toArray();

        //调整数组
        $userIds = array_flatten($userIds);

        if ($this->argument("date") != 0) {
            for ($day = 3; $day <= $this->argument("date"); ++$day) {
                $this->info($day);
                $date = Carbon::now();
                if ($day > 0) $date->subDay($day);

                foreach ($userIds as $userId) {
                    //抽取打卡记录
                    $attendances = Attendance::where("time", ">", $date->startOfDay()->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
                        ->where("time", "<", $date->endOfDay()->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
                        ->where("is_avail", "!=", "2")
                        ->where("userId", $userId)
                        ->orderBy("time", "asc")
                        ->get();


                    //记录数大于1去除2分钟冗余签到卡并设无效打卡
                    if (count($attendances) > 1) $attendances = $this->dropRecords($attendances);

                    //按照剩余记录判断到岗时间

                    $timeA = new Carbon($date->toDateString("yyyy-MM-dd") . " 8:30:00");
                    $timeB = new Carbon($date->toDateString("yyyy-MM-dd") . " 11:30:00");
                    if ($date->month >= 5 && $date->month < 10) {
                        $timeC = new Carbon($date->toDateString("yyyy-MM-dd") . " 13:30:59");
                        $timeD = new Carbon($date->toDateString("yyyy-MM-dd") . " 17:00:00");
                    } else {
                        $timeC = new Carbon($date->toDateString("yyyy-MM-dd") . " 13:00:59");
                        $timeD = new Carbon($date->toDateString("yyyy-MM-dd") . " 16:30:00");
                    }

                    //这句添加中午允许迟到的分钟数
                    $timeC->addMinutes((int)env('HX_MINUTES'));

                    switch (count($attendances)) {
                        case 0://昨日无打卡记录
                            $this->setCollection($userId, $date, '0');
                            break;
                        case 1://昨日一条有效打卡记录
                            $this->setCollection($userId, $date, '0');
                            break;
                        case 2://昨日两条有效打卡记录
                            $A = 0;
                            $B = 0;
                            $C = 0;
                            $D = 0;
                            $E = 0;
                            foreach ($attendances as $attendance) {
                                $carbon = new Carbon($attendance->time);
                                if ($carbon->lte($timeA)) {//A区记录
                                    $A = 1;
                                } else if ($carbon->lte($timeB)) {//B区记录
                                    $B = 1;
                                } else if ($carbon->lte($timeC)) {//C区记录
                                    $C = 1;
                                } else if ($carbon->lte($timeD)) {//D区记录
                                    $D = 1;
                                } else {//E区记录
                                    $E = 1;
                                }
                            }
                            $sum = array_sum([$A, $B, $C, $D, $E]);
                            if ($sum == 1) {//在一个区间
                                if ($A == 1 || $C == 1 || $E == 1) {//在A,C,E区间
                                    //设置无效打卡
                                    $attendances[0]->status_ = 0;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 0;
                                    $attendances[1]->save();
                                    //设置0到岗
                                    $this->setCollection($userId, $date, '0');
                                } else if ($B == 1) {//在B区间
                                    //设置上下班打卡
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 2;
                                    $attendances[1]->save();
                                    //获取分钟数
                                    $first = new Carbon($attendances[0]->time);
                                    $second = new Carbon($attendances[1]->time);
                                    $minutes = $first->diffInMinutes($timeA) + $second->diffInMinutes($timeB);
                                    //设置到岗天数
                                    if ($minutes >= 90) {
                                        $this->setCollection($userId, $date, '0', $first->diffInMinutes($timeA), $second->diffInMinutes($timeB));
                                    } else {
                                        $this->setCollection($userId, $date, '0.5', $first->diffInMinutes($timeA), $second->diffInMinutes($timeB));
                                    }
                                } else if ($D == 1) {//在D区间
                                    //设置上下班打卡
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 2;
                                    $attendances[1]->save();
                                    //获取分钟数
                                    $first = new Carbon($attendances[0]->time);
                                    $second = new Carbon($attendances[1]->time);
                                    $minutes = $first->diffInMinutes($timeC) + $second->diffInMinutes($timeD);
                                    //设置到岗天数
                                    if ($minutes >= 90) {
                                        $this->setCollection($userId, $date, '0', $first->diffInMinutes($timeC), $second->diffInMinutes($timeD));
                                    } else {
                                        $this->setCollection($userId, $date, '0.5', $first->diffInMinutes($timeC), $second->diffInMinutes($timeD));
                                    }
                                }
                            } else {//不在一个区间
                                if ($A == 1) {//第一次在A
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    if ($B == 1) {//A+B
                                        //设置下班打卡
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeC);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', 0, $minutes);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', 0, $minutes);
                                        }
                                    } else if ($C == 1) {//A+C
                                        $attendances[1]->status_ = 1;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeC) > (int)env('HX_MINUTES') ? 0 : (int)env('HX_MINUTES') - $first->diffInMinutes($timeC);
                                        //设置到岗天数
                                        $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                    } else if ($D == 1) {//A+D
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeD);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', 0, $minutes);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', 0, $minutes);
                                        }
                                    } else if ($E == 1) {//A+E
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //设置到岗天数
                                        $this->setCollection($userId, $date, '0.5');
                                    }
                                } else if ($B == 1) {
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    if ($C == 1) {//B+C
                                        $attendances[1]->status_ = 1;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $minutes = $first->diffInMinutes($timeA);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $minutes, 0);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                        }
                                    } else if ($D == 1) {//B+D
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $second = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeA) + $second->diffInMinutes($timeD);
                                        //设置到岗天数
                                        if ($minutes >= 180) {
                                            $this->setCollection($userId, $date, '0', 0, $minutes);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', 0, $minutes);
                                        }
                                    } else if ($E == 1) {//B+E
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $minutes = $first->diffInMinutes($timeA);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $minutes, 0);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                        }
                                    }
                                } else if ($C == 1) {
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 2;
                                    $attendances[1]->save();
                                    if ($D == 1) {//C+D
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $second = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeA) + $second->diffInMinutes($timeD);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $first->diffInMinutes($timeA), $second->diffInMinutes($timeD));
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $first->diffInMinutes($timeA), $second->diffInMinutes($timeD));
                                        }
                                    } else if ($E == 1) {//C+E
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $minutes = $first->diffInMinutes($timeA);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $minutes, 0);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                        }
                                    }
                                } else if ($D == 1) {
                                    $attendances[0]->status_ = 0;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 0;
                                    $attendances[1]->save();
                                    $this->setCollection($userId, $date, '0');
                                }
                            }
                            break;
                        default://昨日两条以上有效打卡记录
                            //获取C区的记录用于判断之后的0.5到岗天数
                            $filter = $attendances->filter(function ($value, $key) use ($timeC, $timeB) {
                                $time = new Carbon($value->time);
                                return $time->between($timeC, $timeB);
                            });

                            //修改其他记录为无效记录
                            if (count($filter) > 0) {
                                foreach ($attendances as $attendance) {
                                    $attendance->status_ = 0;
                                    $attendance->save();
                                }
                                $filter->first()->status_ = 1;
                                $filter->first()->save();
                            }

                            //获取新收集器，取记录第一条和最后一条
                            $multiplied = new \Illuminate\Database\Eloquent\Collection();
                            $multiplied->add($attendances->first());
                            $multiplied->add($attendances->last());
                            $attendances = $multiplied;

                            //走情况2的算法
                            //判断2条记录的位置
                            $A = 0;
                            $B = 0;
                            $C = 0;
                            $D = 0;
                            $E = 0;
                            foreach ($attendances as $attendance) {
                                $carbon = new Carbon($attendance->time);
                                if ($carbon->lte($timeA)) {//A区记录
                                    $A = 1;
                                } else if ($carbon->lte($timeB)) {//B区记录
                                    $B = 1;
                                } else if ($carbon->lte($timeC)) {//C区记录
                                    $C = 1;
                                } else if ($carbon->lte($timeD)) {//D区记录
                                    $D = 1;
                                } else {//E区记录
                                    $E = 1;
                                }
                            }
                            $sum = array_sum([$A, $B, $C, $D, $E]);
                            if ($sum == 1) {//在一个区间
                                if ($A == 1 || $C == 1 || $E == 1) {//在A,C,E区间
                                    //设置无效打卡
                                    $attendances[0]->status_ = 0;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 0;
                                    $attendances[1]->save();
                                    //设置0到岗
                                    $this->setCollection($userId, $date, '0');
                                } else if ($B == 1) {//在B区间
                                    //设置上下班打卡
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 2;
                                    $attendances[1]->save();
                                    //获取分钟数
                                    $first = new Carbon($attendances[0]->time);
                                    $second = new Carbon($attendances[1]->time);
                                    $minutes = $first->diffInMinutes($timeA) + $second->diffInMinutes($timeB);
                                    //设置到岗天数
                                    if ($minutes >= 90) {
                                        $this->setCollection($userId, $date, '0', $first->diffInMinutes($timeA), $second->diffInMinutes($timeB));
                                    } else {
                                        $this->setCollection($userId, $date, '0.5', $first->diffInMinutes($timeA), $second->diffInMinutes($timeB));
                                    }
                                } else if ($D == 1) {//在D区间
                                    //设置上下班打卡
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 2;
                                    $attendances[1]->save();
                                    //获取分钟数
                                    $first = new Carbon($attendances[0]->time);
                                    $second = new Carbon($attendances[1]->time);
                                    $minutes = $first->diffInMinutes($timeC) + $second->diffInMinutes($timeD);
                                    //设置到岗天数
                                    if ($minutes >= 90) {
                                        $this->setCollection($userId, $date, '0', $first->diffInMinutes($timeC), $second->diffInMinutes($timeD));
                                    } else {
                                        $this->setCollection($userId, $date, '0.5', $first->diffInMinutes($timeC), $second->diffInMinutes($timeD));
                                    }
                                }
                            } else {//不在一个区间
                                if ($A == 1) {//第一次在A
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    if ($B == 1) {//A+B
                                        //设置下班打卡
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeC);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', 0, $minutes);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', 0, $minutes);
                                        }
                                    } else if ($C == 1) {//A+C
                                        $attendances[1]->status_ = 1;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeC) > (int)env('HX_MINUTES') ? 0 : (int)env('HX_MINUTES') - $first->diffInMinutes($timeC);
                                        //设置到岗天数
                                        $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                    } else if ($D == 1) {//A+D
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeD);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, count($filter) > 0 ? '0.5' : '0', 0, $minutes);
                                        } else {
                                            $this->setCollection($userId, $date, count($filter) > 0 ? '1' : '0.5', 0, $minutes);
                                        }
                                    } else if ($E == 1) {//A+E
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //设置到岗天数
                                        $this->setCollection($userId, $date, count($filter) > 0 ? '1' : '0.5');
                                    }
                                } else if ($B == 1) {
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    if ($C == 1) {//B+C
                                        $attendances[1]->status_ = 1;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $minutes = $first->diffInMinutes($timeA);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $minutes, 0);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                        }
                                    } else if ($D == 1) {//B+D
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $second = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeA) + $second->diffInMinutes($timeD);
                                        //设置到岗天数
                                        if ($minutes >= 180) {
                                            $this->setCollection($userId, $date, count($filter) > 0 ? '0.5' : '0', 0, $minutes);
                                        } else {
                                            $this->setCollection($userId, $date, count($filter) > 0 ? '1' : '0.5', 0, $minutes);
                                        }
                                    } else if ($E == 1) {//B+E
                                        $attendances[1]->status_ = 2;
                                        $attendances[1]->save();
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $minutes = $first->diffInMinutes($timeA);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, count($filter) > 0 ? '0.5' : '0', $minutes, 0);
                                        } else {
                                            $this->setCollection($userId, $date, count($filter) > 0 ? '1' : '0.5', $minutes, 0);
                                        }
                                    }
                                } else if ($C == 1) {
                                    $attendances[0]->status_ = 1;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 2;
                                    $attendances[1]->save();
                                    if ($D == 1) {//C+D
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $second = new Carbon($attendances[1]->time);
                                        $minutes = $first->diffInMinutes($timeA) + $second->diffInMinutes($timeD);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $first->diffInMinutes($timeA), $second->diffInMinutes($timeD));
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $first->diffInMinutes($timeA), $second->diffInMinutes($timeD));
                                        }
                                    } else if ($E == 1) {//C+E
                                        //获取分钟数
                                        $first = new Carbon($attendances[0]->time);
                                        $minutes = $first->diffInMinutes($timeA);
                                        //设置到岗天数
                                        if ($minutes >= 90) {
                                            $this->setCollection($userId, $date, '0', $minutes, 0);
                                        } else {
                                            $this->setCollection($userId, $date, '0.5', $minutes, 0);
                                        }
                                    }
                                } else if ($D == 1) {
                                    $attendances[0]->status_ = 0;
                                    $attendances[0]->save();
                                    $attendances[1]->status_ = 0;
                                    $attendances[1]->save();
                                    $this->setCollection($userId, $date, '0');
                                }
                            }
                            break;
                    }
                }
                \Log::info("The records in " . $date->toDateString() . " collecting success! ");
                $this->info("The records in " . $date->toDateString() . " collecting success! ");
            }
        } else {
            $this->info("date argument must lager than 0");
        }
    }

    /**
     *  去除2分钟内重复打卡记录，并重整收集器
     *  如果10条记录每条相差1分钟，那么只会留下第一条为有效数据
     *
     * @param $collections
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function dropRecords($collections)
    {
        $max = $collections->keys()->last();
        $record = $collections->first();
        for ($i = 1; $i <= $max; ++$i) {
            $pre = new Carbon($record->time);
            $current = new Carbon($collections[$i]->time);
            if ($pre->diffInSeconds($current) < 121) {
                $collections[$i]->status_ = 0;
                $collections[$i]->save();
                $record = $collections[$i];
                $collections->forget($i);
            } else {
                $record = $collections[$i];
            }
        }

        //重组收集器
        $multiplied = new \Illuminate\Database\Eloquent\Collection();
        foreach ($collections as $collection) {
            $multiplied->add($collection);
        }
        return $multiplied;
    }

    /**
     * 增加统计信息
     *
     * @param $user
     * @param $data
     * @param $availDay
     * @param int $later
     * @param int $early
     */
    public function setCollection($user, $data, $availDay, $later = 0, $early = 0)
    {
        Collection::create([
            'userNum' => $user,
            'date' => $data->toDateString(),
            'lateMinute' => $later,
            'lateTime' => 0,
            'earlyMinute' => $early,
            'earlyTime' => 0,
            'availableDay' => $availDay,
        ]);
    }
}
