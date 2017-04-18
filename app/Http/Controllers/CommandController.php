<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Command;
use App\Device;

use Carbon\Carbon;

use Illuminate\Support\Facades\Artisan;
use Log;

class CommandController extends Controller
{

    /**
     * Get commands.
     *
     * @return void
     */
    public function getCommand(Request $request)
    {
        $device = Device::where('SN', '=', $request->query('SN'))->first();
        $commands = Command::where('deviceId', '=', $device->id)->where('isExecuted', '=', false)->get();
        if (!is_null($request->query('INFO'))) {
            $infoArray = explode(",", $request->query('INFO'));
            $device->version = $infoArray[0];
            $device->userCounts = $infoArray[1];
            $device->FPcounts = $infoArray[2];
            $device->attCounts = $infoArray[3];
            $device->ip = $infoArray[4];
            $device->FPAlgoVersion = $infoArray[5];
            $device->FACEAlgoVersion = $infoArray[6];
            $device->FACEModelCounts = $infoArray[7];
            $device->FACECounts = $infoArray[8];
            $device->SupportFlag = $infoArray[9];
            $device->save();
        }
        if (!is_null($commands)) {
            foreach ($commands as $command) {
                echo "C:" . $command->id . ":" . $command->commandsStr . "\n";
            }
        }
    }

    /**
     * Executed commands.
     *
     * @return void
     */
    public function executedCommand(Request $request)
    {
        $raw_post_data = file_get_contents('php://input');
        $array = explode("&", $raw_post_data);
        $id = substr($array[0], 3, strlen($array[0]) - 3);
        $return = substr($array[1], 7, strlen($array[1]) - 7);
        $cmd = substr($array[2], 4, strlen($array[2]) - 5);
        if ($return == "0") {
            $command = Command::find((int)$id);
            $command->isExecuted = true;
            $command->save();
            echo "OK";
        }
    }

    public function example(Request $request)
    {
//        Artisan::call('update:status', [
//            'date' => $request->input('表单data的name'), 'usernum' => $request->input('表单usernum的name')
//        ]);
        return "123";
    }
}
