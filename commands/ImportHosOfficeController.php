<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\helpers\Json;
use yii\helpers\Console;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeDetail;
use app\modules\filemanager\models\Uploads;

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

    public function actionEmployee()
    {
        // $this->CompanyInfo(); //ข้อมูลพื้นฐาน
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
                LEFT JOIN hr_level ON hr_level.HR_LEVEL_ID = p.HR_LEVEL_ID;";
        $queryPersons = \Yii::$app->db2->createCommand($sqlPerson)->queryAll();
        if (BaseConsole::confirm('Are you sure?')) {
            $i = 1;
            foreach ($queryPersons as $person) {
                $checker = Employees::findOne(['cid' => $person['cid']]);

                if (!$checker) {
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
                    $model->position_name = 0;
                    $model->education = 0;  // การศึกษา
                    // $model->status = $person['status'];  // สถานะ
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
                    echo "นำเข้าข้อมูลแล้ว!!  \n";
                    // return ExitCode::OK;
                }
            }
        } else {
            echo "user typed no\n";
        }
    }

    public function actionUpdatePosition()
{
    // ใช้ batch() หากพนักงานมีจำนวนมากเพื่อประหยัด Memory
    $listEmployees = Employees::find()->each(); 

    foreach ($listEmployees as $emp) {
        $data = is_string($emp->data_json) ? Json::decode($emp->data_json) : $emp->data_json;

        // 1. ค้นหาหรือสร้าง Model ใหม่
        $empDetail = EmployeeDetail::findOne(['emp_id' => $emp->id, 'name' => 'position'])
            ?? new EmployeeDetail();
            
        if(!$empDetail->ref){
              $empDetail->ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        $empDetail->emp_id = $emp->id;
        $empDetail->name = 'position';

        // --- เตรียมตัวแปรสำหรับ Position (Default Values) ---
        $groupCode = $groupTitle = $typeCode = $typeTitle = $positionCode = $positionTitle = "";

        // 2. ดึงชื่อจาก JSON เพื่อไปค้นหาในฐานข้อมูล
        $pName = $data['position_name'] ?? null;
        $pType = $data['person_type_name'] ?? null;

        if ($pName && $pType) {
            $position = \Yii::$app->db->createCommand("
                SELECT 
                    pt.code AS type_code,
                    pg.code AS group_code,
                    pn.code AS position_code,
                    pt.title AS type_title,
                    pg.title AS group_title,
                    pn.title AS position_title
                FROM `categorise` pn
                LEFT JOIN `categorise` pg ON pg.code = pn.category_id AND pg.name = 'position_group'
                LEFT JOIN `categorise` pt ON pt.code = pg.category_id AND pt.name = 'position_type'
                WHERE pn.name = 'position_name' 
                  AND pn.title = :title_pos 
                  AND pt.title = :title_type
            ")
            ->bindValues([
                ':title_pos'  => trim($pName), // trim เพื่อลดความผิดพลาดจากช่องว่าง
                ':title_type' => trim($pType),
            ])
            ->queryOne();

            if ($position) {
                $groupCode     = $position['group_code'];
                $groupTitle    = $position['group_title'];
                $typeCode      = $position['type_code'];
                $typeTitle     = $position['type_title']; // แก้จาก code เป็น title
                $positionCode  = $position['position_code'];
                $positionTitle = $position['position_title'];
                echo "Emp ID: {$emp->id} -> พบตำแหน่ง: {$positionTitle}\n";
            } else {
                echo "Emp ID: {$emp->id} -> ไม่พบข้อมูลตำแหน่งใน categorise: {$pName}\n";
            }
        }

        // 3. จัดเตรียมข้อมูล JSON ลงใน Model
        $empDetail->data_json = [
            'statuslist'     => 'โอนข้อมูลจาก hos-office',
            'position_number'     => $data['hr_position_num'] ?? "-",
            'salary'              => $data['salary'] ?? 0,
            'fullname'            => $emp->fullname ?? 'ไม่ระบุชื่อ',
            'date_start'          => $data['join_date'] ?? null,
            "position_name"       => $positionCode,
            "position_type"       => $typeCode,
            "position_group"      => $groupCode,
            "position_level"      => $data['position_level'] ?? "", // รับจาก data เดิมถ้ามี
            "position_name_text"  => $positionTitle,
            "position_type_text"  => $typeTitle,
            "position_group_text" => $groupTitle
        ];

        // 4. บันทึกข้อมูล (ใช้ save(false) เพื่อข้าม validation หากมั่นใจในข้อมูล)
        if (!$empDetail->save(false)) {
            \Yii::error("Save Error ID: " . $emp->id . " Errors: " . Json::encode($empDetail->getErrors()));
        }
    }
    echo "สำเร็จ: ปรับปรุงข้อมูลตำแหน่งเรียบร้อยแล้ว";
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
}
