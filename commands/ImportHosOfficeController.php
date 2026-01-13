<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use DateTime;
use DatePeriod;
use DateInterval;
use yii\helpers\Json;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Development;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\Organization;
use app\modules\approveV2\models\Approve;
use app\modules\hr\models\EmployeeDetail;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\booking\models\VehicleDetail;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\components\AssetHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class ImportHosOfficeController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */

    public function actionAll()
    {
        if (BaseConsole::confirm('ยืนยันการนำเข้าทั้งหมด?')) {
            $this->actionEmployee();
            $this->actionUpdatePosition();
            $this->actionLeave();
            $this->actionCreateApproveLeave();
            $this->actionDevelopment();
            $this->actionVehicle();
            $this->actionMeeting();
            $this->actionRepairGeneral();
            $this->actionAsset();
        } else {
            echo "user typed no\n";
        }
    }

    public function actionClearDir()
    {
        $dir = 'modules/filemanager/fileupload';
        $dirs = scandir($dir);
        foreach ($dirs as $object) {
            if ($object != '.' && $object != '..' && $object != '.gitignore' && $object != '.DS_Store') {
                $model = Uploads::findOne(['ref' => $object]);
                if ($model) {
                    $model->delete();
                }
                system('rm -rf ' . escapeshellarg($dir . '/' . $object));
                echo 'remove  ' . $object . "\n";
            }
        }
    }

    //นำเข้าบุคลากร
    public function actionEmployee()
    {

        $sqlPerson = "SELECT 
                    pt.HR_PERSON_TYPE_NAME AS person_type_name,
                    hp.HR_POSITION_NAME AS position_name,
                    p.POSITION_IN_WORK AS position_in_work_name,
                    p.HR_POSITION_NUM AS hr_position_num,
                    hr_level.HR_LEVEL_NAME AS hr_level_name,
                    dep.HR_DEPARTMENT_NAME AS work_group,
                    dep_sub.HR_DEPARTMENT_SUB_NAME AS sub_work_group,
                    dep_sub_sub.HR_DEPARTMENT_SUB_SUB_NAME AS department_name,
                    hr_status.HR_STATUS_NAME AS status_name,
                    pre.HR_PREFIX_NAME AS prefix,
                    p.HR_SALARY AS salary,
                    p.VCODE AS vcode,
                    p.VCODE_DATE AS vcode_date,
                    p.BOOK_BANK AS book_bank,
                    p.BOOK_BANK_NUMBER AS book_bank_number,
                    p.BOOK_BANK_NAME AS book_bank_name,
                    p.BOOK_BANK_BRANCH AS book_bank_branch,
                    CASE 
                        WHEN p.SEX = 'M' THEN 'ชาย'
                        WHEN p.SEX = 'F' THEN 'หญิง'
                        ELSE 'ไม่ระบุ'
                    END AS gender,
                    p.HR_STARTWORK_DATE AS join_date,
                    p.HR_FNAME AS fname,
                    p.HR_LNAME AS lname,
                    p.HR_IMAGE AS image,
                    p.HR_CID AS cid,
                    p.HR_EMAIL AS email,
                    p.HR_PHONE AS phone,
                    p.HR_BIRTHDAY AS birthday,
                    p.HR_ZIPCODE AS zipcode,
                    CONCAT('เลขที่ ', p.HR_HOME_NUMBER, ' ม.', p.HR_VILLAGE_NO) AS address,
                    p.HR_ROAD_NAME AS road,
                    p.HR_SOI_NAME AS soiname,
                    marry.HR_MARRY_STATUS_NAME AS marry,
                    nation.HR_NATIONALITY_NAME AS nationality,
                    re.HR_RELIGION_NAME AS religion,
                    b.HR_BLOODGROUP_NAME AS blood
                FROM hr_person p
                LEFT JOIN hr_bloodgroup b ON b.HR_BLOODGROUP_ID = p.HR_BLOODGROUP_ID
                LEFT JOIN hr_religion re ON re.HR_RELIGION_ID = p.HR_RELIGION_ID
                LEFT JOIN hr_marry_status marry ON marry.HR_MARRY_STATUS_ID = p.HR_MARRY_STATUS_ID
                LEFT JOIN hr_nationality nation ON nation.HR_NATIONALITY_ID = p.HR_NATIONALITY_ID
                LEFT JOIN hr_prefix pre ON pre.HR_PREFIX_ID = p.HR_PREFIX_ID
                LEFT JOIN hr_person_type p_type ON p_type.HR_PERSON_TYPE_ID = p.PERSON_TYPE
                LEFT JOIN hr_department dep ON dep.HR_DEPARTMENT_ID = p.HR_DEPARTMENT_ID
                LEFT JOIN hr_department_sub dep_sub ON dep_sub.HR_DEPARTMENT_SUB_ID = p.HR_DEPARTMENT_SUB_ID
                LEFT JOIN hr_department_sub_sub dep_sub_sub ON dep_sub_sub.HR_DEPARTMENT_SUB_SUB_ID = p.HR_DEPARTMENT_SUB_SUB_ID
                LEFT JOIN hr_person_type pt ON pt.HR_PERSON_TYPE_ID = p.HR_PERSON_TYPE_ID
                LEFT JOIN hr_position hp ON hp.HR_POSITION_ID = p.HR_POSITION_ID
                LEFT JOIN hr_status ON hr_status.HR_STATUS_ID = p.HR_STATUS_ID
                LEFT JOIN hr_level ON hr_level.HR_LEVEL_ID = p.HR_LEVEL_ID where p.HR_STATUS_ID = '01';";
        // if (BaseConsole::confirm('Are you sure?')) {
        $querys = \Yii::$app->db2->createCommand($sqlPerson)->queryAll();
        $num = 1;
        $total = count($querys);
        echo "เริ่มนำเข้าข้อมูลบุคลากร...\n";
        foreach ($querys as $person) {
            $checker = Employees::findOne(['cid' => $person['cid']]);
            if (!$checker) {
                $mappedType = $this->MapPositionType($person);
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new Employees();
                $model->ref = $ref;
                $this->CreateDir($ref);
                if ($person['image']) {
                    $name = time() . '.jpg';
                    file_put_contents(\Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $person['image']);

                    $upload = new Uploads();
                    $upload->ref = $ref;
                    $upload->name = 'avatar';
                    $upload->file_name = $name;
                    $upload->real_filename = $name;
                    $upload->type = 'jpg';
                    $upload->save(false);
                }

                $model->user_id = 0;
                $model->prefix = $person['prefix'];
                $model->gender = $person['gender'];
                $model->fname = $person['fname'];
                $model->lname = $person['lname'];
                $model->join_date = $person['join_date'];
                $model->salary = $person['salary'];
                $model->birthday = AppHelper::DateFormDb($person['birthday']);
                $model->cid = $person['cid'];
                $model->phone = preg_match('/-/', (string) $person['phone']) ? null : $person['phone'];
                $model->email = $person['email'];
                $model->zipcode = $person['zipcode'];
                $model->department = Organization::find()
                    ->select(['id'])
                    ->filterWhere(['like', 'name', $person['department_name'] ?? null])
                    ->scalar() ?: 0;

                if ($mappedType) {
                    $model->position_group = $mappedType['position_group_code'];
                    $model->position_type = $mappedType['position_type_code'];
                    $model->position_name = $mappedType['position_code'];
                } else {
                    $model->position_group = null;
                    $model->position_type = null;
                    $model->position_name = null;
                }
                $model->education = 0;  // การศึกษา
                $model->status = $this->MapEmployeeStatus($person);  // สถานะ
                $model->address = $person['address'];
                // 1. คัดลอกข้อมูล person มาไว้ในตัวแปรใหม่
                $old_person_data = $person;
                // 2. ลบเฉพาะ key 'image' ออก
                unset($old_person_data['image']);

                $model->data_json = $old_person_data;

                $this->Family($model->id, $model->cid);

                if ($model->save(false)) {
                } else {
                    echo "False \n";
                }
            } else {
                // echo "นำเข้าข้อมูลแล้ว!!  \n";
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        // } else {
        //     echo "user typed no\n";
        // }
    }


    public function MapEmployeeStatus($data)
    {
        // กำหนดโครงสร้างข้อมูลสำหรับการ Mapping
        $map = [
            'ทำงานปกติ' => ['code' => '1', 'title' => 'ปฏิบัติราชการ'],
            'ลาออก' => ['code' => '2', 'title' => 'ลาออก'],
            'ย้าย' => ['code' => '13', 'title' => 'จ้างเหมาบริการรายวัน'],
            'ลาศึกษาต่อ' => ['code' => '21', 'title' => 'ลาศึกษาในประเทศ'],
            'ให้ออก' => ['code' => '25', 'title' => 'ไล่ออก']
        ];
        return  isset($map[$data['status_name']]) ? $map[$data['status_name']] : [
            'code' => null,
            'title' => 'ไม่พบข้อมูล'
        ];
    }
    public function MapPositionType($data)
    {
        // กำหนดโครงสร้างข้อมูลสำหรับการ Mapping
        $map = [
            'ข้าราชการ' => ['code' => 'PT1', 'title' => 'ข้าราชการ'],
            'พนักงานราชการ' => ['code' => 'PT2', 'title' => 'พนักงานราชการ'],
            'พนักงานกระทรวงสาธารณสุข' => ['code' => 'PT3', 'title' => 'พนักงานกระทรวง (พกส.)'],
            'ลูกจ้างชั่วคราว' => ['code' => 'PT4', 'title' => 'ลูกจ้างชั่วคราวรายเดือน'],
            'ลูกจ้างรายวัน' => ['code' => 'PT5', 'title' => 'ลูกจ้างชั่วคราวรายวัน'],
            'ลูกจ้างประจำ' => ['code' => 'PT6', 'title' => 'ลูกจ้างประจำ'],
            'จ้างเหมาบริการ' => ['code' => 'PT7', 'title' => 'จ้างเหมาบริการรายวัน'],
        ];
        $mapData =  isset($map[$data['person_type_name']]) ? $map[$data['person_type_name']] : [
            'code' => null,
            'title' => 'ไม่พบข้อมูล'
        ];

        $sql = "SELECT pname.title AS position_name,
            pname.code AS position_code,
            p_group.code AS position_group_code,
            p_group.title AS position_group_name,
            p_type.code AS position_type_code, -- นี่คือ PT1-PT7
            p_type.title AS position_type_name
            FROM `categorise` pname
            LEFT JOIN `categorise` p_group ON p_group.code = pname.category_id AND  p_group.name = 'position_group'
            LEFT JOIN `categorise` p_type ON p_type.code = p_group.category_id AND p_type.name = 'position_type'
            WHERE pname.title LIKE :title
            AND p_type.code = :code;";
        $query = \Yii::$app->db->createCommand($sql)
            ->bindValue(':code', $mapData['code'])
            ->bindValue(':title', '%' . $data['position_name'] . '%');
        return $query->queryOne();

        // ตรวจสอบว่ามีข้อมูลใน Map หรือไม่ ถ้าไม่มีให้ส่งค่า Default กลับไป

    }

    public function actionUpdatePosition()
    {
        // ใช้ batch() หากพนักงานมีจำนวนมากเพื่อประหยัด Memory
        $querys = Employees::find()->all();
        $num = 1;
        $total = count($querys);
        echo "เริ่มอัพเดทข้อมูลตำแหน่งงาน...\n";

        foreach ($querys as $emp) {
            $data = is_string($emp->data_json) ? Json::decode($emp->data_json) : $emp->data_json;
            // 1. ค้นหาหรือสร้าง Model ใหม่
            $empDetail = EmployeeDetail::findOne(['emp_id' => $emp->id, 'name' => 'position'])
                ?? new EmployeeDetail();

            if (!$empDetail->ref) {
                $empDetail->ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
            }


            $empDetail->emp_id = $emp->id;
            $empDetail->name = 'position';
            $empDetail->data_json = ArrayHelper::merge($empDetail->data_json ?? [], []);
            $empDetail->data_json = ArrayHelper::merge($empDetail->data_json ?? [], [
                "point" => "",
                "salary" => $data['salary'] ?? 0,
                "status" => $emp->status,
                "comment" => "-",
                "doc_ref" => "คำสั่ง",
                "date_end" => "",
                "fullname" => $emp->fullname ?? "ไม่ระบุชื่อ",
                "expertise" => "",
                "date_start" => $data['join_date'] ?? null,
                "department" => "27",
                "statuslist" => "โอนข้อมูลจาก hos-office",
                "position_number" => "-",
                "position_name"       => $emp->position_name,
                "position_type"       => $emp->position_type,
                "position_group"      => $emp->position_group,
                "position_level"      => $emp->position_level,

            ]);

            $emp->save(false);
            // 4. บันทึกข้อมูล (ใช้ save(false) เพื่อข้าม validation หากมั่นใจในข้อมูล)
            if (!$empDetail->save(false)) {
                \Yii::error("Save Error ID: " . $emp->id . " Errors: " . Json::encode($empDetail->getErrors()));
            }

            BaseConsole::updateProgress($num, $total);
            $num++;
        }
    }


    // ประวัติครอบครัว
    public function Family($emp_id, $cid)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $data = [];
        $sql = 'SELECT p.`HR_FNAME`,p.`HR_LNAME`,p.`HR_CID`,f.`NAME`,f.`TYPE`,f.`PHONE`,f.`ADDRESS` FROM hr_tr_family f 
            LEFT JOIN hr_person p ON p.`ID` = f.`PERSON_ID` 
            WHERE p.`HR_CID` = :cid';
        $querys = \Yii::$app
            ->db2
            ->createCommand($sql)
            ->bindParam(':cid', $cid)
            ->queryAll();

        foreach ($querys as $query) {
            $data_json = [
                'fname' => isset(explode(' ', $query['NAME'], 2)[0]) ? explode(' ', $query['NAME'], 2)[0] : '',
                'lname' => isset(explode(' ', $query['NAME'], 2)[1]) ? explode(' ', $query['NAME'], 2)[1] : '',
                'family_relation' => $query['TYPE'],
                'phone' => $query['PHONE'],
                'address' => $query['ADDRESS'],
            ];

            $model = new EmployeeDetail();
            $model->name = 'family';
            $model->emp_id = $emp_id;
            $model->data_json = ArrayHelper::merge($model->data_json, $data_json);
            $model->save(false);

            $data[] = $model;
        }

        return $data;
    }

    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            $basePath = \Yii::getAlias('@app') . '/modules/filemanager/fileupload/';
            if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
                BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
            }
        }

        return;
    }

    //จองรถยนต์
    public function actionVehicle()
    {
        // ระบบจองรถย์
        $querys = Yii::$app->db2->createCommand('SELECT 
                p.HR_FNAME, 
                p.HR_LNAME, 
                pr.PRIORITY_NAME,
                i.CAR_REG,
                car_req.CAR_REG as car_req,
                d.PERSON_ID as driver_id,
                l.LOCATION_NAME,
                v.*
            FROM 
                car_reserve v
            LEFT JOIN 
                hr_person p ON p.ID = v.RESERVE_PERSON_ID
            LEFT JOIN 
                car_index i  ON i.CAR_ID = v.CAR_SET_ID
            LEFT JOIN 
                car_index car_req  ON car_req.CAR_ID = v.CAR_REQUEST_ID
            LEFT JOIN 
                car_driver d  ON d.PERSON_ID = v.CAR_DRIVER_SET_ID
            LEFT JOIN 
                record_org_location l ON l.LOCATION_ID = v.RESERVE_LOCATION_ID
            LEFT JOIN 
                car_priority pr ON CAST(pr.PRIORITY_ID AS UNSIGNED) = v.PRIORITY_ID ORDER BY v.RESERVE_END_DATE ASC;')
            ->queryAll();
        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลจองรถ...\n";

        foreach ($querys as $key => $item) {
            if ($item['RESERVE_BEGIN_DATE'] && $item['RESERVE_END_DATE']) {
                $emp = $this->Person($item['RESERVE_PERSON_ID']);
                $check = Vehicle::find()->where([
                    'reason' => $item['RESERVE_NAME'],
                    'date_start' => $item['RESERVE_BEGIN_DATE'],
                    'date_end' => $item['RESERVE_END_DATE'],
                    'time_start' => $item['RESERVE_BEGIN_TIME'],
                    'time_end' => $item['RESERVE_END_TIME'],
                ])->one();

                $model = $check ?? new Vehicle();
                $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('CAR' . date('ymd') . '-???'));
                $model->thai_year = AppHelper::YearBudget($item['RESERVE_BEGIN_DATE']);
                $model->reason = $item['RESERVE_NAME'];
                $model->vehicle_type_id = 'official';
                $model->refer_type = 'normal';
                $model->go_type = 1;
                $model->urgent = $item['PRIORITY_NAME'];
                $model->location = $this->checkLocation($item['LOCATION_NAME']);
                $model->status = $this->BookingStatus($item['STATUS']);
                $model->leader_id = $this->Person($item['LEADER_PERSON_ID'])?->id;
                $model->date_start = $item['RESERVE_BEGIN_DATE'] ?? date('Y-m-d');
                $model->date_end = $item['RESERVE_END_DATE'];
                $model->time_start = $item['RESERVE_BEGIN_TIME'] ?? '00:00';
                $model->time_end = $item['RESERVE_END_TIME'] ?? '00:00';
                $model->emp_id = $emp->id ?? 0;
                $model->driver_id = $this->Person($item['CAR_DRIVER_ID'])?->id;
                $model->created_at = $item['RESERVE_DATE_TIME'];
                $model->license_plate = $item['car_req'];

                if ($emp) {
                    $model->data_json = [
                        'old_data' => $item

                    ];
                }

                $model->save(false);
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        return ExitCode::OK;
    }

    //สร้างการจัดสรรถยนต์
    protected function createDetail($model, $item)
    {
        if ($model->date_start && $model->date_end) {

            $startDate = new DateTime($model->date_start);
            $endDate = new DateTime($model->date_end);

            $endDate->modify('+1 day');  // เพิ่ม 1 วัน เพื่อให้รวมวันที่สิ้นสุด

            $interval = new DateInterval('P1D');  // ระยะห่าง 1 วัน
            $period = new DatePeriod($startDate, $interval, $endDate);
            //ถ้าเป็นรถยนต์ส่วนตัว
            if ($model->vehicle_type_id == "personal") {
                $me = UserHelper::GetEmployee();
                if ($model->go_type == "1") {
                    $dates = [];
                    foreach ($period as $date) {
                        $check1 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                        if (!$check1) {
                            $newDetail = new VehicleDetail;
                            $newDetail->date_start = $date->format('Y-m-d');
                            $newDetail->date_end = $date->format('Y-m-d');
                            $newDetail->vehicle_id = $model->id;
                            $newDetail->driver_id = $me->id;
                            $newDetail->license_plate = $model->license_plate;
                            $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                            $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                            $newDetail->oil_price = $item['OIL_IN_BATH'];
                            $newDetail->oil_liter = $item['OIL_IN_LIT'];
                            $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                            $newDetail->status = $this->BookingStatus($model->status);
                            $newDetail->save(false);
                        }
                    }
                } else {
                    $check2 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                    if (!$check2) {
                        $newDetail = new VehicleDetail;
                        $newDetail->date_start = $model->date_start;
                        $newDetail->date_end = $model->date_end;
                        $newDetail->vehicle_id = $model->id;
                        $newDetail->driver_id = $me->id;
                        $newDetail->license_plate = $model->license_plate;
                        $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                        $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                        $newDetail->oil_price = $item['CAR_NUMBER_BACK'];
                        $newDetail->oil_price = $item['OIL_IN_BATH'];
                        $newDetail->oil_liter = $item['OIL_IN_LIT'];
                        $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                        $newDetail->status = $this->BookingStatus($model->status);
                        $newDetail->save(false);
                    }
                }


                $checkApprove = Approve::find()->where(['from_id' => $model->id, 'name' => 'vehicle', 'level' => 1])->one();
                if (!$checkApprove) {
                    $info = SiteHelper::getInfo();
                    $newAprove = new Approve();
                    $newAprove->from_id = $model->id;
                    $newAprove->name = 'vehicle';
                    $newAprove->emp_id = $info['director']->id;
                    $newAprove->title = 'ขออนุมัติใช้รถ';
                    $newAprove->data_json = ['label' => 'อนุมัติ'];
                    $newAprove->level = 1;
                    $newAprove->status = 'Pending';
                    $newAprove->save(false);
                }
            } else {

                if ($model->go_type == "1") {
                    $dates = [];
                    foreach ($period as $date) {
                        $check3 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                        if (!$check3) {
                            $dates[] = $date->format('Y-m-d');
                            $newDetail = new VehicleDetail;
                            $newDetail->vehicle_id = $model->id;
                            $newDetail->date_start = $date->format('Y-m-d');
                            $newDetail->date_end = $date->format('Y-m-d');
                            $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                            $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                            $newDetail->oil_price = $item['OIL_IN_BATH'];
                            $newDetail->oil_liter = $item['OIL_IN_LIT'];
                            $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                            $newDetail->status = $this->BookingStatus($model->status);
                            $newDetail->save(false);
                        }
                    }
                } else {
                    $check4 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
                    if (!$check4) {
                        $newDetail = new VehicleDetail;
                        $newDetail->vehicle_id = $model->id;
                        $newDetail->date_start = $model->date_start;
                        $newDetail->date_end = $model->date_end;
                        $newDetail->mileage_start = $item['CAR_NUMBER_BEGIN'];
                        $newDetail->mileage_end = $item['CAR_NUMBER_BACK'];
                        $newDetail->oil_price = $item['OIL_IN_BATH'];
                        $newDetail->oil_liter = $item['OIL_IN_LIT'];
                        $newDetail->driver_id = $this->Person($item['CAR_DRIVER_SET_ID'])?->id;
                        $newDetail->status = $this->BookingStatus($model->status);
                        $newDetail->save(false);
                    }
                }
            }
        }
    }
    // รถพยาบาล
    public function actionRefer()
    {
        $sql = "SELECT rt.REFER_TYPE_NAME,c.CAR_REG,l.RECORD_LOCATION_NAME,r.* FROM `car_refer` r
        LEFT JOIN car_index c ON c.CAR_ID = r.CAR_ID
        LEFT JOIN record_location l ON l.RECORD_LOCATION_ID = r.REFER_LOCATION_GO_ID
        LEFT JOIN car_refer_type rt ON rt.REFER_TYPE_ID = r.REFER_TYPE_ID WHERE r.OUT_DATE IS NOT NULL;";
        // นำวันลา
        $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();

        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            try {

                $emp = $this->Person($item['USER_REQUEST_ID']);

                $check = Vehicle::find()->where([
                    'reason' => $item['REFER_TYPE_NAME'],
                    'date_start' => $item['OUT_DATE'],
                    'date_end' => $item['BACK_DATE'],
                    'time_start' => $item['OUT_TIME'],
                    'time_end' => $item['BACK_TIME'],
                ])->one();

                $model = $check ?? new Vehicle();
                $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('AMB' . date('ymd') . '-???'));
                $model->thai_year = AppHelper::YearBudget($item['OUT_DATE']);
                $model->reason = $item['REFER_TYPE_NAME'] ?? '-';
                $model->vehicle_type_id = 'ambulance'; //รถพยาบาล
                $model->refer_type = $this->referType($item['REFER_TYPE_NAME']) ?? '-';
                $model->go_type = 1;
                $model->urgent = 'ปกติ';
                $model->location = $this->checkLocation($item['RECORD_LOCATION_NAME']);
                $model->status = 'Pass';
                $model->leader_id = 0;
                $model->date_start = $item['OUT_DATE'];
                $model->date_end = ($item['BACK_DATE'] === '0000-00-00' || empty($item['BACK_DATE'])) ? null : $item['BACK_DATE'];
                $model->time_start = $item['OUT_TIME'] ?? '00:00';
                $model->time_end = $item['BACK_TIME'] ?? '00:00';
                $model->emp_id = $emp->id ?? 0;
                $model->license_plate = $item['CAR_REG'];

                // if($emp){
                $model->data_json = [
                    'old_data' => $item,

                ];
                // }
                $model->save(false);
                //code...
            } catch (\Throwable $th) {
                echo "Error processing item: " . json_encode($item) . "\n";
                echo "Exception: " . $th->getMessage() . "\n";
            }
        }
        return ExitCode::OK;
    }

    //สร้างการจัดสรรถยนต์
    protected function createDetailRefer($model, $item)
    {
        if ($model->date_start && $model->date_end) {
            // foreach ($period as $date) {
            $check1 = VehicleDetail::find()->where(['vehicle_id' => $model->id])->one();
            if (!$check1) {
                $newDetail = new VehicleDetail;
                $newDetail->date_start = $model->date_start;
                $newDetail->date_end = $model->date_end ?? $model->date_start;
                $newDetail->vehicle_id = $model->id;
                $newDetail->license_plate = $model->license_plate;
                $newDetail->mileage_start = $item['CAR_GO_MILE'];
                $newDetail->mileage_end = $item['CAR_BACK_MILE'];
                $newDetail->oil_price = $item['ADD_OIL_BATH'];
                $newDetail->oil_liter = $item['ADD_OIL_LIT'];
                $newDetail->driver_id = $this->Person($item['DRIVER_ID'])?->id ?? 0;
                $newDetail->status = $this->BookingStatus($model->status);
                $newDetail->save(false);
            }
            // }
        }
    }
    // ประเภทการส่งต่อ
    public static function referType($referType)
    {
        if ($referType == 'REFER') {
            return 'REFER';
        } elseif ($referType == 'EMS') {
            return 'EMS';
        } elseif ($referType == 'รับ-ส่ง [ไม่ฉุกเฉิน]') {
            return 'NORMAL';
        } else {
            return $referType;
        }
    }
    //สถานะรถยนต์
    public static function BookingStatus($status)
    {
        if ($status == 'LASTAPP') {
            return 'Approve';
        } else if ($status == 'Cancel') {
            return 'Cancel';
        } else if ($status == 'RECERVE') {
            return 'Pending';
        } else if ($status == 'REQUEST') {
            return 'Pending';
        } else if ($status == 'SUCCESS') {
            return 'Pass';
        } else {
            return $status;
        }
    }

    //ค้นหาบุคลากร
    public static function Person($id)
    {
        $person = Yii::$app->db2->createCommand('SELECT * FROM `hr_person` WHERE ID = :id')
            ->bindValue(':id', $id)->queryOne();
        if ($person) {
            $emp = Employees::findOne(['cid' => $person['HR_CID']]);
            return $emp;
        }
    }

    // ตรวจสอบสถานที่
    protected function checkLocation($locationName)
    {
        $location = Categorise::findOne(['name' => 'document_org', 'title' => $locationName]);
        if (!$location) {
            $maxCode = Categorise::find()
                ->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])
                ->where(['like', 'name', 'document_org'])
                ->scalar();
            $newLocation = new Categorise;
            $newLocation->code = ($maxCode + 1);
            $newLocation->title = $locationName;
            $newLocation->name = 'document_org';
            $newLocation->save(false);
            return $newLocation->code;
        } else {
            return $location->code;
        }
    }


    // อบรม/ศึกษา/ดูงาน
    public function actionDevelopment()
    {
        $sql = 'SELECT go.RECORD_GO_NAME,l.LOCATION_NAME,
                v.RECORD_VEHICLE_NAME,
                i.*
                FROM `record_index` i
                LEFT JOIN record_go go ON go.RECORD_GO_ID = i.RECORD_GO_ID
                LEFT JOIN record_org_location l ON l.LOCATION_ID = i.RECORD_LOCATION_ID
                LEFT JOIN record_vehicle v ON v.RECORD_VEHICLE_ID = i.RECORD_VEHICLE_ID
                LEFT JOIN record_type t ON t.RECORD_TYPE_ID = i.RECORD_TYPE_ID;;';
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();

        // if (BaseConsole::confirm('การพัฒนา ' . count($querys) . ' รายการ ยืนยัน ??')) {
        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลการพัฒนา...\n";
        foreach ($querys as $item) {
            $checkModel = Development::findOne(['topic' => $item['RECORD_HEAD_USE']]);
            if (!$checkModel) {
                $model = new Development();
            } else {
                $model = $checkModel;
            }

            if ($item['RECORD_HEAD_USE']) {
                $model->topic = $item['RECORD_HEAD_USE'] ?? '-';
                $model->date_start = $item['DATE_GO'];
                $model->date_end = $item['DATE_BACK'];
                $model->vehicle_date_start = $item['DATE_TRAVEL_GO'] ?? NULL;
                $model->vehicle_date_end = $item['DATE_TRAVEL_BACK'] ?? NULL;
                $model->status = $this->getDevStatus($item['STATUS']);
                $model->thai_year = AppHelper::YearBudget($item['DATE_GO']);
                $model->assigned_to = $this->Person($item['OFFER_WORK_HR_ID'])?->id ?? 0;
                $model->emp_id = $this->Person($item['HR_ID'])?->id ?? 0;
                $model->development_type_id = $this->getDevType($item['RECORD_TYPE_ID']);

                $model->leader_id = $this->Person($item['LEADER_HR_ID'])?->id;
                $model->leader_group_id = $this->Person($item['HR_DEPART_ID'])?->id;
                $model->data_json = $item;
                if ($model->save(false)) {
                    $this->creteDetailMember($model);
                    $this->creteDevApprove($model);
                }
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
    }


    // นำเข้าส่วนของคณะที่ไปด้วยกัน
    protected function creteDetailMember($data)
    {
        $check = DevelopmentDetail::findOne(['development_id' => $data->id]);
        if (!$check) {
            $model = new DevelopmentDetail();
        } else {
            $model = $check;
        }
        $model->development_id = $data->id;
        $model->name = 'member';
        $model->emp_id = $data->emp_id;
        $model->save(false);
    }

    protected function creteDevApprove($model)
    {
        $checkApprove1 = Approve::findOne(['from_id' => $model->id, 'name' => 'development', 'emp_id' => $model->leader_id, 'level' => 1]);
        if ($checkApprove1) {
            $approve1 = $checkApprove1;
        } else {
            $approve1 = new Approve();
        }
        $approve1->name = 'development';
        $approve1->from_id = $model->id;
        $approve1->level = 1;
        $approve1->emp_id = $model->leader_id;
        $approve1->title = 'เห็นชอบ';
        $approve1->data_json = [
            'topic' => 'เห็นชอบ',
            'approve_date' => null
        ];
        $approve1->status = $model->status;
        $approve1->save(false);

        $checkApprove2 = Approve::findOne(['from_id' => $model->id, 'name' => 'development', 'emp_id' => $model->leader_group_id, 'level' => 2]);
        if ($checkApprove2) {
            $approve2 = $checkApprove2;
        } else {
            $approve2 = new Approve();
        }
        $approve2->name = 'development';
        $approve2->from_id = $model->id;
        $approve2->level = 2;
        $approve2->emp_id = $model->leader_group_id;
        $approve2->title = 'เห็นชอบ';
        $approve2->data_json = [
            'topic' => 'เห็นชอบ',
            'approve_date' => null
        ];
        $approve2->status = $model->status;
        $approve2->save(false);
    }

    private function getDevStatus($data)
    {
        switch ($data) {
            case 'APPLY':
                return 'Pending';
            case 'RECEIVE':
                return 'Pending';
            case 'SUCCESS':
                return 'Approve';
            case 'CANCEL':
                return 'Cancel';
            case 'NOTALLOW':
                return 'Reject';
            default:
                return 'Unknown';
        }
    }

    public function getDevType($data)
    {
        switch ($data) {
            case '1':
                return 'dev1';
            case '2':
                return 'dev2';
            case '3':
                return 'dev3';
            case '4':
                return 'dev4';
            case '5':
                return 'dev5';
            case '6':
                return 'dev6';
            default:
                return 'Unknown';
        }
    }

    public function actionCreateMoney()
    {

        $sql = 'SELECT i.RECORD_HEAD_USE,m.MONEY_ID,m.SUMMONEY FROM `grecord_index_money` m LEFT JOIN grecord_index i ON i.ID = m.RECORD_ID WHERE `SUMMONEY` IS NOT NULL;';
        $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();

        if (count($querys) > 0) {
            foreach ($querys as $item) {

                // try {

                $checkModel = Development::findOne(['topic' => $item['RECORD_HEAD_USE']]);
                //  echo 'Create Expense Type : ' . $checkModel->topic . "\n";
                $checkDetail = DevelopmentDetail::findOne(['development_id' => $checkModel->id, 'name' => 'expense_type', 'category_id' => 'ET' . $item['MONEY_ID']]);
                if (!$checkDetail) {
                    $model = new DevelopmentDetail();
                } else {
                    $model = $checkDetail;
                }
                $model->development_id = $checkModel->id;
                $model->category_id = 'ET' . $item['MONEY_ID'];
                $model->name = 'expense_type';
                $model->price = $item['SUMMONEY'];
                $model->data_json = [
                    'old_data' => $item,
                ];
                if ($model->save(false)) {
                    echo 'Create Expense Type : ' . $model->id . "\n";
                }
                //code...
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }
            }
        }
    }

    //ระบบลา
    public function actionLeave()
    {
        $querys = Yii::$app->db2->createCommand('SELECT *
                    FROM leave_register
                    LEFT JOIN leave_type ON leave_register.LEAVE_TYPE_CODE = leave_type.LEAVE_TYPE_ID
                    LEFT JOIN leave_status ON leave_register.LEAVE_STATUS_CODE = leave_status.STATUS_CODE
                    LEFT JOIN leave_location ON leave_register.LOCATION_ID = leave_location.LOCATION_ID
                    LEFT JOIN leave_day_type ON leave_day_type.DAY_TYPE_ID = leave_register.DAY_TYPE_ID
                    ORDER BY leave_register.ID DESC;')
            ->queryAll();
        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลลา...\n";

        foreach ($querys as $key => $item) {
            try {
                $emp = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);
                $sendwork = $this->Person($item['LEAVE_WORK_SEND_ID']);
                $leaderLevel1 = $this->Person($item['LEADER_PERSON_ID']);
                $leaderLevel2 = $this->Person($item['LEADER_DEP_PERSON_ID']);
                $userCheckId = $this->Person($item['USER_CONFIRM_CHECK_ID']);
                $ApproveDirector = $this->Person($item['TOP_LEADER_AC_ID']);

                $checkLeave = Leave::find()->where([
                    'emp_id' =>  $emp->id,
                    'thai_year' => $item['LEAVE_YEAR_ID'],
                    'date_start' => $item['LEAVE_DATE_BEGIN'],
                    'date_end' => $item['LEAVE_DATE_END'],
                ])->andWhere(['json_extract(data_json, "$.cid")' => $item['LEAVE_PERSON_CODE']])->one();

                $leaveType = Categorise::findOne(['name' => 'leave_type', 'title' => $item['LEAVE_TYPE_NAME']]);

                $leave = $checkLeave ?? new Leave();


                $leave->leave_type_id = $leaveType ? $leaveType->code : '';
                $leave->emp_id = $emp ? $emp->id : 0;
                $leave->thai_year = $item['LEAVE_YEAR_ID'];
                $leave->date_start = $item['LEAVE_DATE_BEGIN'];
                $leave->date_end = $item['LEAVE_DATE_END'];
                $leave->status = $this->getStatus($item['STATUS_CODE'])['status'];

                if ($item['DAY_TYPE_ID'] == 2) {
                    $leave->date_start_type = 0.5;
                    $leave->date_end_type = 0;
                } elseif ($item['DAY_TYPE_ID'] == 3) {

                    $leave->date_start_type = 0;
                    $leave->date_end_type = 0.5;
                } else {
                    $leave->date_start_type = 0;
                    $leave->date_end_type = 0;
                }

                $leave->total_days = $item['WORK_DO'];

                $leaveJson  = [

                    'sat_sun_days' => $item['LEAVE_SUM_HOLIDAY'], // วันหยุดเสาร์-อาทิตย์
                    'holidays' => $item['LEAVE_SUM_SETSUN'], //วันหยุดนคัตฤก
                    'sum_all_days' => $item['LEAVE_SUM_ALL'], //รวมจำนวนวันทั้งหมด
                    'id' => $item['ID'],
                    'cid' => $item['LEAVE_PERSON_CODE'],
                    'fullname' => $item['LEAVE_PERSON_FULLNAME'],
                    'leave_type_id' => $item['LEAVE_TYPE_ID'],
                    'leave_type_name' => $item['LEAVE_TYPE_NAME'],
                    'status_code' => $item['STATUS_CODE'],
                    'status_name' => $item['STATUS_NAME'],
                    'location_id' => $item['LOCATION_ID'],
                    'location' => $item['LOCATION_NAME'],
                    'reason' => $item['LEAVE_BECAUSE'],
                    'leave_date_sum' => $item['LEAVE_DATE_SUM'],
                    'day_type_id' => $item['DAY_TYPE_ID'],
                    'address' => $item['LEAVE_CONTACT'],
                    'leave_datetime_regis' => $item['LEAVE_DATETIME_REGIS'],
                    'leave_type_code' => $item['LEAVE_TYPE_CODE'],
                    'leave_person_id' => $item['LEAVE_PERSON_ID'],
                    'leave_status_code' => $item['LEAVE_STATUS_CODE'],
                    'phone' => $item['LEAVE_CONTACT_PHONE'],
                    'leave_work_send' => $item['LEAVE_WORK_SEND'],
                    'leave_work_send_id' => isset($sendwork) ? $sendwork->id : 0,
                    'approve_1' => isset($leaderLevel1) ? (string)$leaderLevel1->id : 0,
                    'approve_fullname_1' => isset($leaderLevel1) ? (string)$leaderLevel1->fullname : 0,
                    'approve_2' => isset($leaderLevel2) ? (string)$leaderLevel2->id : 0,
                    'approve_fullname_2' => isset($leaderLevel2) ? (string)$leaderLevel2->fullname : 0,
                    'approve_3' => isset($userCheckId) ? (string)$userCheckId->id : 0,
                    'approve_fulname_3' => isset($userCheckId) ? (string)$userCheckId->fullname : 0,
                    'approve_4' => isset($ApproveDirector) ? (string)$ApproveDirector->id : 0,
                    'approve_fullname_4' => isset($ApproveDirector) ? (string)$ApproveDirector->fullname : 0,
                    'director_id' => $item['TOP_LEADER_AC_ID'],
                    'director_fullname' => $item['TOP_LEADER_AC_NAME'],
                    'leader' => isset($leaderId) ? (string)$leaderId->id : 0,
                    'leader_person_name' => $item['LEADER_PERSON_NAME'],
                    'leader_person_position' => $item['LEADER_PERSON_POSITION'],
                ];
                $leave->data_json = ArrayHelper::merge($leave->data_json ?? [], $leaveJson, $item);

                if ($leave->save(false)) {
                    // $this->CreateApprove($item);
                }
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        $this->UpdateStatus();
        return ExitCode::OK;
    }

    public function getStatus($variable)
    {

        switch ($variable) {
            //  รอเห็นชอบ
            case 'Pending':
                $level = 1;
                $approve_status = 'Pending';
                $status = 'Pending';
                break;

            //  หัวหน้าเห็นชอบ
            case 'Approve':
                $level = 1;
                $approve_status = 'Pass';
                $status = 'Pending';
                break;
            //หน.กลุ่มเห็นชอบ
            case 'ApproveGroup':
                $level = 2;
                $approve_status = 'Pass';
                $status = 'Pending';
                break;
            case 'Verify':
                $level = 3;
                $approve_status = 'Pass';
                $status = 'Verify';
                break;
            //ผอ.อนุมัติ
            case 'Allow':
                $level = 4;
                $approve_status = 'Pass';
                $status = 'Approve';
                break;
            //แจ้งยกเลิก
            case 'Recancel':
                $level = 0;
                $approve_status = '';
                $status = 'ReqCancel';
                break;
            //ยกเลิก
            case 'Cancel':
                $level = 0;
                $approve_status = '';
                $status = 'Cancel';
                break;
            //ไม่อนุมัติ
            case 'Disapprove':
                $level = 0;
                $approve_status = '';
                $status = 'Reject';
                break;

            default:
                $level = 0;
                $approve_status = '';
                $status = '';
                break;
        }
        return [
            'level' => $level,
            'approve_status' => $approve_status,
            'status' => $status
        ];
    }

    public function UpdateStatus()
    {
        // อัปเดตสถานะ leave จาก 'Allow' เป็น 'Approve' เฉพาะที่มี thai_year
        $count = Yii::$app->db->createCommand("UPDATE `leave` SET status = 'Approve' WHERE `thai_year` IS NOT NULL AND status = 'Allow'")->execute();
        echo "อัปเดตข้อมูลจำนวน $count รายการ \n";
        return ExitCode::OK;
    }

    public function actionCreateApproveLeave()
    {

        $leaves = Leave::find()->all();
        $num = 1;
        $total = count($leaves);
        echo "สร้างข้อมูลการอนุมัติลา...\n";
        foreach ($leaves as $item) {
            $obj1 = ['name' => 'leave', 'from_id' => $item->id, 'level' => 1, 'emp_id' => $item->data_json['approve_1'], 'status' => 'Pass'];
            $approve1 = Approve::find()->where($obj1)->one();
            if (!$approve1) {
                $newApprove1 = new Approve($obj1);
                $newApprove1->save(false);
            }

            $obj2 = ['name' => 'leave', 'from_id' => $item->id, 'level' => 2, 'emp_id' => $item->data_json['approve_2'], 'status' => 'Pass'];
            $approve2 = Approve::find()->where($obj2)->one();
            if (!$approve2) {
                $newApprove2 = new Approve($obj2);
                $newApprove2->save(false);
            }

            $obj3 = ['name' => 'leave', 'from_id' => $item->id, 'level' => 3, 'emp_id' => $item->data_json['approve_3'], 'status' => 'Pass'];
            $approve3 = Approve::find()->where($obj3)->one();
            if (!$approve3) {
                $newApprove3 = new Approve($obj3);
                $newApprove3->save(false);
            }

            $obj4 = ['name' => 'leave', 'from_id' => $item->id, 'level' => 4, 'emp_id' => $item->data_json['approve_4'], 'status' => 'Pass'];
            $approve4 = Approve::find()->where($obj4)->one();
            if (!$approve4) {
                $newApprove4 = new Approve($obj4);
                $newApprove4->save(false);
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
    }

    // ระบบห้องประชุม
    public function actionMeeting()
    {
        $this->CreateRoom();
        $sql = "SELECT r.ROOM_ID,r.ROOM_NAME,s.* FROM `room_service` s 
        LEFT JOIN room_index r ON r.ROOM_ID = s.ROOM_ID
        ORDER BY s.DATE_TIME_REQUEST ASC;";
        $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();

        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลการจองห้องประชุม...\n";
        foreach ($querys as $key => $item) {
            try {

                $emp = $this->Person($item['PERSON_REQUEST_ID']);

                $check = Meeting::find()->where([
                    'emp_id' => $emp?->id  ?? 0,
                    'room_id' => $item['ROOM_ID'],
                    'thai_year' => $item['YEAR_ID'],
                    'title' => $item['SERVICE_STORY'],
                    'date_start' => $item['DATE_BEGIN'],
                    'date_end' => $item['DATE_END'],
                    'time_start' => $item['TIME_BEGIN'],
                    'time_end' => $item['TIME_END'],
                ])->one();

                $model = $check ?? new Meeting();
                $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('MEETING' . date('ymd', strtotime($item['DATE_BEGIN'])) . '-???'));
                $model->emp_id = $emp?->id  ?? 0;
                $model->room_id = $item['ROOM_ID'];
                $model->thai_year = $item['YEAR_ID'];
                $model->title = $item['SERVICE_STORY'];
                $model->date_start = $item['DATE_BEGIN'];
                $model->date_end = $item['DATE_END'];
                $model->time_start = $item['TIME_BEGIN'];
                $model->time_end = $item['TIME_END'];
                $model->urgent = 'ปกติ';
                $model->emp_number = $item['TOTAL_PEOPLE'];
                $model->status = $this->BookingStatus($item['STATUS']);
                $model->data_json = [
                    'phone' => $item['PERSON_REQUEST_PHONE'],
                    'meeting_details' => $item['SERVICE_FOR_DETAIL'],
                    'old_data' => $item
                ];
                if ($model->save(false)) {
                }
            } catch (\Throwable $th) {
                echo 'เกิดข้อผิดพลาด : ' . $item['ID'] . " - " . $th->getMessage() . "\n";
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        return ExitCode::OK;
    }

    protected function CreateRoom()
    {
        $sql = "SELECT * FROM `room_index`";
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        foreach ($querys as $item) {
            $room = Categorise::findOne(['name' => 'meeting_room', 'code' => $item['ROOM_ID']]);
            if (!$room) {
                $newRoom = new Categorise;
                $newRoom->name = 'meeting_room';
                $newRoom->code = $item['ROOM_ID'];
                $newRoom->title = $item['ROOM_NAME'];
                $newRoom->data_json = [
                    'owner' => $this->Person($item['ROOM_PERSON_ID'])?->id,
                    "location" => "อาคารผู้ป่วยนอก ชั้น 2",
                    "seat_capacity" => $item['CONTAIN'],
                    "room_accessory" => [
                        "คอมพิวเตอร์",
                        "โปรเจคเตอร์",
                        "ไมค์ประชุม"
                    ],
                    "advance_booking" =>  "1"
                ];
                $newRoom->save(false);
                echo "สร้างห้องประชุม : " . $newRoom->title . "\n";
            }
        }
    }



    // งานซ่อมบำรุง
    public function actionRepairGeneral()
    {
        set_time_limit(0);
        $sql = 'SELECT ot.OTHER_NAME,l.LOCATION_NAME ,p.PRIORITY_NAME,
                a.ARTICLE_NUM,
                r.*
                FROM `repair_index` r
                LEFT JOIN repair_other ot ON ot.OTHER_ID = r.OTHER_ID
                LEFT JOIN repair_location_see l ON l.ID = r.LOCATION_SEE_ID
                LEFT JOIN repair_priority p ON p.PRIORITY_ID = r.PRIORITY_ID
                LEFT JOIN ar_article a ON a.ARTICLE_ID = r.ARTICLE_ID';

        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $num = 1;
        $total = count($querys);

        echo "เริ่มนำเข้าข้อมูลซ้อมบำรุง...\n";
        foreach ($querys as $item) {
            // try {
            $emp = $this->Person($item['USER_REQUEST_ID']);
            $checkModel = Helpdesk::findOne(['repair_number' => $item['REPAIR_ID']]);
            if (!$checkModel) {
                $model = new Helpdesk();
            } else {
                $model = $checkModel;
            }
            // ตรวจสอบว่ามีวันที่ส่งมาไหม
            if (!empty($item['TECH_RECEIVE_DATE']) && $item['TECH_RECEIVE_DATE'] !== '0000-00-00') {
                // รวมวันที่และเวลา
                $receiveDateTime = trim($item['TECH_RECEIVE_DATE'] . ' ' . ($item['TECH_RECEIVE_TIME'] ?: '00:00:00'));
                $model->receive_date = $receiveDateTime;
            } else {
                $model->receive_date = null; // หรือค่าเริ่มต้นอื่นๆ
            }



            // ทำความสะอาดข้อมูลก่อนใช้งาน
            $cleanItem = $this->cleanUtf8($item);
            $model->repair_group = 1;
            $model->name = 'repair';
            $model->repair_number = $item['REPAIR_ID'];
            $model->emp_id = $emp->id;
            $model->thai_year = $item['YEAR_ID'];
            $model->title = $item['REPAIR_NAME'];
            $model->created_by = $emp->user_id;
            $model->status = !empty($item['TECH_SUCCESS_DATE']) ? 'success' : $this->RepairStatus($item);
            $model->created_at = $item['DATE_TIME_REQUEST'];
            $model->date_start = $item['REPAIR_DATE'];
            $model->date_end = $item['TECH_REPAIR_DATE'];
            $model->rating = $item['REPAIR_SCORE'];
            $model->created_at = $item['DATE_TIME_REQUEST'];
            $model->receive_date = $receiveDateTime;

            $model->data_json = [
                'time_start' => $item['REPAIR_TIME'],
                'end_start' => $item['TECH_REPAIR_TIME'],
                'status' =>  $item['REPAIR_STATUS'],
                'note' => $item['SYMPTOM'],
                'phone' => '',
                'urgency' => $item['PRIORITY_NAME'],
                'urgency_name' => $item['PRIORITY_NAME'],
                'location' => $item['LOCATION_NAME'],
                'repair_type' => 'ซ่อมภายใน',
                'send_type' => 'general',
                'accept_emp_id' => '',
                'accept_name' => '',
                'accept_time' => $item['TECH_RECEIVE_DATE'] . ' ' . $item['TECH_RECEIVE_TIME'],
                'create_name' => $emp->fullname,
                'status_name' => 'ร้องขอ',
                'location_other' => '',
                'technician_req' => '0',
                'technician_name' => '',
                'old_data' => $cleanItem
            ];

            if ($model->save(false)) {

                ################## วันรับเื่อง ม###########################

                if (!empty($item['TECH_RECEIVE_DATE'])) {

                    $receiveData = [
                        'helpdesk_id' => $model->id,
                        'title' =>  $item['TECH_RECEIVE_COMMENT'],
                        'name' => 'service_record',
                        'status' => 'รับเรื่อง',
                        'created_at' => $receiveDateTime
                    ];

                    $checkServiceRecordReceived = HelpdeskDetail::findOne($receiveData);
                    if (!$checkServiceRecordReceived) {
                        $checkServiceRecordReceived = new HelpdeskDetail($receiveData);
                        $checkServiceRecordReceived->detachBehavior(0);
                        $checkServiceRecordReceived->save(false);
                    }
                }

                ############### วันที่ซ่อม #########################
                if (!empty($item['TECH_REPAIR_DATE']) && $item['TECH_REPAIR_DATE'] !== '0000-00-00') {
                    // รวมวันที่และเวลา
                    $repairDateTime = trim($item['TECH_REPAIR_DATE'] . ' ' . ($item['TECH_REPAIR_TIME'] ?: '00:00:00'));
                    $repairData = [
                        'helpdesk_id' => $model->id,
                        'name' => 'service_record',
                        'status' => $item['REPAIR_COMMENT'],
                        'created_at' => $repairDateTime
                    ];
                    $checkServiceRecordRepair = HelpdeskDetail::findOne($repairData);
                    if (!$checkServiceRecordRepair) {
                        $checkServiceRecordRepair = new HelpdeskDetail($repairData);
                        $checkServiceRecordRepair->detachBehavior(0);
                        $checkServiceRecordRepair->save(false);
                    }
                }

                ############### วันที่ซ่อมเสร็จ #########################

                if (!empty($item['TECH_SUCCESS_DATE']) && $item['TECH_SUCCESS_DATE'] !== '0000-00-00') {
                    // รวมวันที่และเวลา
                    $repairSuccessDateTime = trim($item['TECH_SUCCESS_DATE'] . ' ' . ($item['TECH_SUCCESS_TIME'] ?: '00:00:00'));
                    $repairSucessData = [
                        'helpdesk_id' => $model->id,
                        'name' => 'service_record',
                        'status' => 'ซ่อมเสร็จ',
                        'created_at' => $repairSuccessDateTime
                    ];
                    $checkServiceRecordRepair = HelpdeskDetail::findOne($repairSucessData);
                    if (!$checkServiceRecordRepair) {
                        $checkServiceRecordRepair = new HelpdeskDetail($repairSucessData);
                        $checkServiceRecordRepair->detachBehavior(0);
                        $checkServiceRecordRepair->save(false);
                    }
                }


                ################## ช่างซ่อม ###########################
                $techPerson = $this->Person($item['TECH_REPAIR_ID']);
                if ($techPerson) {
                    $repairTeamData = [
                        'helpdesk_id' => $model->id,
                        'name' => 'repair_team',
                        'emp_id' => $techPerson->id ?? 0,
                    ];
                    $checkServiceRecordTeam = HelpdeskDetail::findOne($repairTeamData);
                    if (!$checkServiceRecordTeam) {
                        $checkServiceRecordTeam = new HelpdeskDetail($repairTeamData);
                        $checkServiceRecordTeam->detachBehavior(0);
                        $checkServiceRecordTeam->save(false);
                    }
                }
                ############### สิ้นสุด วันรับเื่อง ม #########################



                // ใช้ \r เพื่อเลื่อน Cursor กลับไปต้นบรรทัด เพื่อพิมพ์ทับบรรทัดเดิม
                BaseConsole::updateProgress($num, $total);
                $num++;
                $this->TechRepair($model->id, $item['TECH_REPAIR_ID']);
                // $this->serviceItems($model, $item);
            } else {
                echo "Save Error \n";
                print_r($model->getErrors());
            }
            // } catch (\Throwable $th) {
            // }
        }
        // เมื่อเสร็จสิ้น ให้ปิด Progress Bar
        BaseConsole::endProgress();
        echo "นำเข้าข้อมูลซ่อมบำรุงเสร็จสิ้น!\n";
    }


    public function actionComputer()
    {
        $sql = 'SELECT * FROM `com_repair` r';
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        // if (BaseConsole::confirm('งานซ่อมบำรุง ' . count($querys) . ' รายการ ยืนยัน ??')) {
        $num = 1;
        $total = count($querys);
        foreach ($querys as $item) {
            // try {
            $emp = $this->Person($item['USER_REQUEST_ID']);
            $checkModel = Helpdesk::findOne(['title' => $item['SYMPTOM']]);
            if (!$checkModel) {
                $model = new Helpdesk();
            } else {
                $model = $checkModel;
            }
            $model->repair_group = 2;
            $model->name = 'repair';
            $model->emp_id = $emp->id;
            $model->thai_year = $item['YEAR_ID'];
            $model->title = $item['SYMPTOM'];
            $model->created_by = $emp->user_id;
            $model->status = $item['REPAIR_STATUS'];
            $model->created_at = $item['DATE_TIME_REQUEST'];
            $model->date_start = $item['TECH_REPAIR_DATE'];
            $model->date_end = $item['TECH_SUCCESS_DATE'];
            $model->rating = $item['FANCINESS_SCORE'];
            $model->data_json = [
                'time_start' => $item['REPAIR_TIME'],
                'end_start' => $item['TECH_REPAIR_TIME'],
                'status' => $item['REPAIR_STATUS'],
                'note' => $item['REPAIR_SUCCESS_REMARK'],
                'phone' => '',
                'urgency' => '1',
                'location' => 'งานซ่อมบำรุง',
                'repair_type' => $item['OUTSIDE_ACTIVE'] == true ? 'ซ่อมภายนอก' : 'ซ่อมภายใน',
                'send_type' => 'general',
                'accept_emp_id' => '',
                'accept_name' => '',
                'accept_time' => $item['TECH_RECEIVE_DATE'] . ' ' . $item['TECH_RECEIVE_TIME'],
                'create_name' => $emp->fullname,
                'status_name' => 'ร้องขอ',
                'location_other' => '',
                'technician_req' => '0',
                'technician_name' => '',
                'old_data' => $item
            ];
            if ($model->save(false)) {
                $this->TechRepair($model->id, $item['TECH_REPAIR_ID']);
                // $this->serviceItems($model, $item);
            } else {
                echo "Save Error \n";
                print_r($model->getErrors());
            }

            // } catch (\Throwable $th) {

            // }
        }
        // }
    }

    private function RepairStatus($data)
    {
        switch ($data['REPAIR_STATUS']) {
            case 'REQUEST':
                return 'pending';
            case 'REPAIR':
                return 'in_progress';
            case 'CANCEL':
                return 'cancel';
            default:
                return '';
        }
    }

    private function cleanUtf8($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanUtf8($value);
            }
        } elseif (is_string($data)) {
            // กรองตัวอักษรที่ไม่ใช่ UTF-8 ออก และแทนที่ด้วยอักขระว่างหรืออักขระที่ถูกต้อง
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        return $data;
    }

    public function TechRepair($id, $person_id)
    {
        try {
            $emp = $this->Person($person_id);
            $checkModel = Helpdesk::findOne(['name' => 'repair_team', 'category_id' => $id]);
            if (!$checkModel) {
                $model = new Helpdesk();
            } else {
                $model = $checkModel;
            }
            $model->emp_id = $emp->id;
            $model->name = 'repair_team';
            $model->category_id = $id;
            $model->data_json = [
                'tech_fullname' => $emp->fullname,
                'tech_position' => $emp->positionName(),
                'tech_department' => $emp->departmentName(),
            ];
            $model->save(false);
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    public function actionAsset()
    {

        // ** Query 
        $sql = "SELECT
        s.*,    
        b.*,
        c.*,
        m.*,
        mt.*,
        s_bg.*,
        s_buy.*,
        st.*,
        sd.*,
        ar_status.*,
        v.*,
        dep.*,
        l.*,
        u.*,
                ll.LOCATION_LEVEL_NAME,
        lr.LEVEL_ROOM_NAME,
        a.*
                                        FROM `ar_article`  a
                                        LEFT JOIN sup_model m ON m.MODEL_ID = a.MODEL_ID
                                        LEFT JOIN sup_size s ON s.SIZE_ID = a.SIZE_ID
                                        LEFT JOIN sup_brand b ON b.BRAND_ID = a.BRAND_ID
                                        LEFT JOIN sup_color c ON c.COLOR_ID = a.COLOR_ID
                                        LEFT JOIN sup_method mt ON mt.METHOD_ID = a.METHOD_ID
                                        LEFT JOIN sup_budget s_bg ON s_bg.BUDGET_ID = a.BUDGET_ID
                                        LEFT JOIN sup_buy s_buy ON s_buy.BUY_ID = a.BUY_ID
                                        LEFT JOIN sup_type st ON st.SUP_TYPE_ID = a.TYPE_ID
                                        LEFT JOIN sup_decline sd ON sd.DECLINE_ID = a.DECLINE_ID
                                        LEFT JOIN ar_status  ON ar_status.STATUS_ID = a.STATUS_ID  
                                        LEFT JOIN sup_vendor v ON v.VENDOR_ID = a.VENDOR_ID 
                                        LEFT JOIN sup_location l ON l.LOCATION_ID = a.LOCATION_ID
                                        LEFT JOIN sup_unit u ON u.SUP_UNIT_ID = a.UNIT_ID
                                        LEFT JOIN hr_department_sub_sub dep ON dep.HR_DEPARTMENT_SUB_SUB_ID = a.DEP_ID
                                        LEFT JOIN hr_person person ON person.ID = a.PERSON_ID
                                        LEFT JOIN sup_location_level ll ON ll.LOCATION_LEVEL_ID = a.LOCATION_LEVEL_ID
                                        LEFT JOIN sup_location_level_room lr ON lr.LEVEL_ROOM_ID = a.LEVEL_ROOM_ID
ORDER BY `st`.`SUP_TYPE_NAME` ASC;";
        //  End Query
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $num = 1;
        $total = count($querys);
        echo "เริ่มนำเข้าข้อมูลครุภัณฑ์...\n";
        foreach ($querys as $asset) {
            try {


                $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                $checkItem = Asset::findOne(['code' => $asset['ARTICLE_NAME']]);
                $model = $checkItem ? $checkItem : new Asset([
                    'ref' => $ref
                ]);

                if (!$checkItem) {
                    $this->CreateDir($ref);
                    if ($asset['IMG']) {

                        $name = time() . '.jpg';
                        file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $asset['IMG']);

                        $upload = new Uploads;
                        $upload->ref = $ref;
                        $upload->name = 'asset';
                        $upload->file_name = $name;
                        $upload->real_filename = $name;
                        $upload->type = 'jpg';
                        $upload->save(false);
                    }
                }


                // ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือไม่ ถ้าไม่มีให้เพิ่มข้อมูลลงไป
                $assetType = $asset['DECLINE_NAME'];
                $assetItemName = $asset['ARTICLE_NAME'];
                $assetCode = $asset['ARTICLE_NUM'];
                $vendorName = $asset['VENDOR_NAME'];
                $methodGet = $asset['METHOD_NAME'];
                $purchaseText = $asset['BUY_NAME'];
                $budgetType = $asset['BUDGET_NAME'];
                $departmentName = $asset['HR_DEPARTMENT_SUB_SUB_NAME'];
                $onYear = $asset['YEAR_ID'];
                $price =  $asset['PRICE_PER_UNIT'];
                $receiveDate = $asset['RECEIVE_DATE'];
                $assetStatus = $asset['STATUS_NAME'];
                $assetItem = AssetHelper::CheckAssetItem($assetType, $assetCode, $assetItemName);
                $assetType =  Categorise::findOne(['title' => $asset['DECLINE_NAME'], 'name' => 'asset_type', 'group_id' => 'EQUIP']);

                $checkAsset = $assetCode ? Asset::find()->where(['code' => $assetCode])->one() : null;
                $org = $departmentName ? Organization::find()->where(['name' => $departmentName])->one() : null;
                $model->department = $org && isset($org->id) ? $org->id : 0;  // หน่วยงานภายในตามโครงสร้าง
                // สมมติว่าคุณมี Model ชื่อ YourModel
                $model = $checkAsset ?? new Asset();
                $model->asset_group_id = 4;
                $model->asset_type_id = isset($assetType->code) ? $assetType->code : null;
                $model->asset_item_id = isset($assetItem) ? $assetItem->code : null;
                $model->code = $assetCode;
                $model->owner = $this->Person($asset['PERSON_ID'])?->id ?? 0;
                $data_json = [
                    'fsn_old' => $assetCode,
                    'brand' => $asset['BRAND_NAME'],
                    'asset_model' => $asset['MODEL_NAME'],
                    'color_name' => $asset['COLOR_NAME'],
                    'serial_number' => $asset['SERIAL_NO'],
                    'location' => $asset['LOCATION_NAME'] . ' ' . $asset['LOCATION_LEVEL_NAME'] . ' ' . $asset['LEVEL_ROOM_NAME'],
                    'unit' => $asset['SUP_UNIT_NAME'],
                    'asset_options' => $asset['ARTICLE_PROP'],
                    'expire_date' => $asset['EXPIRE_DATE'],
                    'vendor_id' => $vendorName ? AssetHelper::findByName('vendor', $vendorName) : null,
                    'method_get' => $methodGet ? AssetHelper::findByName('method_get', $methodGet) : null,
                    'budget_type' => $budgetType ? AssetHelper::findByName('budget_type', $budgetType) : null,
                    'asset_type_text' => $assetType,
                    'method_get_text' => $methodGet,
                    'purchase_text' => $purchaseText,
                    'budget_type_text' => $budgetType,
                    'department_name' => $departmentName,
                ];
                $model->asset_name = $asset['ARTICLE_NAME'];
                $model->fsn_number = $asset['SUP_FSN'];
                $model->purchase = $purchaseText ? AssetHelper::findByName('purchase', $purchaseText) : null;  // วิธีการได้มา
                $model->on_year = $onYear;
                $model->price = $price;
                $model->receive_date = $receiveDate; // วันที่รับเข้า
                // $model->asset_status = $assetStatus ? AssetHelper::findByName('asset_status', $assetStatus) : null;  // วิธีการได้มา
                $model->asset_status = $asset['STATUS_ID'];


                $model->data_json = ArrayHelper::merge($model->data_json, $data_json, $this->cleanUtf8($asset));
                $model->save(false);
                BaseConsole::updateProgress($num, $total);
                $num++;
                //code...
            } catch (\Throwable $th) {
                echo "เกิดข้อผิดพลาด : " . $asset['ARTICLE_ID'] . " - " . $th->getMessage() . "\n";
                exit;
            }
        }
        echo "นำเข้าข้อมูลครุภัณฑ์เสร็จสิ้น!\n";
    }

}
