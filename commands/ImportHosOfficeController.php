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
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveEntitlements;
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
            $this->actionLeaveAll();
            $this->actionDevelopment();
            $this->actionCreateMoney();
            $this->actionVehicle();
            $this->actionRefer();
            $this->actionMeeting();
            $this->actionRepairGeneral();
            $this->actionComputer();
            $this->actionAsset();
            $this->actionMaterial();
        } else {
            echo "user typed no\n";
        }
    }
    public function actionSync()
    {
        $this->actionEmployee();
        $this->actionUpdatePosition();
        $this->actionLeaveAll();
        $this->actionDevelopment();
        $this->actionCreateMoney();
        $this->actionVehicle();
        $this->actionRefer();
        $this->actionMeeting();
        $this->actionRepairGeneral();
        $this->actionComputer();
        $this->actionAsset();
        $this->actionMaterial();
    }

    /**
     * นำเข้าระบบลาจาก HosOffice ให้ครบในคำสั่งเดียว
     *
     * ลำดับ: ใบลา -> สิทธิลาพักผ่อน -> ประวัติอนุมัติ -> label การอนุมัติ
     */
    public function actionLeaveAll($limit = null)
    {
        echo "=== 1/4 นำเข้าใบลา ===\n";
        if ($this->actionLeave($limit) !== ExitCode::OK) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\n=== 2/4 นำเข้าสิทธิลาพักผ่อน ===\n";
        if ($this->actionLeaveEntitlements($limit) !== ExitCode::OK) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\n=== 3/4 สร้างประวัติการอนุมัติ ===\n";
        if ($this->actionCreateApproveLeave($limit) !== ExitCode::OK) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\n=== 4/4 ปรับข้อมูลกำกับขั้นอนุมัติ ===\n";
        if ($this->actionFixApproveLabel() !== ExitCode::OK) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\nนำเข้าระบบลาจาก HosOffice เสร็จสมบูรณ์\n";
        return ExitCode::OK;
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
    public function actionEmployee($limit = null)
    {
        $sqlPerson = "SELECT
                    p.ID AS HOSOFFICE_ID,
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
                LEFT JOIN hr_level ON hr_level.HR_LEVEL_ID = p.HR_LEVEL_ID
                ORDER BY p.ID";
        $sqlPerson .= $this->limitSql($limit);
        $querys = \Yii::$app->db2->createCommand($sqlPerson)->queryAll();
        $existingBySourceId = $this->importedRecordIds(Employees::class, 'HOSOFFICE_ID');
        $num = 1;
        $total = count($querys);
        $created = 0;
        $updated = 0;
        $failed = 0;
        echo "เริ่มนำเข้าข้อมูลบุคลากร...\n";
        foreach ($querys as $person) {
            try {
                $sourceId = (string) $person['HOSOFFICE_ID'];
                $model = isset($existingBySourceId[$sourceId])
                    ? Employees::findOne($existingBySourceId[$sourceId])
                    : $this->sourceFallbackQuery(
                        Employees::find()->where(['cid' => $person['cid']]),
                        'HOSOFFICE_ID',
                        $sourceId
                    )->one();
                $isNew = $model === null;
                $mappedType = $this->MapPositionType($person);
                if ($isNew) {
                    $model = new Employees();
                    $model->user_id = 0;
                    $model->ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                    $this->CreateDir($model->ref);
                }

                if ($isNew && $person['image']) {
                    $name = time() . '.jpg';
                    file_put_contents(\Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $model->ref . '/' . $name, $person['image']);

                    $upload = new Uploads();
                    $upload->ref = $model->ref;
                    $upload->name = 'avatar';
                    $upload->file_name = $name;
                    $upload->real_filename = $name;
                    $upload->type = 'jpg';
                    $upload->save(false);
                }

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
                $personData = $person;
                unset($personData['image']);
                $model->data_json = $this->prepareImportedDataJson(
                    Employees::class,
                    $model->data_json,
                    $this->cleanUtf8($personData)
                );

                if ($model->save(false)) {
                    $existingBySourceId[$sourceId] = $model->id;
                    $isNew ? $created++ : $updated++;
                    $this->Family($model->id, $model->cid);
                } else {
                    $failed++;
                    echo "บันทึกบุคลากร CID {$person['cid']} ไม่สำเร็จ\n";
                }
            } catch (\Throwable $th) {
                $failed++;
                echo "นำเข้าบุคลากร CID {$person['cid']} ไม่สำเร็จ: {$th->getMessage()}\n";
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        echo "\nสรุปบุคลากร: สร้าง {$created}, อัปเดต {$updated}, ผิดพลาด {$failed}\n";
        return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }


    public function MapEmployeeStatus($data)
    {
        if ($data['status_name'] == 'ทำงานปกติ') {
            return 1;
        } else {
            $data = Categorise::findOne(['name' => 'emp_status', 'title' => $data['status_name']]);
            if ($data) {
                return $data->code;
            } else {
                return 0;
            }
        }
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

    public function actionUpdatePosition($limit = null)
    {
        // ใช้ batch() หากพนักงานมีจำนวนมากเพื่อประหยัด Memory
        $query = Employees::find()->where(['status' => 1])->orderBy(['id' => SORT_ASC]);
        if ($limit !== null) {
            $query->limit(max(1, (int) $limit));
        }
        $querys = $query->all();
        $num = 1;
        $total = count($querys);
        echo "เริ่มอัพเดทข้อมูลตำแหน่งงาน...\n";

        foreach ($querys as $emp) {
            $data = $this->decodeDataJson($emp->data_json);
            // 1. ค้นหาหรือสร้าง Model ใหม่
            $empDetail = EmployeeDetail::findOne(['emp_id' => $emp->id, 'name' => 'position'])
                ?? new EmployeeDetail();

            if (!$empDetail->ref) {
                $empDetail->ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
            }


            $empDetail->emp_id = $emp->id;
            $empDetail->name = 'position';
            $empDetail->data_json = $this->prepareImportedDataJson(EmployeeDetail::class, $empDetail->data_json, [
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
        $sql = 'SELECT f.`ID` AS `HOSOFFICE_FAMILY_ID`,p.`HR_FNAME`,p.`HR_LNAME`,p.`HR_CID`,f.`NAME`,f.`TYPE`,f.`PHONE`,f.`ADDRESS` FROM hr_tr_family f
            LEFT JOIN hr_person p ON p.`ID` = f.`PERSON_ID`
            WHERE p.`HR_CID` = :cid';
        $querys = \Yii::$app
            ->db2
            ->createCommand($sql)
            ->bindParam(':cid', $cid)
            ->queryAll();

        $existingBySourceId = $this->importedRecordIds(
            EmployeeDetail::class,
            'HOSOFFICE_FAMILY_ID',
            ['emp_id' => $emp_id, 'name' => 'family']
        );
        foreach ($querys as $query) {
            $dataJson = [
                'HOSOFFICE_FAMILY_ID' => $query['HOSOFFICE_FAMILY_ID'],
                'fname' => isset(explode(' ', $query['NAME'], 2)[0]) ? explode(' ', $query['NAME'], 2)[0] : '',
                'lname' => isset(explode(' ', $query['NAME'], 2)[1]) ? explode(' ', $query['NAME'], 2)[1] : '',
                'family_relation' => $query['TYPE'],
                'phone' => $query['PHONE'],
                'address' => $query['ADDRESS'],
            ];

            $sourceId = (string) $query['HOSOFFICE_FAMILY_ID'];
            $model = isset($existingBySourceId[$sourceId])
                ? EmployeeDetail::findOne($existingBySourceId[$sourceId])
                : new EmployeeDetail();
            $model->name = 'family';
            $model->emp_id = $emp_id;
            $model->data_json = $this->prepareImportedDataJson(
                EmployeeDetail::class,
                $model->data_json,
                $this->cleanUtf8($dataJson)
            );
            if ($model->save(false)) {
                $existingBySourceId[$sourceId] = $model->id;
            }

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
    public function actionVehicle($limit = null)
    {
        // ระบบจองรถย์
        $sql = 'SELECT
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
                car_location l ON l.LOCATION_ID = v.RESERVE_LOCATION_ID
            LEFT JOIN 
                car_priority pr ON CAST(pr.PRIORITY_ID AS UNSIGNED) = v.PRIORITY_ID
            ORDER BY v.RESERVE_END_DATE ASC, v.RESERVE_ID ASC';
        $sql .= $this->limitSql($limit);
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $existingBySourceId = $this->importedRecordIds(
            Vehicle::class,
            'RESERVE_ID',
            ['vehicle_type_id' => 'official']
        );
        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลจองรถ...\n";

        foreach ($querys as $key => $item) {
            if ($item['RESERVE_BEGIN_DATE'] && $item['RESERVE_END_DATE']) {
                $emp = $this->Person($item['RESERVE_PERSON_ID']);
                $sourceId = (string) $item['RESERVE_ID'];
                $check = isset($existingBySourceId[$sourceId])
                    ? Vehicle::findOne($existingBySourceId[$sourceId])
                    : null;

                // รองรับข้อมูลที่เคยนำเข้าก่อนบันทึก source ID ลง data_json
                if (!$check) {
                    $check = $this->sourceFallbackQuery(
                        Vehicle::find()->where([
                            'reason' => $item['RESERVE_NAME'],
                            'date_start' => $item['RESERVE_BEGIN_DATE'],
                            'date_end' => $item['RESERVE_END_DATE'],
                            'time_start' => $item['RESERVE_BEGIN_TIME'],
                            'time_end' => $item['RESERVE_END_TIME'],
                        ]),
                        'RESERVE_ID',
                        $sourceId
                    )->one();
                }

                $model = $check ?? new Vehicle();
                $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('CAR' . date('ymd') . '-???'));
                $model->thai_year = AppHelper::YearBudget($item['RESERVE_BEGIN_DATE']);
                $model->reason = $item['RESERVE_NAME'];
                $model->vehicle_type_id = 'official';
                $model->refer_type = 'normal';
                $model->go_type = 1;
                $model->urgent = $item['PRIORITY_NAME'];
                $model->location = $this->checkLocation($item['LOCATION_NAME']) ?? '-';
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

                $model->data_json = $this->prepareImportedDataJson(
                    Vehicle::class,
                    $model->data_json,
                    $this->cleanUtf8($item)
                );

                if ($model->save(false)) {
                    $existingBySourceId[$sourceId] = $model->id;
                    $this->createDetail($model, $item);
                }
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        return ExitCode::OK;
    }

    //สร้างการจัดสรรถยนต์
    protected function createDetail($model, $item)
    {
        if (!$model->date_start || !$model->date_end) {
            return;
        }

        $driverId = $this->Person($item['CAR_DRIVER_SET_ID'] ?? null)?->id;
        $dateRanges = [[$model->date_start, $model->date_end]];
        if ((string) $model->go_type === '1') {
            $startDate = new DateTime($model->date_start);
            $endDate = (new DateTime($model->date_end))->modify('+1 day');
            $dateRanges = [];
            foreach (new DatePeriod($startDate, new DateInterval('P1D'), $endDate) as $date) {
                $dateRanges[] = [$date->format('Y-m-d'), $date->format('Y-m-d')];
            }
        }

        foreach ($dateRanges as [$dateStart, $dateEnd]) {
            $detail = VehicleDetail::findOne([
                'vehicle_id' => $model->id,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ]) ?? new VehicleDetail();
            $detail->vehicle_id = $model->id;
            $detail->date_start = $dateStart;
            $detail->date_end = $dateEnd;
            $detail->license_plate = $model->license_plate;
            $detail->mileage_start = $item['CAR_NUMBER_BEGIN'] ?? null;
            $detail->mileage_end = $item['CAR_NUMBER_BACK'] ?? null;
            $detail->oil_price = $item['OIL_IN_BATH'] ?? null;
            $detail->oil_liter = $item['OIL_IN_LIT'] ?? null;
            $detail->driver_id = $driverId;
            $detail->status = $this->BookingStatus($model->status);
            $detail->save(false);
        }

        if ($model->vehicle_type_id === 'personal') {
            $approve = Approve::findOne([
                'from_id' => $model->id,
                'name' => 'vehicle',
                'level' => 1,
            ]) ?? new Approve();
            $info = SiteHelper::getInfo();
            $approve->from_id = $model->id;
            $approve->name = 'vehicle';
            $approve->emp_id = $info['director']->id ?? null;
            $approve->title = 'ขออนุมัติใช้รถ';
            $approve->data_json = $this->prepareImportedDataJson(
                Approve::class,
                $approve->data_json,
                ['label' => 'อนุมัติ']
            );
            $approve->level = 1;
            $approve->status = 'Pending';
            $approve->save(false);
        }
    }
    // รถพยาบาล
    public function actionRefer($limit = null)
    {
        $sql = "SELECT rt.REFER_TYPE_NAME,c.CAR_REG,l.RECORD_LOCATION_NAME,r.* FROM `car_refer` r
        LEFT JOIN car_index c ON c.CAR_ID = r.CAR_ID
        LEFT JOIN record_location l ON l.RECORD_LOCATION_ID = r.REFER_LOCATION_GO_ID
        LEFT JOIN car_refer_type rt ON rt.REFER_TYPE_ID = r.REFER_TYPE_ID
        WHERE r.OUT_DATE IS NOT NULL
        ORDER BY r.ID";
        $sql .= $this->limitSql($limit);
        // นำวันลา
        $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();
        $existingBySourceId = $this->importedRecordIds(
            Vehicle::class,
            'ID',
            ['vehicle_type_id' => 'ambulance']
        );

        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            try {

                $emp = $this->Person($item['USER_REQUEST_ID']);
                $sourceId = (string) $item['ID'];
                $check = isset($existingBySourceId[$sourceId])
                    ? Vehicle::findOne($existingBySourceId[$sourceId])
                    : null;

                if (!$check) {
                    $check = $this->sourceFallbackQuery(
                        Vehicle::find()->where([
                            'vehicle_type_id' => 'ambulance',
                            'reason' => $item['REFER_TYPE_NAME'],
                            'date_start' => $item['OUT_DATE'],
                            'date_end' => $item['BACK_DATE'],
                            'time_start' => $item['OUT_TIME'],
                            'time_end' => $item['BACK_TIME'],
                        ]),
                        'ID',
                        $sourceId
                    )->one();
                }

                $model = $check ?? new Vehicle();
                $check ?? ($model->code  =   \mdm\autonumber\AutoNumber::generate('AMB' . date('ymd') . '-???'));
                $model->thai_year = AppHelper::YearBudget($item['OUT_DATE']);
                $model->reason = $item['REFER_TYPE_NAME'] ?? '-';
                $model->vehicle_type_id = 'ambulance'; //รถพยาบาล
                $model->refer_type = $this->referType($item['REFER_TYPE_NAME']) ?? '-';
                $model->go_type = 1;
                $model->urgent = 'ปกติ';
                $model->location = $this->checkLocation($item['RECORD_LOCATION_NAME']) ?? '-';
                $model->status = 'Pass';
                $model->leader_id = 0;
                $model->date_start = $item['OUT_DATE'];
                $model->date_end = ($item['BACK_DATE'] === '0000-00-00' || empty($item['BACK_DATE'])) ? null : $item['BACK_DATE'];
                $model->time_start = $item['OUT_TIME'] ?? '00:00';
                $model->time_end = $item['BACK_TIME'] ?? '00:00';
                $model->emp_id = $emp->id ?? 0;
                $model->license_plate = $item['CAR_REG'];

                $model->data_json = $this->prepareImportedDataJson(
                    Vehicle::class,
                    $model->data_json,
                    $this->cleanUtf8($item)
                );

                if ($model->save(false)) {
                    $existingBySourceId[$sourceId] = $model->id;
                    $this->createDetailRefer($model, $item);
                }
            } catch (\Throwable $th) {
                echo "Error processing item: " . json_encode($item) . "\n";
                echo "Exception: " . $th->getMessage() . "\n";
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        return ExitCode::OK;
    }

    //สร้างการจัดสรรถยนต์
    protected function createDetailRefer($model, $item)
    {
        if ($model->date_start && $model->date_end) {
            $detail = VehicleDetail::findOne([
                'vehicle_id' => $model->id,
                'date_start' => $model->date_start,
                'date_end' => $model->date_end,
            ]) ?? new VehicleDetail();
            $detail->date_start = $model->date_start;
            $detail->date_end = $model->date_end;
            $detail->vehicle_id = $model->id;
            $detail->license_plate = $model->license_plate;
            $detail->mileage_start = $item['CAR_GO_MILE'] ?? null;
            $detail->mileage_end = $item['CAR_BACK_MILE'] ?? null;
            $detail->oil_price = $item['ADD_OIL_BATH'] ?? null;
            $detail->oil_liter = $item['ADD_OIL_LIT'] ?? null;
            $detail->driver_id = $this->Person($item['DRIVER_ID'] ?? null)?->id ?? 0;
            $detail->status = $this->BookingStatus($model->status);
            $detail->save(false);
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
            return 'Success';
        } else if ($status == 'ALLOCATE') {
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
        $locationName = trim((string) $locationName);
        if ($locationName === '') {
            return null;
        }

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
    public function actionDevelopment($limit = null)
    {
        $sql = 'SELECT 
m.RECORD_MONEY_NAME,
rl.RECORD_LEVEL_NAME,
                car.CAR_NAME,
                org.RECORD_ORG_NAME,
                go.RECORD_GO_NAME,l.LOCATION_NAME,
                v.RECORD_VEHICLE_NAME,
                lp.*,
                i.*
                FROM `record_index` i
                LEFT JOIN record_go go ON go.RECORD_GO_ID = i.RECORD_GO_ID
                LEFT JOIN record_org_location l ON l.LOCATION_ID = i.RECORD_LOCATION_ID
                LEFT JOIN record_org org ON org.RECORD_ORG_ID = i.RECORD_ORG_ID
                LEFT JOIN record_vehicle v ON v.RECORD_VEHICLE_ID = i.RECORD_VEHICLE_ID
                LEFT JOIN record_type t ON t.RECORD_TYPE_ID = i.RECORD_TYPE_ID
                LEFT JOIN record_car car ON car.CAR_ID = i.CAR
                LEFT JOIN record_location_prov lp ON lp.LOCATION_PROV_ID = i.LOCATION_PROV_ID
                LEFT JOIN record_level rl ON rl.ID = i.RECORD_LEVEL_ID
                LEFT JOIN record_money m ON m.RECORD_MONEY_ID = i.RECORD_MONEY_ID
                ORDER BY i.ID';
        $sql .= $this->limitSql($limit);

        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $existingBySourceId = $this->importedRecordIds(Development::class, 'ID');

        // if (BaseConsole::confirm('การพัฒนา ' . count($querys) . ' รายการ ยืนยัน ??')) {
        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลการพัฒนา...\n";
        foreach ($querys as $item) {
            $sourceId = (string) $item['ID'];
            $model = isset($existingBySourceId[$sourceId])
                ? Development::findOne($existingBySourceId[$sourceId])
                : null;
            if (!$model) {
                $model = $this->sourceFallbackQuery(
                    Development::find()->where(['topic' => $item['RECORD_HEAD_USE']]),
                    'ID',
                    $sourceId
                )->one()
                    ?? new Development();
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

                $model->leader_id = $this->Person($item['LEADER_HR_ID'])?->id ?? 0;
                $model->leader_group_id = $this->Person($item['HR_DEPART_ID'])?->id ?? 0;
                $this->checkLocation($item['LOCATION_NAME']);
                $this->checkLocation($item['RECORD_ORG_NAME']);
                $dataJson = [
                    'license_plate' => $item['PRIVATE_CAR_REG'],
                    'development_go_type_name' => $item['RECORD_GO_NAME'],
                    'location_org' => $item['RECORD_ORG_NAME'],
                    'location' => $item['LOCATION_NAME'],
                    'province_name' => $item['PROVINCE_NAME'],
                    'location_org_type' => ($item['LOCATION_PROV_NAME'] == 'นอกจังหวัด' ? 'ต่างจังหวัด' : $item['LOCATION_PROV_NAME']),
                    'vehicle_time_start' => $item['TIME_GO'],
                    'vehicle_time_end' => $item['TIME_BACK'],
                    'development_level_name' => $item['RECORD_LEVEL_NAME'],
                    'time_slot' => $this->getDayTypeKey($item['DAY_TYPE_ID']),
                    'claim_type_name' => $item['RECORD_MONEY_NAME'],
                ];
                $model->data_json = $this->prepareImportedDataJson(
                    Development::class,
                    $model->data_json,
                    $dataJson,
                    $this->cleanUtf8($item)
                );
                $model->vehicle_type_id = $this->mapVehicleType($item['CAR_NAME'])['code'] ?? '';
                if ($model->save(false)) {
                    $existingBySourceId[$sourceId] = $model->id;
                    $this->creteDetailMember($model, $item['ID']);
                    $this->creteDevApprove($model);
                }
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
    }

//ประเภทวัน
private function getDayTypeKey($input) {
    // กำหนดโครงสร้างการ Map ข้อมูล
    $map = [
        '01' => 'เต็มวัน',
        '02' => 'ครึ่งวันเช้า',
        '03' => 'ครึ่งวันบ่าย',
        'เต็มวัน' => 'เต็มวัน',
        'ครึ่งวัน(เช้า)' => 'ครึ่งวันเช้า',
        'ครึ่งวัน(บ่าย)' => 'ครึ่งวันบ่าย'
    ];

    // คืนค่าตาม Key ที่พบ ถ้าไม่พบให้คืนค่าว่าง หรือตาม Input เดิม
    return $map[$input] ?? $input;
}

/**
 * ฟังก์ชันสำหรับแปลงประเภทรถข้ามระบบ
 * @param string $input ข้อมูลขาเข้า (ID, ชื่อไทย, หรือ Code)
 * @return array|null คืนค่าเป็น Array ของข้อมูลที่เกี่ยวข้องทั้งหมด
 */
private function mapVehicleType($input) {
    // กำหนด Master Data สำหรับการ Mapping
    // [CAR_ID, CAR_NAME (กลุ่มใหญ่), Title (ชื่อเต็ม), Code (ระบบหลัก)]
    $masterData = [
        ['id' => '01', 'group' => 'รถราชการ', 'title' => 'รถยนต์ราชการ', 'code' => 'official'],
        ['id' => '02', 'group' => 'รถส่วนตัว', 'title' => 'รถยนต์ส่วนตัว', 'code' => 'personal'],
        ['id' => '03', 'group' => 'รถประจำทาง', 'title' => 'รถไฟ/เครื่องบิน/เรือ', 'code' => 'transport'], 
        ['id' => '04', 'group' => 'อื่นๆ', 'title' => 'อื่นๆ', 'code' => 'other']
    ];

    foreach ($masterData as $item) {
        if ($input === $item['id'] || $input === $item['group'] || $input === $item['title'] || $input === $item['code']) {
            return $item;
        }
    }

    return null; // กรณีไม่พบข้อมูล
}


    // นำเข้าส่วนของคณะที่ไปด้วยกัน
    protected function creteDetailMember($data, $sourceRecordId)
    {

        $sql = "SELECT * FROM `record_index` i 
            LEFT JOIN record_index_person p ON p.RECORD_ID = i.ID 
            WHERE p.RECORD_ID = :record_id";

        $querys = Yii::$app->db2->createCommand($sql)
            ->bindValue(':record_id', $sourceRecordId)
            ->queryAll();
        foreach ($querys as $item) {
            try {


                $empId = $this->Person($item['HR_PERSON_ID'])->id;
                $check = DevelopmentDetail::findOne(['development_id' => $data->id, 'name' => 'member', 'emp_id' => $empId]);

                if (!$check) {
                    $model = new DevelopmentDetail();
                } else {
                    $model = $check;
                }
                $model->development_id = $data->id;
                $model->name = 'member';
                $model->emp_id = $empId;
                $model->save(false);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    protected function creteDevApprove($model)
    {
        $checkApprove1 = Approve::findOne(['from_id' => $model->id, 'name' => 'development', 'level' => 1]);
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
        $approve1->data_json = $this->prepareImportedDataJson(
            Approve::class,
            $approve1->data_json,
            ['topic' => 'เห็นชอบ', 'approve_date' => null]
        );
        $approve1->status = $model->status;
        $approve1->save(false);

        $checkApprove2 = Approve::findOne(['from_id' => $model->id, 'name' => 'development', 'level' => 2]);
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
        $approve2->data_json = $this->prepareImportedDataJson(
            Approve::class,
            $approve2->data_json,
            ['topic' => 'เห็นชอบ', 'approve_date' => null]
        );
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

    public function actionCreateMoney($limit = null)
    {
        $sql = 'SELECT MIN(m.ID) AS ID,
                       m.RECORD_ID,
                       i.RECORD_HEAD_USE,
                       m.MONEY_ID,
                       SUM(m.SUMMONEY) AS SUMMONEY,
                       GROUP_CONCAT(m.ID ORDER BY m.ID) AS HOSOFFICE_MONEY_IDS
                FROM `record_index_money` m
                INNER JOIN record_index i ON i.ID = m.RECORD_ID
                WHERE m.SUMMONEY IS NOT NULL
                GROUP BY m.RECORD_ID, i.RECORD_HEAD_USE, m.MONEY_ID
                ORDER BY MIN(m.ID)';
        $sql .= $this->limitSql($limit);
        $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();
        $developmentBySourceId = $this->importedRecordIds(Development::class, 'ID');

        if (count($querys) > 0) {
            foreach ($querys as $item) {

                // try {

                $developmentId = $developmentBySourceId[(string) $item['RECORD_ID']] ?? null;
                $checkModel = $developmentId
                    ? Development::findOne($developmentId)
                    : $this->sourceFallbackQuery(
                        Development::find()->where(['topic' => $item['RECORD_HEAD_USE']]),
                        'ID',
                        $item['RECORD_ID']
                    )->one();
                if (!$checkModel) {
                    echo "ข้ามค่าใช้จ่าย ID {$item['ID']}: ไม่พบทะเบียนไปราชการต้นทาง {$item['RECORD_ID']}\n";
                    continue;
                }
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
                $model->data_json = $this->prepareImportedDataJson(
                    DevelopmentDetail::class,
                    $model->data_json,
                    $this->cleanUtf8($item)
                );
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
    public function actionLeave($limit = null)
    {
        $limitSql = $this->limitSql($limit);
        $querys = Yii::$app->db2->createCommand('SELECT
                    lr.ID, lr.LEAVE_YEAR_ID, lr.LEAVE_BECAUSE,
                    lr.LEAVE_DATE_BEGIN, lr.LEAVE_DATE_END, lr.LEAVE_DATE_SUM,
                    lr.DAY_TYPE_ID, lr.LEAVE_CONTACT, lr.LEAVE_DATETIME_REGIS,
                    lr.LEAVE_TYPE_CODE, lr.LEAVE_PERSON_ID, lr.LEAVE_PERSON_CODE,
                    lr.LEAVE_PERSON_FULLNAME, lr.LEAVE_STATUS_CODE,
                    lr.LEAVE_CONTACT_PHONE, lr.LEAVE_WORK_SEND, lr.LEAVE_WORK_SEND_ID,
                    lr.LEADER_DEP_PERSON_ID, lr.LEADER_PERSON_ID,
                    lr.LEADER_PERSON_NAME, lr.LEADER_PERSON_POSITION,
                    lr.USER_CONFIRM_CHECK_ID, lr.LEAVE_ACCEPT_BY_ID,
                    lr.LEAVE_ACCEPT_DATETIME, lr.USER_CONFIRM_CHECK_DATE,
                    lr.TOP_LEADER_AC_ID, lr.TOP_LEADER_AC_NAME,
                    lr.TOP_LEADER_AC_DATE, lr.TOP_LEADER_AC_DATE_TIME,
                    lr.LOCATION_ID, lr.WORK_DO, lr.LEAVE_SUM_ALL,
                    lr.LEAVE_SUM_HOLIDAY, lr.LEAVE_SUM_SETSUN,
                    lt.LEAVE_TYPE_ID, lt.LEAVE_TYPE_NAME,
                    ll.LOCATION_NAME
                    FROM leave_register lr
                    LEFT JOIN leave_type lt ON lr.LEAVE_TYPE_CODE = lt.LEAVE_TYPE_ID
                    LEFT JOIN leave_location ll ON lr.LOCATION_ID = ll.LOCATION_ID
                    ORDER BY lr.ID DESC' . $limitSql)
            ->queryAll();
        $num = 1;
        $total = count($querys);
        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $existingBySourceId = $this->importedRecordIds(Leave::class, 'id');
        echo "นำเข้าข้อมูลลา...\n";

        foreach ($querys as $key => $item) {
            try {
                $emp = Employees::findOne(['cid' => $item['LEAVE_PERSON_CODE']]);
                if (!$emp) {
                    $skipped++;
                    echo "\nข้าม ID {$item['ID']}: ไม่พบพนักงาน CID {$item['LEAVE_PERSON_CODE']}\n";
                    BaseConsole::updateProgress($num, $total);
                    $num++;
                    continue;
                }

                $sendwork = $this->Person($item['LEAVE_WORK_SEND_ID']);
                $leaderLevel1 = $this->Person($item['LEADER_DEP_PERSON_ID']);
                $leaderLevel2 = $this->Person($item['LEADER_PERSON_ID']);
                $userCheckId = $this->Person($item['USER_CONFIRM_CHECK_ID']);
                $ApproveDirector = $this->Person($item['TOP_LEADER_AC_ID']);

                $sourceId = (string) $item['ID'];
                $checkLeave = isset($existingBySourceId[$sourceId])
                    ? Leave::findOne($existingBySourceId[$sourceId])
                    : null;

                // รองรับข้อมูลเก่าที่ยังไม่มี source ID ใน data_json
                if (!$checkLeave) {
                    $checkLeave = $this->sourceFallbackQuery(
                        Leave::find()->where([
                            'emp_id' =>  $emp->id,
                            'thai_year' => $item['LEAVE_YEAR_ID'],
                            'date_start' => $item['LEAVE_DATE_BEGIN'],
                            'date_end' => $item['LEAVE_DATE_END'],
                        ])->andWhere(['json_extract(data_json, "$.cid")' => $item['LEAVE_PERSON_CODE']]),
                        'id',
                        $sourceId
                    )->one();
                }

                $leaveType = Categorise::findOne(['name' => 'leave_type', 'title' => $item['LEAVE_TYPE_NAME']]);

                $leave = $checkLeave ?? new Leave();


                $leave->leave_type_id = $leaveType ? $leaveType->code : '';
                $leave->emp_id = $emp ? $emp->id : 0;
                $leave->thai_year = $item['LEAVE_YEAR_ID'];
                $leave->date_start = $item['LEAVE_DATE_BEGIN'];
                $leave->date_end = $item['LEAVE_DATE_END'];
                // ใช้โค้ดดิบจาก leave_register (LEAVE_STATUS_CODE) — ตาราง leave_status มีแค่ Z ทำให้ join แล้ว STATUS_CODE เป็น NULL
                $leave->status = $this->getStatus($item['LEAVE_STATUS_CODE'])['status'];

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
                    'status_code' => $item['LEAVE_STATUS_CODE'],
                    'status_name' => $this->leaveStatusNameTh($item['LEAVE_STATUS_CODE']),
                    // ขั้นตอนที่ "ผ่านจริง" จากระบบต้นทาง (ใช้สร้างการอนุมัติใน create-approve-leave)
                    'milestone_leader' => empty($item['LEAVE_ACCEPT_BY_ID']) ? 0 : 1,     // หัวหน้ารับทราบ/เห็นชอบ
                    'milestone_check' => empty($item['USER_CONFIRM_CHECK_ID']) ? 0 : 1,   // ผู้ตรวจสอบ
                    'milestone_director' => empty($item['TOP_LEADER_AC_ID']) ? 0 : 1,     // ผอ.อนุมัติ
                    'leader_approved_at' => $item['LEAVE_ACCEPT_DATETIME'],
                    'check_approved_at' => $item['USER_CONFIRM_CHECK_DATE'],
                    'director_approved_at' => $item['TOP_LEADER_AC_DATE_TIME'] ?: $item['TOP_LEADER_AC_DATE'],
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
                    'leader' => $leaderLevel2?->id ?? 0,
                    'leader_person_name' => $item['LEADER_PERSON_NAME'],
                    'leader_person_position' => $item['LEADER_PERSON_POSITION'],
                ];
                $leave->data_json = $this->prepareImportedDataJson(
                    Leave::class,
                    $leave->data_json,
                    $leaveJson,
                    $this->cleanUtf8($item)
                );

                if ($leave->save(false)) {
                    $imported++;
                    $existingBySourceId[$sourceId] = $leave->id;
                    // $this->CreateApprove($item);
                }
                //code...
            } catch (\Throwable $th) {
                $failed++;
                $sourceId = $item['ID'] ?? '-';
                echo "\nนำเข้า ID {$sourceId} ไม่สำเร็จ: {$th->getMessage()}\n";
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        $this->UpdateStatus();
        echo "\nสรุป: สำเร็จ {$imported}, ข้าม {$skipped}, ผิดพลาด {$failed}, ทั้งหมด {$total} รายการ\n";
        return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * Upsert สิทธิลาพักผ่อนจาก HosOffice.leave_over โดยจับคู่พนักงานด้วย CID
     */
    public function actionLeaveEntitlements($limit = null)
    {
        $sql = "SELECT
                    lo.*,
                    p.HR_CID,
                    ly.DAY_PER_YEAR
                FROM leave_over lo
                INNER JOIN hr_person p ON p.ID = lo.PERSON_ID
                LEFT JOIN leave_year ly ON ly.LEAVE_YEAR_ID = lo.OVER_YEAR_ID
                WHERE CAST(lo.OVER_YEAR_ID AS UNSIGNED) > 0
                ORDER BY CAST(lo.OVER_YEAR_ID AS UNSIGNED), lo.ID";
        $sql .= $this->limitSql($limit);
        $sourceRows = Yii::$app->db2->createCommand($sql)->queryAll();

        $employeesByCid = [];
        foreach (Employees::find()->all() as $employee) {
            $cid = trim((string) $employee->cid);
            if ($cid !== '') {
                $employeesByCid[$cid] = $employee;
            }
        }

        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $skipped = 0;
        $failed = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($sourceRows as $row) {
                $cid = trim((string) $row['HR_CID']);
                $employee = $employeesByCid[$cid] ?? null;
                if (!$employee) {
                    $skipped++;
                    echo "ข้ามสิทธิ PERSON_ID {$row['PERSON_ID']}: ไม่พบ CID {$cid}\n";
                    continue;
                }

                $thaiYear = (int) $row['OVER_YEAR_ID'];
                $model = LeaveEntitlements::findOne([
                    'emp_id' => $employee->id,
                    'thai_year' => $thaiYear,
                ]);
                $isNew = $model === null;
                $model = $model ?? new LeaveEntitlements();

                $workYear = $employee->workYear();

                $model->emp_id = $employee->id;
                $model->position_type_id = $employee->position_type;
                $model->leave_type_id = 'LT4';
                $model->month_of_service = (int) ($workYear['month'] ?? 0);
                $model->year_of_service = (int) $row['OLDS'];
                $model->thai_year = $thaiYear;

                $entitlement = $this->calculateImportedLeaveEntitlement($model, $row);
                $model->balance = $entitlement['before_leave_balance'];
                $model->leave_on_year = $entitlement['leave_days'];
                $model->days = $entitlement['total_days'];

                $currentJson = is_array($model->data_json)
                    ? $model->data_json
                    : Json::decode($model->data_json ?: '{}');
                $model->data_json = Json::encode(ArrayHelper::merge($currentJson, [
                    'before_leave_balance' => $entitlement['before_leave_balance'],
                    'leave_days' => $entitlement['leave_days'],
                    'accumulation' => $entitlement['accumulation'],
                    'leave_max_days' => $entitlement['leave_max_days'],
                    'source_before_leave_balance' => $entitlement['source_before_leave_balance'],
                    'source_total_days' => $entitlement['source_total_days'],
                    'hosoffice_leave_over_id' => (int) $row['ID'],
                    'hosoffice_person_id' => (string) $row['PERSON_ID'],
                    'hosoffice_person_type_id' => (string) $row['HR_PERSON_TYPE_ID'],
                ]));

                if (!$model->getDirtyAttributes()) {
                    $unchanged++;
                    continue;
                }

                if ($model->save(false)) {
                    $isNew ? $created++ : $updated++;
                } else {
                    $failed++;
                }
            }

            $transaction->commit();
        } catch (\Throwable $th) {
            $transaction->rollBack();
            echo "นำเข้าสิทธิลาพักผ่อนไม่สำเร็จ: {$th->getMessage()}\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "สรุปสิทธิลาพักผ่อน: สร้าง {$created}, ปรับปรุง {$updated}, ไม่เปลี่ยน {$unchanged}, ข้าม {$skipped}, ผิดพลาด {$failed}\n";
        return ExitCode::OK;
    }

    /**
     * คำนวณสิทธิลาพักผ่อนที่นำเข้าตามนโยบายเดียวกับ /leave/leave-policies
     *
     * DAY_LEAVE_OVER คือสิทธิรวมหลัง HosOffice คำนวณแล้ว จึงหักสิทธิประจำปี
     * เพื่อหา "ยอดยกมา" ที่รวมอยู่ในปีนี้จริง ส่วน DAY_LEAVE_OVER_BEFORE เป็น
     * ค่าดิบจากปีก่อนซึ่งบางรายการว่างหรือไม่ตรงกับยอดรวม จึงเก็บไว้เพื่ออ้างอิง
     */
    private function calculateImportedLeaveEntitlement(LeaveEntitlements $model, array $row): array
    {
        $sourceAnnualDays = max(0, (float) ($row['DAY_PER_YEAR'] ?? 10));
        $sourceTotalDays = max(0, (float) ($row['DAY_LEAVE_OVER'] ?? 0));
        $sourceBeforeBalance = max(0, (float) ($row['DAY_LEAVE_OVER_BEFORE'] ?? 0));

        static $policyCache = [];
        $policyKey = $model->position_type_id . ':' . $model->year_of_service;
        if (!array_key_exists($policyKey, $policyCache)) {
            $policyCache[$policyKey] = Yii::$app->db->createCommand(
                'SELECT days, max_days, accumulation
                 FROM leave_policies
                 WHERE position_type_id = :position_type_id
                   AND year_of_service <= :year_of_service
                 ORDER BY year_of_service DESC
                 LIMIT 1'
            )->bindValues([
                ':position_type_id' => $model->position_type_id,
                ':year_of_service' => $model->year_of_service,
            ])->queryOne();
        }
        $policy = $policyCache[$policyKey];

        if (!$policy) {
            $leaveDays = min($sourceAnnualDays, $sourceTotalDays);
            $beforeBalance = max(0, $sourceTotalDays - $leaveDays);

            return [
                'before_leave_balance' => $beforeBalance,
                'leave_days' => $leaveDays,
                'total_days' => $sourceTotalDays,
                'accumulation' => $beforeBalance > 0 ? 1 : 0,
                'leave_max_days' => 0.0,
                'source_before_leave_balance' => $sourceBeforeBalance,
                'source_total_days' => $sourceTotalDays,
            ];
        }

        $policyDays = max(0, (float) $policy['days']);
        $annualDays = $policyDays > 0 ? $policyDays : $sourceAnnualDays;
        $maxDays = max(0, (float) $policy['max_days']);
        $accumulation = (int) $policy['accumulation'];

        if ($accumulation !== 1) {
            return [
                'before_leave_balance' => 0.0,
                'leave_days' => $annualDays,
                'total_days' => $annualDays,
                'accumulation' => 0,
                'leave_max_days' => $maxDays,
                'source_before_leave_balance' => $sourceBeforeBalance,
                'source_total_days' => $sourceTotalDays,
            ];
        }

        $cappedSourceTotal = $maxDays > 0
            ? min($sourceTotalDays, $maxDays)
            : $sourceTotalDays;
        $leaveDays = min($annualDays, $cappedSourceTotal);
        $derivedCarryForward = max(0, $cappedSourceTotal - $leaveDays);
        $maxCarryForward = $maxDays > 0
            ? max(0, $maxDays - $leaveDays)
            : $derivedCarryForward;
        $beforeBalance = min(
            $derivedCarryForward,
            $maxCarryForward
        );

        return [
            'before_leave_balance' => $beforeBalance,
            'leave_days' => $leaveDays,
            'total_days' => $leaveDays + $beforeBalance,
            'accumulation' => 1,
            'leave_max_days' => $maxDays,
            'source_before_leave_balance' => $sourceBeforeBalance,
            'source_total_days' => $sourceTotalDays,
        ];
    }

    public function getStatus($variable)
    {

        switch ($variable) {
            // ===== โค้ดตัวอักษรเดี่ยวจาก hos-office (leave_register.LEAVE_STATUS_CODE) =====
            // Z = ผ่านครบทุกขั้น (หัวหน้า+ตรวจสอบ+ผอ.) = อนุมัติสมบูรณ์
            case 'Z':
                $level = 4;
                $approve_status = 'Pass';
                $status = 'Approve';
                break;
            // A/B/E/S = ยังอยู่ระหว่างดำเนินการ (ผอ.ยังไม่อนุมัติ) = รอ
            case 'A': // เพิ่งยื่น ยังไม่มีใครดำเนินการ
            case 'B': // แทบไม่ดำเนินการ
            case 'E': // หัวหน้าเห็นชอบ รอตรวจสอบ
            case 'S': // ผ่านตรวจสอบแล้ว รอ ผอ.อนุมัติ
                $level = 1;
                $approve_status = 'Pending';
                $status = 'Pending';
                break;
            // N = ยกเลิก
            case 'N':
                $level = 0;
                $approve_status = '';
                $status = 'Cancel';
                break;
            // D = ไม่อนุมัติ
            case 'D':
                $level = 0;
                $approve_status = '';
                $status = 'Reject';
                break;

            // ===== โค้ดแบบคำเต็ม (รองรับ tenant อื่นที่ใช้คำเต็ม) =====
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

    // ชื่อสถานะภาษาไทยจากโค้ดตัวอักษรเดี่ยวของ hos-office (เก็บลง data_json.status_name)
    private function leaveStatusNameTh($code)
    {
        $map = [
            'Z' => 'อนุมัติ',
            'A' => 'รอดำเนินการ',
            'B' => 'รอดำเนินการ',
            'E' => 'รอตรวจสอบ',
            'S' => 'รอผู้อำนวยการอนุมัติ',
            'N' => 'ยกเลิก',
            'D' => 'ไม่อนุมัติ',
        ];
        return $map[$code] ?? $code;
    }

    public function UpdateStatus()
    {
        // จำกัดเฉพาะใบลาที่นำเข้าจาก HosOffice ไม่แก้ใบลาที่สร้างในระบบหลัก
        $count = Yii::$app->db->createCommand(
            "UPDATE `leave`
             SET status = 'Approve'
             WHERE `thai_year` IS NOT NULL
               AND status = 'Allow'
               AND JSON_VALID(`data_json`)
               AND JSON_EXTRACT(`data_json`, '$.id') IS NOT NULL"
        )->execute();
        echo "อัปเดตข้อมูลจำนวน $count รายการ \n";
        return ExitCode::OK;
    }

    public function actionCreateApproveLeave($limit = null)
    {
        $transaction = Yii::$app->db->getTransaction();
        $ownsTransaction = $transaction === null;
        if ($ownsTransaction) {
            $transaction = Yii::$app->db->beginTransaction();
        }
        try {
            $query = Leave::find()
                ->where('JSON_VALID([[data_json]])')
                ->andWhere("JSON_EXTRACT([[data_json]], '$.id') IS NOT NULL")
                ->orderBy(['id' => SORT_ASC]);
            if ($limit !== null) {
                $query->limit(max(1, (int) $limit));
            }
            $leaves = $query->all();
            $num = 1;
            $total = count($leaves);
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $approvalSteps = $this->leaveApprovalStepDefinitions();
            echo "อัปเดตข้อมูลการอนุมัติลา...\n";
            foreach ($leaves as $item) {
                $data = $this->decodeDataJson($item->data_json);

                // ระดับที่ "ผ่านจริง" ตาม milestone จากระบบต้นทาง (บันทึกไว้ตอน actionLeave)
                if (array_key_exists('milestone_leader', $data)) {
                    $passed = [
                        1 => !empty($data['milestone_leader']),   // หัวหน้ารับทราบ/เห็นชอบ
                        2 => !empty($data['milestone_leader']),
                        3 => !empty($data['milestone_check']),     // ผู้ตรวจสอบ
                        4 => !empty($data['milestone_director']),  // ผอ.อนุมัติ
                    ];
                } else {
                    // Fallback (กรณียังไม่ได้ re-run actionLeave): อนุมานจากสถานะใบลา
                    // เฉพาะใบที่อนุมัติสมบูรณ์เท่านั้นที่ถือว่าผ่านครบทุกระดับ
                    $approved = ($item->status === 'Approve');
                    $passed = [1 => $approved, 2 => $approved, 3 => $approved, 4 => $approved];
                }

                foreach ($approvalSteps as $level => $step) {
                    $empId = $data['approve_' . $level] ?? null;
                    // ไม่มีผู้อนุมัติ หรือระดับนี้ยังไม่ผ่านจริง → ไม่สร้าง
                    if (empty($empId) || empty($passed[$level])) {
                        $skipped++;
                        continue;
                    }

                    $approve = Approve::findOne([
                        'name' => 'leave',
                        'from_id' => $item->id,
                        'level' => $level,
                    ]);
                    $isNew = $approve === null;
                    $approve = $approve ?? new Approve();
                    $approvedAt = $this->normalizeApproveDate(match ($level) {
                        1, 2 => $data['leader_approved_at'] ?? null,
                        3 => $data['check_approved_at'] ?? null,
                        4 => $data['director_approved_at'] ?? null,
                        default => null,
                    });
                    $approve->name = 'leave';
                    $approve->from_id = $item->id;
                    $approve->level = $level;
                    $approve->emp_id = $empId;
                    $approve->status = 'Pass';
                    $approve->title = $step['title'];
                    $approve->data_json = $this->prepareImportedDataJson(
                        Approve::class,
                        $approve->data_json,
                        [
                            'label' => $step['label'],
                            'title' => $step['title'],
                            'approve_date' => $approvedAt,
                            'source_system' => 'hosoffice',
                        ]
                    );
                    if ($approvedAt !== null) {
                        $approve->created_at = $approvedAt;
                    }
                    if (!$approve->save(false)) {
                        throw new \RuntimeException('บันทึกข้อมูลอนุมัติใบลา ID ' . $item->id . ' ระดับ ' . $level . ' ไม่สำเร็จ');
                    }
                    $isNew ? $created++ : $updated++;
                }
                BaseConsole::updateProgress($num, $total);
                $num++;
            }

            if ($ownsTransaction) {
                $transaction->commit();
            }
            echo "สรุปอนุมัติลา: สร้าง {$created}, อัปเดต {$updated}, ข้าม {$skipped}\n";
            return ExitCode::OK;
        } catch (\Throwable $th) {
            if ($ownsTransaction && $transaction->isActive) {
                $transaction->rollBack();
            }
            echo "\nสร้างข้อมูลการอนุมัติลาไม่สำเร็จ: {$th->getMessage()}\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * โครงสร้างมาตรฐานของการอนุมัติลาในข้อมูลที่นำเข้าจาก HosOffice
     */
    private function leaveApprovalStepDefinitions(): array
    {
        return [
            1 => ['label' => 'เห็นชอบ', 'title' => 'หัวหน้างาน'],
            2 => ['label' => 'เห็นชอบ', 'title' => 'หัวหน้ากลุ่มงาน'],
            3 => ['label' => 'ผ่าน', 'title' => 'เจ้าหน้าที่ตรวจสอบ'],
            4 => ['label' => 'อนุมัติ', 'title' => 'ผู้อำนวยการ'],
        ];
    }

    /**
     * รวม data_json เดิมกับข้อมูลจาก HosOffice และคืนชนิดที่ตรงกับ schema ปลายทาง
     * เพื่อให้ importer ใช้ได้ทั้งฐานใหม่ที่เป็น JSON และฐาน legacy ที่เป็น LONGTEXT
     *
     * @param class-string<\yii\db\ActiveRecord> $modelClass
     * @param mixed $currentData
     * @param array ...$updates
     * @return array|string
     */
    private function prepareImportedDataJson(string $modelClass, $currentData, array ...$updates)
    {
        $currentData = $this->decodeDataJson($currentData);
        $merged = ArrayHelper::merge($currentData, ...$updates);
        $column = $modelClass::getTableSchema()->getColumn('data_json');

        return $column && $column->type === \yii\db\Schema::TYPE_JSON
            ? $merged
            : Json::encode($merged);
    }

    /**
     * สร้างแผนที่ source ID => primary key เพื่อให้การนำเข้ารอบถัดไป update
     * แถวเดิม แม้วันที่ เวลา หรือรายละเอียดใน HosOffice จะถูกแก้ไขแล้ว
     *
     * @param class-string<\yii\db\ActiveRecord> $modelClass
     * @return array<string,int>
     */
    private function importedRecordIds(string $modelClass, string $sourceKey, array $conditions = []): array
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $sourceKey)) {
            throw new \InvalidArgumentException('Invalid HosOffice source key.');
        }

        $db = Yii::$app->db;
        $table = $db->quoteTableName($modelClass::tableName());
        $id = $db->quoteColumnName('id');
        $dataJson = $db->quoteColumnName('data_json');
        $jsonPath = '$.' . $sourceKey;
        $where = [
            "{$dataJson} IS NOT NULL",
            "JSON_VALID({$dataJson})",
            "JSON_EXTRACT({$dataJson}, :filterJsonPath) IS NOT NULL",
        ];
        $params = [
            ':jsonPath' => $jsonPath,
            ':filterJsonPath' => $jsonPath,
        ];
        foreach ($conditions as $column => $value) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $column)) {
                throw new \InvalidArgumentException('Invalid destination filter column.');
            }
            $placeholder = ':condition' . count($params);
            $where[] = $db->quoteColumnName((string) $column) . " = {$placeholder}";
            $params[$placeholder] = $value;
        }

        $rows = $db->createCommand(
            "SELECT {$id}, JSON_UNQUOTE(JSON_EXTRACT({$dataJson}, :jsonPath)) AS source_id
             FROM {$table}
             WHERE " . implode(' AND ', $where),
            $params
        )->queryAll();

        $map = [];
        foreach ($rows as $row) {
            $sourceId = trim((string) ($row['source_id'] ?? ''));
            if ($sourceId !== '') {
                $map[$sourceId] = (int) $row['id'];
            }
        }

        return $map;
    }

    /**
     * Fallback ใช้รับแถว legacy ที่ยังไม่มี source ID เท่านั้น และไม่ยึดแถวของ source อื่น
     * แม้ข้อมูลชื่อ/วันที่จะบังเอิญตรงกัน
     */
    private function sourceFallbackQuery($query, string $sourceKey, $sourceId)
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $sourceKey)) {
            throw new \InvalidArgumentException('Invalid HosOffice source key.');
        }

        $jsonPath = '$.' . $sourceKey;
        return $query->andWhere(new \yii\db\Expression(
            "COALESCE(
                JSON_UNQUOTE(JSON_EXTRACT(
                    CASE WHEN JSON_VALID([[data_json]]) THEN [[data_json]] ELSE '{}' END,
                    :fallbackJsonPath
                )),
                ''
            ) IN ('', :fallbackSourceId)",
            [
                ':fallbackJsonPath' => $jsonPath,
                ':fallbackSourceId' => (string) $sourceId,
            ]
        ));
    }

    private function decodeDataJson($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (!is_string($data) || trim($data) === '') {
            return [];
        }

        try {
            $decoded = Json::decode($data, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }

    private function limitSql($limit): string
    {
        return $limit === null ? '' : ' LIMIT ' . max(1, (int) $limit);
    }

    /**
     * แปลงวันอนุมัติจาก HosOffice ให้อยู่ในรูปแบบ datetime ของระบบ
     */
    private function normalizeApproveDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * กำหนด label/title ของการอนุมัติลาตามระดับ และรองรับ data_json ที่เป็น NULL
     */
    public function actionFixApproveLabel()
    {
        $approvalSteps = $this->leaveApprovalStepDefinitions();

        $transaction = Yii::$app->db->beginTransaction();
        $totalAffected = 0;
        try {
            foreach ($approvalSteps as $level => $step) {
                Yii::$app->db->createCommand(
                    'UPDATE `approve_level_setting`
                     SET `label` = :label, `title` = :title
                     WHERE `system` = :system AND `level` = :level',
                    [
                        ':label' => $step['label'],
                        ':title' => $step['title'],
                        ':system' => 'leave',
                        ':level' => $level,
                    ]
                )->execute();

                $affected = Yii::$app->db->createCommand(
                    "UPDATE `approve`
                     SET `title` = :title,
                         `data_json` = JSON_SET(
                             CASE WHEN JSON_VALID(`data_json`) THEN COALESCE(`data_json`, JSON_OBJECT()) ELSE JSON_OBJECT() END,
                             '$.label', :label,
                             '$.title', :title
                         )
                     WHERE `name` = :name AND `level` = :level",
                    [
                        ':label' => $step['label'],
                        ':title' => $step['title'],
                        ':name' => 'leave',
                        ':level' => $level,
                    ]
                )->execute();
                $totalAffected += $affected;
                echo "Level {$level} ({$step['label']} / {$step['title']}): {$affected} รายการ\n";
            }
            $transaction->commit();
        } catch (\Throwable $th) {
            $transaction->rollBack();
            echo "ปรับ label ไม่สำเร็จ: {$th->getMessage()}\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "สรุปปรับข้อมูลกำกับขั้นอนุมัติ {$totalAffected} รายการ\n";
        return ExitCode::OK;
    }

    // ระบบห้องประชุม
    public function actionMeeting($limit = null)
    {
        $this->CreateRoom();
        $sql = "SELECT r.ROOM_ID,r.ROOM_NAME,s.* FROM `room_service` s 
        LEFT JOIN room_index r ON r.ROOM_ID = s.ROOM_ID
        ORDER BY s.DATE_TIME_REQUEST ASC, s.ID ASC";
        $sql .= $this->limitSql($limit);
        $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();
        $existingBySourceId = $this->importedRecordIds(Meeting::class, 'ID');

        $num = 1;
        $total = count($querys);
        echo "นำเข้าข้อมูลการจองห้องประชุม...\n";
        foreach ($querys as $key => $item) {
            try {

                $emp = $this->Person($item['PERSON_REQUEST_ID']);
                $sourceId = (string) $item['ID'];
                $check = isset($existingBySourceId[$sourceId])
                    ? Meeting::findOne($existingBySourceId[$sourceId])
                    : null;

                // รองรับข้อมูลที่เคยนำเข้าก่อนบันทึก source ID ลง data_json
                if (!$check) {
                    $check = $this->sourceFallbackQuery(
                        Meeting::find()->where([
                            'emp_id' => $emp?->id  ?? 0,
                            'room_id' => $item['ROOM_ID'],
                            'thai_year' => $item['YEAR_ID'],
                            'title' => $item['SERVICE_STORY'],
                            'date_start' => $item['DATE_BEGIN'],
                            'date_end' => $item['DATE_END'],
                            'time_start' => $item['TIME_BEGIN'],
                            'time_end' => $item['TIME_END'],
                        ]),
                        'ID',
                        $sourceId
                    )->one();
                }

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
                $dataJson = [
                    'phone' => $item['PERSON_REQUEST_PHONE'],
                    'meeting_details' => $item['SERVICE_FOR_DETAIL'],
                ];
                $model->data_json = $this->prepareImportedDataJson(
                    Meeting::class,
                    $model->data_json,
                    $dataJson,
                    $this->cleanUtf8($item)
                );
                if ($model->save(false)) {
                    $existingBySourceId[$sourceId] = $model->id;
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
            $isNew = $room === null;
            $room = $room ?? new Categorise();
            $room->name = 'meeting_room';
            $room->code = $item['ROOM_ID'];
            $room->title = $item['ROOM_NAME'];
            $defaults = $isNew
                ? [
                    'location' => 'อาคารผู้ป่วยนอก ชั้น 2',
                    'room_accessory' => [
                        "คอมพิวเตอร์",
                        "โปรเจคเตอร์",
                        "ไมค์ประชุม"
                    ],
                    'advance_booking' => '1',
                ]
                : [];
            $room->data_json = ArrayHelper::merge(
                $this->decodeDataJson($room->data_json),
                $defaults,
                [
                    'owner' => $this->Person($item['ROOM_PERSON_ID'])?->id,
                    'seat_capacity' => $item['CONTAIN'],
                ]
            );
            if ($room->save(false) && $isNew) {
                echo "สร้างห้องประชุม : " . $room->title . "\n";
            }
        }
    }



    // งานซ่อมบำรุง
    public function actionRepairGeneral($limit = null)
    {
        set_time_limit(0);
        $sql = 'SELECT ot.OTHER_NAME,l.LOCATION_NAME ,p.PRIORITY_NAME,
                a.ARTICLE_NUM,
                r.*
                FROM `repair_index` r
                LEFT JOIN repair_other ot ON ot.OTHER_ID = r.OTHER_ID
                LEFT JOIN repair_location_see l ON l.ID = r.LOCATION_SEE_ID
                LEFT JOIN repair_priority p ON p.PRIORITY_ID = r.PRIORITY_ID
                LEFT JOIN ar_article a ON a.ARTICLE_ID = r.ARTICLE_ID
                ORDER BY r.ID';
        $sql .= $this->limitSql($limit);

        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $existingBySourceId = $this->importedRecordIds(
            Helpdesk::class,
            'ID',
            ['repair_group' => 1]
        );
        $num = 1;
        $total = count($querys);

        echo "เริ่มนำเข้าข้อมูลซ้อมบำรุง...\n";
        foreach ($querys as $item) {
            // try {
            $emp = $this->Person($item['USER_REQUEST_ID']);
            $sourceId = (string) $item['ID'];
            $checkModel = isset($existingBySourceId[$sourceId])
                ? Helpdesk::findOne($existingBySourceId[$sourceId])
                : $this->sourceFallbackQuery(
                    Helpdesk::find()->where(['repair_group' => 1, 'repair_number' => $item['REPAIR_ID']]),
                    'ID',
                    $sourceId
                )->one();
            if (!$checkModel) {
                $model = new Helpdesk();
            } else {
                $model = $checkModel;
            }
            // ตรวจสอบว่ามีวันที่ส่งมาไหม
            $receiveDateTime = null;
            if (!empty($item['TECH_RECEIVE_DATE']) && $item['TECH_RECEIVE_DATE'] !== '0000-00-00') {
                // รวมวันที่และเวลา
                $receiveDateTime = trim($item['TECH_RECEIVE_DATE'] . ' ' . ($item['TECH_RECEIVE_TIME'] ?: '00:00:00'));
                $model->receive_date = $receiveDateTime;
            } else {
                $model->receive_date = null; // หรือค่าเริ่มต้นอื่นๆ
            }



            // ไม่เก็บไฟล์รูปแบบ binary ลง data_json เพราะทำให้ JSON/UTF-8 เสีย
            $sourceData = $item;
            foreach (['REPAIR_IMG', 'REPAIR_SUCCESS_IMG', 'IMAGES'] as $imageColumn) {
                unset($sourceData[$imageColumn]);
            }
            $cleanItem = $this->cleanUtf8($sourceData);
            $model->repair_group = 1;
            $model->name = 'repair';
            $model->repair_number = $item['REPAIR_ID'];
            $model->emp_id = $emp?->id ?? 0;
            $model->thai_year = $item['YEAR_ID'];
            $model->title = $item['REPAIR_NAME'];
            $model->created_by = $emp?->user_id;
            $model->status = !empty($item['TECH_SUCCESS_DATE']) ? 'success' : $this->RepairStatus($item);
            $model->created_at = $item['DATE_TIME_REQUEST'];
            $model->date_start = $item['REPAIR_DATE'];
            $model->date_end = $item['TECH_REPAIR_DATE'];
            $model->rating = $item['REPAIR_SCORE'];
            $model->created_at = $item['DATE_TIME_REQUEST'];
            $model->receive_date = $receiveDateTime;

            $dataJson = [
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
                'create_name' => $emp?->fullname ?? $item['USER_REQUEST_NAME'] ?? '',
                'status_name' => 'ร้องขอ',
                'location_other' => '',
                'technician_req' => '0',
                'technician_name' => '',
            ];
            $model->data_json = $this->prepareImportedDataJson(
                Helpdesk::class,
                $model->data_json,
                $dataJson,
                $cleanItem
            );

            if ($model->save(false)) {
                $existingBySourceId[$sourceId] = $model->id;

                ################## วันรับเื่อง ม###########################

                if (!empty($item['TECH_RECEIVE_DATE']) && $item['TECH_RECEIVE_DATE'] !== '0000-00-00') {
                    $this->upsertRepairServiceRecord($model->id, 'hosoffice_receive', [
                        'title' => $item['TECH_RECEIVE_COMMENT'],
                        'status' => 'รับเรื่อง',
                        'created_at' => $receiveDateTime,
                    ]);
                }

                ############### วันที่ซ่อม #########################
                if (!empty($item['TECH_REPAIR_DATE']) && $item['TECH_REPAIR_DATE'] !== '0000-00-00') {
                    // รวมวันที่และเวลา
                    $repairDateTime = trim($item['TECH_REPAIR_DATE'] . ' ' . ($item['TECH_REPAIR_TIME'] ?: '00:00:00'));
                    $this->upsertRepairServiceRecord($model->id, 'hosoffice_repair', [
                        'status' => $item['REPAIR_COMMENT'],
                        'created_at' => $repairDateTime,
                    ]);
                }

                ############### วันที่ซ่อมเสร็จ #########################

                if (!empty($item['TECH_SUCCESS_DATE']) && $item['TECH_SUCCESS_DATE'] !== '0000-00-00') {
                    // รวมวันที่และเวลา
                    $repairSuccessDateTime = trim($item['TECH_SUCCESS_DATE'] . ' ' . ($item['TECH_SUCCESS_TIME'] ?: '00:00:00'));
                    $this->upsertRepairServiceRecord($model->id, 'hosoffice_success', [
                        'status' => 'ซ่อมเสร็จ',
                        'created_at' => $repairSuccessDateTime,
                    ]);
                }


                ################## ช่างซ่อม ###########################
                $techPerson = $this->Person($item['TECH_REPAIR_ID']);
                if ($techPerson) {
                    $repairTeamData = [
                        'helpdesk_id' => $model->id,
                        'name' => 'repair_team',
                    ];
                    $repairTeam = HelpdeskDetail::findOne($repairTeamData)
                        ?? new HelpdeskDetail($repairTeamData);
                    $repairTeam->emp_id = $techPerson->id;
                    $repairTeam->save(false);
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

    private function upsertRepairServiceRecord(int $helpdeskId, string $code, array $attributes): void
    {
        $record = HelpdeskDetail::findOne([
            'helpdesk_id' => $helpdeskId,
            'name' => 'service_record',
            'code' => $code,
        ]);

        // รับข้อมูลเก่าที่ยังไม่มี code มาใช้ต่อ เพื่อไม่สร้างประวัติซ้ำเมื่ออัปเกรด importer
        if (!$record) {
            $query = HelpdeskDetail::find()
                ->where(['helpdesk_id' => $helpdeskId, 'name' => 'service_record'])
                ->andWhere(['or', ['code' => null], ['code' => '']]);
            if ($code === 'hosoffice_receive') {
                $query->andWhere(['status' => 'รับเรื่อง']);
            } elseif ($code === 'hosoffice_success') {
                $query->andWhere(['status' => 'ซ่อมเสร็จ']);
            } else {
                $query->andWhere(['not in', 'status', ['รับเรื่อง', 'ซ่อมเสร็จ']]);
            }
            $record = $query->orderBy(['id' => SORT_ASC])->one();
        }

        $record = $record ?? new HelpdeskDetail();
        $record->helpdesk_id = $helpdeskId;
        $record->name = 'service_record';
        $record->code = $code;
        $record->setAttributes($attributes, false);
        $record->detachBehavior(0);
        $record->save(false);
    }


    public function actionComputer($limit = null)
    {
        $sql = 'SELECT p.PRIORITY_NAME,l.LOCATION_NAME,r.*
                FROM `com_repair` r
                LEFT JOIN com_priority p ON p.PRIORITY_ID = r.PRIORITY_ID
                LEFT JOIN com_location l ON l.LOCATION_ID = r.LOCATION_ID
                ORDER BY r.REPAIR_ID';
        $sql .= $this->limitSql($limit);
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $existingBySourceId = $this->importedRecordIds(
            Helpdesk::class,
            'REPAIR_ID',
            ['repair_group' => 2]
        );

        foreach ($querys as $item) {
            $sourceId = (string) $item['REPAIR_ID'];
            $emp = $this->Person($item['REPAIR_BY_ID']);
            $model = isset($existingBySourceId[$sourceId])
                ? Helpdesk::findOne($existingBySourceId[$sourceId])
                : $this->sourceFallbackQuery(
                    Helpdesk::find()->where([
                        'repair_group' => 2,
                        'repair_number' => 'COM-' . $sourceId,
                    ]),
                    'REPAIR_ID',
                    $sourceId
                )->one();
            $model = $model ?? new Helpdesk();
            $model->repair_group = 2;
            $model->name = 'repair';
            $model->repair_number = 'COM-' . $sourceId;
            $model->emp_id = $emp?->id ?? 0;
            $model->thai_year = $item['YEAR_ID'] ?: AppHelper::YearBudget($item['DATE_TIME_REQUEST']);
            $model->title = $item['REPAIR_DETAIL_BEGIN'] ?: ($item['COM_NAME'] ?: 'แจ้งซ่อมคอมพิวเตอร์ #' . $sourceId);
            $model->created_by = $emp?->user_id;
            $model->status = $this->RepairStatus($item);
            $model->created_at = $item['DATE_TIME_REQUEST'];
            $model->request_repair_date = $item['DATE_WANT_USES'];
            $model->receive_date = $item['RECEIVE_DATE_TIME'];
            $model->date_start = $item['RECEIVE_DATE_TIME']
                ? date('Y-m-d', strtotime($item['RECEIVE_DATE_TIME']))
                : null;
            $model->date_end = null;
            $model->rating = $item['ASSES_SCORE'];
            $dataJson = [
                'time_start' => $item['TIME_WANT_USES'],
                'status' => $item['REPAIR_STATUS'],
                'note' => $item['RECEIVE_COMMENT'],
                'phone' => '',
                'urgency' => $item['PRIORITY_ID'],
                'urgency_name' => $item['PRIORITY_NAME'],
                'location' => $item['LOCATION_NAME'],
                'repair_type' => 'ซ่อมภายใน',
                'send_type' => 'general',
                'accept_emp_id' => $this->Person($item['RECEIVE_BY_ID'])?->id,
                'accept_name' => $item['RECEIVE_BY_NAME'],
                'accept_time' => $item['RECEIVE_DATE_TIME'],
                'create_name' => $emp?->fullname ?? '',
                'status_name' => $item['REPAIR_STATUS'],
                'location_other' => '',
                'technician_req' => '0',
                'technician_name' => '',
                'source_system' => 'hosoffice_com_repair',
            ];
            $sourceData = $item;
            foreach (['COM_IMG', 'REPAIR_IMG1', 'REPAIR_IMG2', 'REPAIR_IMG3', 'REPAIR_IMG4'] as $imageColumn) {
                unset($sourceData[$imageColumn]);
            }
            $model->data_json = $this->prepareImportedDataJson(
                Helpdesk::class,
                $model->data_json,
                $dataJson,
                $this->cleanUtf8($sourceData)
            );
            if ($model->save(false)) {
                $existingBySourceId[$sourceId] = $model->id;
                $this->TechRepair($model->id, $item['ENGINEER_REQUEST_ID']);
            } else {
                echo "Save Error \n";
                print_r($model->getErrors());
            }
        }
        return ExitCode::OK;
    }

    private function RepairStatus($data)
    {
        switch ($data['REPAIR_STATUS']) {
            case 'REQUEST':
            case 'PENDING':
            case 'WAIT':
                return 'pending';
            case 'RECEIVE':
            case 'REPAIR':
            case 'REPAIR_OUT':
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
            $model->data_json = $this->prepareImportedDataJson(
                Helpdesk::class,
                $model->data_json,
                [
                    'tech_fullname' => $emp->fullname,
                    'tech_position' => $emp->positionName(),
                    'tech_department' => $emp->departmentName(),
                ]
            );
            $model->save(false);
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    public function actionAsset($limit = null)
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
ORDER BY `st`.`SUP_TYPE_NAME` ASC, a.ARTICLE_ID ASC";
        $sql .= $this->limitSql($limit);
        //  End Query
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $existingBySourceId = $this->importedRecordIds(Asset::class, 'ARTICLE_ID');
        $num = 1;
        $total = count($querys);
        echo "เริ่มนำเข้าข้อมูลครุภัณฑ์...\n";
        foreach ($querys as $asset) {
            try {
                $sourceId = (string) $asset['ARTICLE_ID'];
                $assetCode = $asset['ARTICLE_NUM'];
                $model = isset($existingBySourceId[$sourceId])
                    ? Asset::findOne($existingBySourceId[$sourceId])
                    : null;
                if (!$model && $assetCode) {
                    $model = $this->sourceFallbackQuery(
                        Asset::find()->where(['code' => $assetCode]),
                        'ARTICLE_ID',
                        $sourceId
                    )->one();
                }
                $isNew = $model === null;
                if ($isNew) {
                    $model = new Asset([
                        'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                    ]);
                    $this->CreateDir($model->ref);
                    if ($asset['IMG']) {
                        $name = time() . '.jpg';
                        file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $model->ref . '/' . $name, $asset['IMG']);

                        $upload = new Uploads;
                        $upload->ref = $model->ref;
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
                $vendorName = $asset['VENDOR_NAME'];
                $methodGet = $asset['METHOD_NAME'];
                $purchaseText = $asset['BUY_NAME'];
                $budgetType = $asset['BUDGET_NAME'];
                $departmentName = $asset['HR_DEPARTMENT_SUB_SUB_NAME'];
                $onYear = $asset['YEAR_ID'];
                $price =  $asset['PRICE_PER_UNIT'];
                $receiveDate = $asset['RECEIVE_DATE'];
                $assetItem = AssetHelper::CheckAssetItem($assetType, $assetCode, $assetItemName);
                $assetTypeModel = Categorise::findOne(['title' => $asset['DECLINE_NAME'], 'name' => 'asset_type', 'group_id' => 'EQUIP']);

                $org = $departmentName ? Organization::find()->where(['name' => $departmentName])->one() : null;
                $model->department = $org && isset($org->id) ? $org->id : 0;  // หน่วยงานภายในตามโครงสร้าง
                $model->asset_group_id = 4;
                $model->asset_type_id = isset($assetTypeModel->code) ? $assetTypeModel->code : null;
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
                    'asset_type_text' => $asset['DECLINE_NAME'],
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


                $sourceData = $asset;
                foreach (['IMG', 'IMAGES', 'QRCODE', 'IMG1', 'IMG2'] as $imageColumn) {
                    unset($sourceData[$imageColumn]);
                }
                $model->data_json = ArrayHelper::merge(
                    $this->decodeDataJson($model->data_json),
                    $data_json,
                    $this->cleanUtf8($sourceData)
                );
                if ($model->save(false)) {
                    $existingBySourceId[$sourceId] = $model->id;
                }
            } catch (\Throwable $th) {
                echo "เกิดข้อผิดพลาด : " . $asset['ARTICLE_ID'] . " - " . $th->getMessage() . "\n";
            }
            BaseConsole::updateProgress($num, $total);
            $num++;
        }
        echo "นำเข้าข้อมูลครุภัณฑ์เสร็จสิ้น!\n";
    }

    public function actionMaterial($limit = null)
    {
        $sql = "SELECT 
*
FROM 
    sup
LEFT JOIN 
    sup_type ON sup.SUP_TYPE_ID = sup_type.SUP_TYPE_ID
LEFT JOIN 
    sup_type_kind ON sup.SUP_TYPE_KIND_ID = sup_type_kind.SUP_TYPE_KIND_ID
LEFT JOIN sup_unit u ON u.SUP_UNIT_ID = sup.SUP_UNIT_ID
WHERE sup.SUP_TYPE_KIND_ID IN('1','2','4')
ORDER BY `sup_type`.`SUP_TYPE_NAME` ASC, sup.ID ASC";
        $sql .= $this->limitSql($limit);
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        $existingBySourceId = $this->importedRecordIds(
            Categorise::class,
            'ID',
            ['name' => 'asset_item']
        );
        $num = 1;
        $total = count($querys);

        foreach ($querys as $item) {
            unset($item['IMG']);
            $sourceId = (string) $item['ID'];
            $checkMaterial = isset($existingBySourceId[$sourceId])
                ? Categorise::findOne($existingBySourceId[$sourceId])
                : $this->sourceFallbackQuery(
                    Categorise::find()->where(['title' => $item['SUP_NAME'], 'name' => 'asset_item']),
                    'ID',
                    $sourceId
                )->one();
            $assetType = Categorise::find()->where(['category_id' => 4, 'name' => 'asset_type', 'title' => $item['SUP_TYPE_NAME']])->one();
            if ($checkMaterial) {
                $model = $checkMaterial;
            } else {
                $model = new Categorise;
                $model->ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model->name = 'asset_item';
            }
            $dataJson = [
                'unit' => $item['SUP_UNIT_NAME'],
                'metter_type' => $item['SUP_TYPE_KIND_NAME'],
                'purchase_type' => ''
            ];

            $model->qty_max = (float)$item['MAX'];
            $model->qty_min = (float)$item['MIN'];
            $model->group_id = 'MATER';
            $model->category_id = $assetType ? $assetType->code : '';
            $model->code = $item['SUP_FSN_NUM'];
            $model->title = $item['SUP_NAME'];
            $model->data_json = $this->prepareImportedDataJson(
                Categorise::class,
                $model->data_json,
                $dataJson,
                $this->cleanUtf8($item)
            );
            if ($model->save(false)) {
                $existingBySourceId[$sourceId] = $model->id;
            }

            BaseConsole::updateProgress($num, $total);
            $num++;
        }
    }
}
