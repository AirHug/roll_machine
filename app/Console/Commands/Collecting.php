<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Attendance;
use App\Collection;

class Collecting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collecting {date=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collecting the yesterday attendance logs';

    /**
     * Collecting constructor.
     *
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
        $date = Carbon::now();
        if ($this->argument("date") > 0) $date->subDay($this->argument("date"));
        $attendances = Attendance::where("time", ">", $date->startOfDay()->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
            ->where("time", "<", $date->endOfDay()->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
            ->orderBy("userId")
            ->orderBy("time")
            ->get();

        $currentId = -1;
        $collection = null;
        $successCount = 0;

        foreach ($attendances as $attendance) {
            if ($attendance->userId != $currentId) {
                //存储上次记录
                if (!is_null($collection)) {
                    $collection->save();
                    ++$successCount;
                }
                //初始化新记录
                $collection = new Collection();
                $currentId = $attendance->userId;
                $collection->userNum = $attendance->userId;
                $collection->date = $date->startOfDay();
                $collection->lateMinute = 0;
                $collection->lateTime = 0;
                $collection->earlyMinute = 0;
                $collection->earlyTime = 0;
                $collection->availableDay = 0;
            }
            switch ($attendance->status_) {
                case 0://无效记录
                    break;
                case 1://上班签到
                    $collection->lateMinute = $collection->lateMinute + $attendance->latemin;
                    $collection->lateTime = $collection->lateTime + $attendance->latetime;
                    $collection->availableDay = $collection->availableDay + 0.5;
                    break;
                case 2://下班签到
                    $collection->earlyMinute = $collection->earlyMinute + $attendance->earlymin;
                    $collection->earlyTime = $collection->earlyTime + $attendance->earlytime;
                    break;
                default:
                    break;
            }
        }
        $collection->save();
        ++$successCount;
        $this->info("success!");
        \Log::info("success! " . $successCount . " records added in collections table in " . $date->toDateString());
    }
}
