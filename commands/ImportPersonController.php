<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AppHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\Employees;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use yii\helpers\BaseFileHelper;
use Yii;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class ImportPersonController extends Controller
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

    public function actionIndex()
    {
        // $this->CompanyInfo(); //ข้อมูลพื้นฐาน
        $sqlPerson = "SELECT p.`HR_STATUS_ID` as status,
        pre.`HR_PREFIX_NAME`,
        p.HR_POSITION_NUM,
        p.`HR_PREFIX_ID`,
        p.HR_SALARY  as salary,
        p.SEX,
        p.HR_STARTWORK_DATE as join_date,
        p.HR_FNAME as fname,
        p.HR_LNAME as lname,
        p.`HR_IMAGE` as image,
        if(p.`SEX` = 'M','ชาย','หญิง')  as gender,
        p.HR_CID as cid,
        p.`HR_EMAIL` as email,
        p.`HR_PHONE` as phone,
        p.`HR_BIRTHDAY` as birthday,
        p.HR_ZIPCODE as zipcode,
        p.HR_POSITION_ID as position_name,
        p.HR_PERSON_TYPE_ID as position_type,
        p.HR_LEVEL_ID as position_level,
        p.HR_DEPARTMENT_SUB_SUB_ID as department,
        CONCAT('เลขที่ ',p.HR_HOME_NUMBER,' ม.',p.HR_VILLAGE_NO) as address,
        CONCAT(p.HR_ROAD_NAME) as road,
        CONCAT(p.HR_SOI_NAME) as soiname,
        marry.`HR_MARRY_STATUS_NAME` as marry,
        nation.`HR_NATIONALITY_NAME` as nationality,
        re.HR_RELIGION_NAME as religion,
        b.`HR_BLOODGROUP_NAME` as blood
        FROM hrd_person p

        LEFT JOIN hrd_bloodgroup b ON b.`HR_BLOODGROUP_ID` = p.`HR_BLOODGROUP_ID`
        LEFT JOIN hrd_religion re ON re.`HR_RELIGION_ID` = p.HR_RELIGION_ID
        LEFT JOIN hrd_marry_status marry ON marry.`HR_MARRY_STATUS_ID` = p.`HR_MARRY_STATUS_ID`
        LEFT JOIN hrd_nationality nation ON nation.`HR_NATIONALITY_ID` = p.`HR_NATIONALITY_ID`
        LEFT JOIN hrd_prefix pre ON pre.`HR_PREFIX_ID` = p.`HR_PREFIX_ID`";
        $queryPersons = \Yii::$app->db2->createCommand($sqlPerson)->queryAll();
        $data = [];
        if (BaseConsole::confirm('Are you sure?')) {
            echo "user typed yes\n";
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
                    $model->prefix = $this->getPrefix($person['HR_PREFIX_ID'], $person['SEX']);
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
                    $model->status = 0;  // สถานะ
                    $model->address = $person['address'];
                    $data_json = [
                        'marry' => $person['marry'],
                        'nationality' => $person['nationality'],
                        'religion' => $person['religion'],
                        'blood' => $person['blood'],
                    ];

                    // $fullname =   $this->getPrefix($person['HR_PREFIX_ID'], $person['SEX']).$model->fname = $person['fname'].' '.$model->lname = $person['lname'];
                    $model->data_json = $data_json;

                    $this->Family($model->id, $model->cid);

                    // $message = ('# '.$i++.' ').$fullname.' Success';
                    if ($model->save(false)) {
                        // echo $message . "\n";
                        // echo  $person['fname'] . "\n";
                        echo $model->fname . ' ' . $model->lname . "\n";
                        // echo $person['fname'] . "\n";
                    } else {
                        echo "False \n";
                    }
                } else {
                    echo "นำเข้าข้อมูลแล้ว เข้าใจไม!!  \n";

                    return ExitCode::OK;
                }
            }
        } else {
            echo "user typed no\n";
        }
    }

    public function getPrefix($id, $gender)
    {
        if ($id == '01') {
            return 'นาย';
        } elseif ($id == '02') {
            return 'นาง';
        } elseif ($id == '03') {
            return 'น.ส.';
        } else {
        }
    }

    // ประวัติครอบครัว
    public function Family($emp_id, $cid)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $data = [];
        $sql = 'SELECT p.`HR_FNAME`,p.`HR_LNAME`,p.`HR_CID`,f.`NAME`,f.`TYPE`,f.`PHONE`,f.`ADDRESS` FROM hrd_tr_family f 
    LEFT JOIN hrd_person p ON p.`ID` = f.`PERSON_ID` 
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
