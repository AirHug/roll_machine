<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Attendance;
use Illuminate\Console\Command;

class KillRepeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kill:repeat {date=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Killing repeat record.';

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
        if ($this->argument("date") != 0) {
            for ($day = 1; $day <= $this->argument("date"); ++$day) {
                $date = Carbon::now();
                if ($day > 0) $date->subDay($day);
                $attendances = Attendance::where("time", ">", $date->startOfDay()->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
                    ->where("time", "<", $date->endOfDay()->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
                    ->orderBy("userId")
                    ->orderBy("time", "desc")
                    ->get();
                $repeat = null;
                $count = 0;
                $this->info("-start finding repeated records-");
                for ($i = 0; $i < count($attendances); ++$i) {
                    if ($i + 1 < count($attendances)) {
                        if ($attendances[$i]->userId == $attendances[$i + 1]->userId && $attendances[$i]->time == $attendances[$i + 1]->time) {
                            $repeat = array_add($repeat, count($repeat), $attendances[$i]);
                        }
                    }
                }
                if (!is_null($repeat)) {
                    $this->info("-start deleting repeated records-");
                    foreach ($repeat as $item) {
                        $item->delete();
                        $this->info("Killed record " . $item->userId . " " . $item->time);
                        ++$count;
                    }
                    $this->info("-" . $count . " repeated records killed successful-");
                    \Log::info($count . " repeated records in " . $date->toDateString() . " killed successful!");
                } else {
                    $this->info("-Can not find repeated records-");
                    \Log::info("Can not find repeated records in " . $date->toDateString());
                }
            }
        } else {
            $this->info("date argument must lager than 0");
        }
    }
}
