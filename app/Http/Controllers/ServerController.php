<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Http\Requests;

use App\Config;
use App\Device;
use App\Log;
use App\Attendance;
use App\Userlog;
use App\Oplog;
use App\User;
use App\Face;
use App\Piclog;
use App\Finger;
use Carbon\Carbon;

class ServerController extends Controller
{

    /**
     * Get configs information.
     *
     * @return void
     */
    public function getConfig(Request $request)
    {
        $device = Device::where('SN', '=', $request->query('SN'))->first();

        if (is_null($device)) {
            $device = new Device;
            $device->SN = $request->query('SN');
            $device->Stamp = time(Carbon::now());
            $device->OpStamp = time(Carbon::now());
            $device->save();
        }
        $configs = Config::where('DeviceSN', $device->SN)->first();
        if (is_null($configs)) {
            $configs = new Config;
            $configs->DeviceSN = $request->query('SN');
            $configs->save();
        }

        return view('server.config', ['configs' => $configs, 'device' => $device]);
    }

    /**
     * Upload record.
     *
     * @return void
     */
    public function uploadRecord(Request $request)
    {
        if ($request->query('table') == 'ATTLOG') {
            $this->addAttendanceRecord($request);
        } else if ($request->query('table') == 'OPERLOG') {
            $this->addOperateRecoed($request);
        } else if ($request->query('table') == 'ATTPHOTO') {
            $this->addAttPhoto($request);
        }
    }

    /**
     * Upload attendance record.
     *
     * @return void
     */
    private function addAttendanceRecord(Request $request)
    {
        $raw_post_data = file_get_contents('php://input');
        $attArray = explode("\n", $raw_post_data);
        for ($i = 0; $i < count($attArray); ++$i) {
            if (strlen($attArray[$i]) > 0) {
                $infoArray = explode("\t", $attArray[$i]);
                $userId = $infoArray[0];
                $time = $infoArray[1];
                $status = $infoArray[2];
                $verify = $infoArray[3] . "";

                $carbon = new Carbon($time);

                if ($carbon->month >= 5 && $carbon->month < 10) {
                    $afternoonEnd = new Carbon($carbon->toDateString("yyyy-MM-dd") . " 17:00:00");
                } else {
                    $afternoonEnd = new Carbon($carbon->toDateString("yyyy-MM-dd") . " 16:30:00");
                }

                $attendance = Attendance::where("userId", $userId)->where("time", $time)->first();//去重复

                if (!is_null($attendance)) continue;

                $attendance = new Attendance();

                if ($carbon->lte($afternoonEnd)) {
                    $attendance->status_ = 1;
                } else {
                    $attendance->status_ = 2;
                }

                $attendance->userId = $userId;
                $attendance->time = $time;
                $attendance->status = $status;
                $attendance->verify = $verify;
                $attendance->save();
            }
        }
        $device = Device::where('SN', '=', $request->query('SN'))->first();
        $device->Stamp = $request->query('Stamp');
        $device->save();
        $configs = Config::where('DeviceSN', '=', $request->query('SN'))->first();
        $configs->ATTLOGStamp = $request->query('Stamp');
        $configs->save();
        echo "OK";
    }

    /**
     * Upload operate record.
     *
     * @return void
     */
    private function addOperateRecoed(Request $request)
    {
        $raw_post_data = file_get_contents('php://input');
        $counts = substr_count($raw_post_data, "\n");
        $logArray = explode("\n", $raw_post_data);
        for ($i = 0; $i < count($logArray); ++$i) {
            if (strlen($logArray[$i]) > 0) {
                $t0 = stripos($logArray[$i], " ", 0);
                $type = substr($logArray[$i], 0, $t0);
                switch ($type) {
                    case 'USER'://pass
                        $userInfoArray = explode("\t", $logArray[$i]);

                        $userNum = explode("=", $userInfoArray[0])[1];
                        $userName = explode("=", $userInfoArray[1])[1];
                        $userPri = explode("=", $userInfoArray[2])[1];
                        $userPassword = explode("=", $userInfoArray[3])[1];
                        $userCard = explode("=", $userInfoArray[4])[1];
                        $userCrp = explode("=", $userInfoArray[5])[1];

                        $user = User::where('num', $userNum)->first();

                        if (is_null($user)) {
                            $user = new User();
                        }

                        $user->num = $userNum;
                        $user->Name = $userName;
                        $user->Pri = $userPri;
                        $user->Passwd = $userPassword;
                        $user->Card = $userCard;
                        $user->Grp = $userCrp;
                        $user->save();
                        break;
                    case 'FP'://pass
                        $fingerInfoArray = explode("\t", $logArray[$i]);

                        $PIN = explode("=", $fingerInfoArray[0])[1];
                        $FID = explode("=", $fingerInfoArray[1])[1];
                        $Size = explode("=", $fingerInfoArray[2])[1];
                        $Valid = explode("=", $fingerInfoArray[3])[1];
                        $TMP = substr($fingerInfoArray[4], 4);
                        $finger = new Finger();
                        $finger->num = $FID;
                        $finger->usernum = $PIN;
                        $finger->FingerValid = $Valid;
                        $finger->FingerTMP = $TMP;
                        $finger->save();
                        break;
                    case 'OPLOG'://pass

                        $t1 = stripos($logArray[$i], "\t", $t0 + 1);
                        $t2 = stripos($logArray[$i], "\t", $t1 + 1);
                        $t3 = stripos($logArray[$i], "\t", $t2 + 1);
                        $t4 = stripos($logArray[$i], "\t", $t3 + 1);
                        $t5 = stripos($logArray[$i], "\t", $t4 + 1);
                        $t6 = stripos($logArray[$i], "\t", $t5 + 1);
                        $t7 = strlen($logArray[$i]);

                        $oplog = new Oplog;

                        $code = substr($logArray[$i], $t0 + 1, $t1 - $t0 - 1);
                        $oplog->code = $code;

                        $adminid = substr($logArray[$i], $t1 + 1, $t2 - $t1 - 1);
                        $oplog->adminid = $adminid;

                        $time = substr($logArray[$i], $t2 + 1, $t3 - $t2 - 1);
                        $oplog->time = $time;

                        $param0 = substr($logArray[$i], $t3 + 1, $t4 - $t3 - 1);
                        $oplog->param0 = $param0;

                        $param1 = substr($logArray[$i], $t4 + 1, $t5 - $t4 - 1);
                        $oplog->param1 = $param1;

                        $param2 = substr($logArray[$i], $t5 + 1, $t6 - $t5 - 1);
                        $oplog->param2 = $param2;

                        $param3 = substr($logArray[$i], $t6 + 1, $t7 - $t6 - 1);
                        $oplog->param3 = $param3;

                        $oplog->save();
                        break;
                    case 'USERPIC'://pass
                        $userPicInfoArray = explode("\t", $logArray[$i]);

                        $num = explode("=", $userPicInfoArray[0])[1];
                        $PicFile = explode("=", $userPicInfoArray[1])[1];
                        $PicSize = explode("=", $userPicInfoArray[2])[1];
                        $tmp = substr($userPicInfoArray[3], 8);

                        $pic = Piclog::where('PIN', '=', (int)$num)->first();
                        if (is_null($pic)) {
                            $pic = new Piclog();
                        }

                        $pic->PIN = $num;
                        $pic->FileName = $PicFile;
                        $pic->Size = $PicSize;
                        $pic->Content = $tmp;
                        $pic->save();
                        break;
                    case 'FACE':
                        $t1 = stripos($logArray[$i], "\t", $t0 + 1);
                        $t2 = stripos($logArray[$i], "\t", $t1 + 1);
                        $t3 = stripos($logArray[$i], "\t", $t2 + 1);
                        $t4 = stripos($logArray[$i], "\t", $t3 + 1);
                        $t5 = strlen($logArray[$i]);

                        $usernum = substr($logArray[$i], $t0 + 5, $t1 - $t0 - 5);
                        $num = substr($logArray[$i], $t1 + 5, $t2 - $t1 - 5);
                        $size = substr($logArray[$i], $t2 + 6, $t3 - $t2 - 6);
                        $valid = substr($logArray[$i], $t3 + 7, $t4 - $t3 - 7);
                        $tmp = substr($logArray[$i], $t4 + 5, $t5 - $t4 - 5);

                        $face = Face::where('num', '=', (int)$num)->first();

                        if (is_null($face)) {
                            $face = new Face();
                        }

                        $face->num = $num;
                        $face->usernum = $usernum;
                        $face->FaceSize = $size;
                        $face->FaceValid = $valid;
                        $face->FaceTMP = $tmp;
                        $face->save();
                        break;
                }
            }
        }
        $device = Device::where('SN', '=', $request->query('SN'))->first();
        $device->OpStamp = $request->query('OpStamp');
        $device->save();
        $configs = Config::where('DeviceSN', '=', $request->query('SN'))->first();
        $configs->OPERLOGStamp = $request->query('OpStamp');
        $configs->save();
        echo "OK";
    }

    /**
     * Upload operate record.
     *
     * @return void
     */
    private function addAttPhoto(Request $request)
    {
        $raw_post_data = file_get_contents('php://input');
        dd($raw_post_data);
    }

    /**
     * Determining attendance log is available or not
     *
     * @return void
     */
    private function getAvail(Carbon $start, Carbon $end, $user)
    {
        $attendance = Attendance::where("time", ">", $start->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
            ->where("time", "<", $end->toDateTimeString("yyyy-MM-dd hh:mm:ss"))
            ->where("userId", $user)
            ->get();
        if (count($attendance) > 0) {
            return 0;
        } else {
            return 1;
        }
    }

}
