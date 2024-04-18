<?php

namespace app\modules\backoffice\controllers;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\backoffice\models\AssetArticle;
use app\modules\backoffice\models\Department;
use app\modules\backoffice\models\HrdLevel;
use app\modules\backoffice\models\InfoOrg;
use app\modules\backoffice\models\Person;
use app\modules\backoffice\models\PersonType;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeDetail;
use Yii;
use yii\helpers\BaseFileHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * Default controller for the `backoffice` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionData()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => '<i class="bi bi-database-add"></i> นำเข้าข้อมูลจาก Backoffice',
            'content' => $this->renderAjax('import_data'),
        ];
    }

    public function actionImportData()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        //$this->PositionType(); //ประภทบุคลากร
        //$this->PositionLevel(); // ระดับของข้าราชการ
        //$this->Department(); //แผนก
        // $this->ImportAsset(); //ทรัพย์สิน
        
        // $this->Department(); //หน่วยงาน
        
        // $this->CompanyInfo(); //ข้อมูลพื้นฐาน
        // $sqlPerson = "SELECT p.`HR_STATUS_ID` as status,
        // pre.`HR_PREFIX_NAME`,
        // p.HR_POSITION_NUM,
        // p.`HR_PREFIX_ID`,
        // p.HR_SALARY  as salary,
        // p.SEX,
        // p.HR_STARTWORK_DATE as join_date,
        // p.HR_FNAME as fname,p.HR_LNAME as lname,
        // p.`HR_IMAGE` as image,
        // if(p.`SEX` = 'M','ชาย','หญิง')  as gender,
        // p.HR_CID as cid,
        // p.`HR_EMAIL` as email,
        // p.`HR_PHONE` as phone,
        // p.`HR_BIRTHDAY` as birthday,
        // p.HR_ZIPCODE as zipcode,
        // p.HR_POSITION_ID as position_name,
        // p.HR_PERSON_TYPE_ID as position_type,
        // p.HR_LEVEL_ID as position_level,
        // p.HR_DEPARTMENT_SUB_SUB_ID as department,
        // CONCAT('เลขที่ ',p.HR_HOME_NUMBER,' ม.',p.HR_VILLAGE_NO) as address,
        // CONCAT(p.HR_ROAD_NAME) as road,
        // CONCAT(p.HR_SOI_NAME) as soiname,
        // marry.`HR_MARRY_STATUS_NAME` as marry,
        // nation.`HR_NATIONALITY_NAME` as nationality,
        // re.HR_RELIGION_NAME as religion,
        // b.`HR_BLOODGROUP_NAME` as blood
        // FROM hrd_person p

        // LEFT JOIN hrd_bloodgroup b ON b.`HR_BLOODGROUP_ID` = p.`HR_BLOODGROUP_ID`
        // LEFT JOIN hrd_religion re ON re.`HR_RELIGION_ID` = p.HR_RELIGION_ID
        // LEFT JOIN hrd_marry_status marry ON marry.`HR_MARRY_STATUS_ID` = p.`HR_MARRY_STATUS_ID`
        // LEFT JOIN hrd_nationality nation ON nation.`HR_NATIONALITY_ID` = p.`HR_NATIONALITY_ID`
        // LEFT JOIN hrd_prefix pre ON pre.`HR_PREFIX_ID` = p.`HR_PREFIX_ID`";
        // $queryPersons = Yii::$app->db2->createCommand($sqlPerson)->queryAll();
        // $data = [];
        // foreach ($queryPersons as $person) {
        //     $checker = Employees::findOne(['cid' => $person['cid']]);
        //     if (!$checker) {
        //         $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        //         $model = new Employees();
        //         $model->ref = $ref;
        //         $this->CreateDir($ref);
        //         if ($person['image']) {

        //             $name = time() . '.jpg';
        //             file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $person['image']);

        //             $upload = new Uploads;
        //             $upload->ref = $ref;
        //             $upload->name = 'avatar';
        //             $upload->file_name = $name;
        //             $upload->real_filename = $name;
        //             $upload->type = 'jpg';
        //             $upload->save(false);
        //         }

        //     } else {

        //         $model = Employees::findOne(['cid' => $person['cid']]);
        //     }

        //     $model->user_id = 0;
        //     $model->prefix = $this->getPrefix($person['HR_PREFIX_ID'], $person['SEX']);
        //     $model->gender = $person['gender'];
        //     $model->fname = $person['fname'];
        //     $model->lname = $person['lname'];
        //     $model->join_date = $person['join_date'];
        //     $model->salary = $person['salary'];
        //     $model->birthday = AppHelper::DateFormDb($person['birthday']);
        //     $model->cid = $person['cid'];
        //     $model->phone = preg_match("/-/", (string) $person['phone']) ? null : $person['phone'];
        //     $model->email = $person['email'];
        //     $model->zipcode = $person['zipcode'];
        //     $model->position_name = 0;
        //     $model->education = 0; //การศึกษา
        //     $model->status = 0; //สถานะ
        //     $model->address = $person['address'];
        //     $data_json = [
        //         'marry' => $person['marry'],
        //         'nationality' => $person['nationality'],
        //         'religion' => $person['religion'],
        //         'blood' => $person['blood'],
        //     ];
        //     $model->data_json = $data_json;
            
        //     $model->save(false);
        //     $this->Family($model->id,$model->cid);
        //     $data[] = $model;

        // }
        // //$this->Workgroup(); //กลุ่มงาน
        // Yii::$app->user->logout();
        // return $this->redirect(['/site/login']);
        // return $data;
    }

    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/';
            if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
                BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
            }
        }
        return;
    }

    public static function Utility($id)
    {

        $sqlBlood = "";
    }

    public function getPrefix($id, $gender)
    {
        if ($id == '01') {
            return 'นาย';
        } else if ($id == '02') {
            return 'นาง';

        } else if ($id == '03') {
            return 'น.ส.';
        } else {
            if ($gender == 'M') {
                return 'ชาย';
            } else {
                return 'หญิง';
            }
        }
    }

    //ประเภทบุคลากร
    public function PositionType()
    {
        Categorise::deleteAll(['name' => 'position_type']);
        foreach (PersonType::find()->all() as $person) {
            $model = new Categorise();
            $model->name = 'position_type';
            $model->code = $person->HR_PERSON_TYPE_ID;
            $model->title = $person->HR_PERSON_TYPE_NAME;
            $model->data_json = [
                'leave_condition' => $person->HR_LEAVE04_CON,
                'leave_day' => $person->HR_LEAVE04_DAY,

            ];
            $model->save(false);
        }

        return true;
    }

    //ระดับตำแหน่งข้าราชการ
    public function PositionLevel()
    {
        $data = [];
        Categorise::deleteAll(['name' => 'position_level']);
        foreach (HrdLevel::find()->all() as $person) {
            $model = new Categorise();
            $model->name = 'position_level';
            $model->code = $person->HR_LEVEL_ID;
            $model->title = $person->HR_LEVEL_NAME;
            $model->save(false);
            $data[] = ['erp' => $model];
        }

        return $data;
    }

    //กลุ่มงาน
    public function Workgroup()
    {
        $data = [];
        Categorise::deleteAll(['name' => 'workgroup']);

        $sql = "SELECT p.`HR_FNAME`,p.`HR_LNAME`,p.`HR_CID`, d.* FROM hrd_department d
        LEFT JOIN hrd_person p ON p.`ID` = d.`LEADER_HR_ID`";

            $querys = Yii::$app->db2->createCommand($sql)
            ->queryAll();

        foreach ($querys as $person) {
            $emp = $this->GetFormCid($person['HR_CID']);
            $model = new Categorise();
            $model->name = 'workgroup';
            $model->code = $person['HR_DEPARTMENT_ID'];
            $model->title = $person['HR_DEPARTMENT_NAME'];
            $model->data_json = [
                'leader'=>  $emp->id
            ];
            $model->save(false);
            $data[] = $model;
        }

        return $data;
    }

    public function GetFormCid($cid)
    {
  

        $model = Employees::findOne(['cid' => $cid]);
        if($model){
            return $model;
        }else{
            return null;
        }
    }

    //แผนก
    public function Department()
    {
        $data = [];
        Categorise::deleteAll(['name' => 'department']);
        $sql = "SELECT
                            d.`HR_DEPARTMENT_ID` as category_id,
                            d.HR_DEPARTMENT_NAME,
                            ss.`HR_DEPARTMENT_SUB_SUB_ID`  as code,
                            ss.`HR_DEPARTMENT_SUB_SUB_NAME` as name
                            FROM hrd_department d
                            LEFT JOIN hrd_department_sub s ON s.`HR_DEPARTMENT_ID` = d.`HR_DEPARTMENT_ID`
                            LEFT JOIN hrd_department_sub_sub ss ON ss.`HR_DEPARTMENT_SUB_ID` = s.`HR_DEPARTMENT_SUB_ID` ORDER BY d.`HR_DEPARTMENT_ID` ASC";
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        foreach ($querys as $query) {
            $model = new Categorise();
            $model->name = 'department';
            $model->category_id = $query['category_id'];
            $model->code = $query['code'];
            $model->title = $query['name'];
            $model->save(false);
        }

        return $data;
    }

// ประวัติครอบครัว
    public function Family($emp_id,$cid)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = [];
        $sql = "SELECT p.`HR_FNAME`,p.`HR_LNAME`,p.`HR_CID`,f.`NAME`,f.`TYPE`,f.`PHONE`,f.`ADDRESS` FROM hrd_tr_family f 
        LEFT JOIN hrd_person p ON p.`ID` = f.`PERSON_ID` 
        WHERE p.`HR_CID` = :cid";
         $querys = Yii::$app->db2->createCommand($sql)
         ->bindParam(':cid', $cid)
         ->queryAll();

         foreach ($querys as $query) {
            $data_json = [
                'fname' => isset(explode(' ',$query['NAME'],2)[0]) ? explode(' ',$query['NAME'],2)[0] : '',
                'lname' => isset(explode(' ',$query['NAME'],2)[1]) ? explode(' ',$query['NAME'],2)[1] : '',
                'family_relation' => $query['TYPE'],
                'phone' => $query['PHONE'],
                'address' => $query['ADDRESS']
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


    //ข้อมูลองกรค์
    public function CompanyInfo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Categorise::findOne(['name' => 'site']);
        $ref = $model->ref;
        $org = InfoOrg::findOne(1);

        $data = [
            'company_name' => $org->ORG_NAME,
            'phone' => $org->ORG_PHONE,
            'fax' => $org->ORG_FAX,
            'email' => $org->ORG_EMAIL,
            'hoscode' => $org->ORG_PCODE,
            'director_name' => $org->CHECK_HR_NAME,
            'address' => $org->ORG_ADDRESS,
            'website' => $org->ORG_WEBSITE,
        ];
        $model->data_json = $data;
        $model->save();
        $this->CreateDir($ref);
        $name = time() . '.jpg';
        file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $org->img_logo);

        $upload = new Uploads;
        $upload->ref = $ref;
        $upload->name = 'company_logo';
        $upload->file_name = $name;
        $upload->real_filename = $name;
        $upload->type = 'png';
        $upload->save(false);

        return $data;
    }
    public function ImportAsset()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = [];

        foreach (AssetArticle::find()->limit(100)->all() as $asset) {
            $checker = Asset::findOne(['fsn' => $asset->ARTICLE_NUM]);
            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            if (!$checker) {
                $model = new Asset();
                $model->ref = $ref;
                $this->CreateDir($ref);
                if ($asset->IMAGES) {

                    $name = time() . '.jpg';
                    file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $asset->IMAGES);

                    $upload = new Uploads;
                    $upload->ref = $ref;
                    $upload->name = 'asset_photo';
                    $upload->file_name = $name;
                    $upload->real_filename = $name;
                    $upload->type = 'jpg';
                    $upload->save(false);
                }

            } else {
                $model = $checker;
            }
            $model->fsn = $asset->ARTICLE_NUM;
            $model->data_json = [
                'name' => $asset->ARTICLE_NAME,

            ];
            $model->name = $asset->ARTICLE_NAME;
            $model->receive_date = $asset->RECEIVE_DATE;
            $model->price = $asset->PRICE_PER_UNIT;
            $model->life = $asset->OLD_USE;
            $model->dep_id = $asset->DEP_ID;
            $model->depre_type = $asset->DECLINE_ID;
            $model->budget_year = $asset->BUDGET_ID;
            $data[] = [
                'status' => $model->save(false),
                'fsn' => $model->fsn,
            ];

        }
        return $data;
    }

    public function actionDemo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->Workgroup();
    }

}
