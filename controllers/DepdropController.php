<?php

namespace app\controllers;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Amphure;
use app\models\Categorise;
use app\models\Company;
use app\models\District;
use app\models\Profile;
use app\models\ProfileSearch;
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
use app\modules\sm\models\Product;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

class DepdropController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetAmphur()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $province_id = $parents[0];
                $out = $this->getAmphur($province_id);
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionGetDistrict()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $ids = $_POST['depdrop_parents'];

            $province_id = empty($ids[0]) ? null : $ids[0];
            $amphur_id = empty($ids[1]) ? null : $ids[1];
            if ($province_id != null) {
                $out = $this->getDistrict($amphur_id);
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    protected function getAmphur($id)
    {
        $datas = Amphure::find()->where(['province_id' => $id])->all();
        return $this->MapData($datas, 'id', 'name_th');
    }

    protected function getDistrict($id)
    {
        $datas = District::find()->where(['amphure_id' => $id])->all();
        return $this->MapData($datas, 'id', 'name_th');
    }

    protected function MapData($datas, $fieldId, $fieldName)
    {
        $obj = [];
        foreach ($datas as $key => $value) {
            array_push($obj, ['id' => $value->{$fieldId}, 'name' => $value->{$fieldName}]);
        }
        return $obj;
    }


    public function actionAssetType() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $out = self::getAssetType($cat_id); 
                // the getSubCatList function will query the database based on the
                // cat_id and return an array like below:
                // [
                //    ['id'=>'<sub-cat-id-1>', 'name'=>'<sub-cat-name1>'],
                //    ['id'=>'<sub-cat_id_2>', 'name'=>'<sub-cat-name2>']
                // ]
                return ['output'=>$out, 'selected'=>''];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }

    protected function getAssetType($id)
    {
        $datas = Categorise::find()->where(['category_id' => $id])->all();
        return $this->MapData($datas, 'code', 'title');
    }

    // บุคลากร
    public function actionEmployee($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Employees::find()
            ->Where(['or', ['LIKE', 'fname', $q]])
            // ->andWhere(['name' => 'position_group'])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->cid,
                'text' => $model->fullname,
                'title' => $model->fname,
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }

    // บุคลากร
    public function actionEmployeeById($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Employees::find()
            ->Where(['or', ['LIKE', 'fname', $q]])
            ->andWhere(['<>','user_id','0'])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'text' => $model->getAvatar(false),
                'fullname' => $model->fullname,
                'position_name' => $model->positionName(),
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }

    public function actionEmployeeByUserId($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Employees::find()->where(['is not', 'user_id', null])
            ->andWhere(['or', ['LIKE', 'fname', $q]])
            // ->andWhere(['name' => 'position_group'])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->user_id,
                'text' => $model->getAvatar(false),
                'fullname' => $model->fullname,
                'position_name' => $model->positionName(),
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }


    public function actionProduct($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Product::find()->where(['name' => 'asset_item','group_id' => 4])
            ->andWhere(['or', ['LIKE', 'title',$q]])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->code,
                'text' => $model->Avatar(false),
                'fullname' => $model->title,
                'avatar' => $model->Avatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }



    // ตำแหน่งกลุ่มบุคลากร
    public function actionPositionGroupList($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Categorise::find()
            ->Where(['or', ['LIKE', 'title', $q]])
            ->andWhere(['name' => 'position_group'])
            ->limit(1000)
            ->all();
        $data = [['id' => '', 'text' => '', 'position_type' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->code,
                'text' => $this->renderAjax('@app/modules/hr/views/position/poaition_group_ajax_template', ['model' => $model]),
                'title' => $model->name,
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }

    // ตำแหน่งบุคลากร
    public function actionPositionList($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Categorise::find()
            ->Where(['or', ['LIKE', 'title', $q]])
            ->andWhere(['name' => 'position_name'])
            ->limit(1000)
            ->all();
        $data = [['id' => '', 'text' => '', 'position_type' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->code,
                'text' => $this->renderAjax('@app/modules/hr/views/position/poaition_ajax_template', ['model' => $model]),
                'title' => $model->title,
            ];
        }
        return [
            'results' => $data,
            'items' => $model
        ];
    }

    
    public function actionGetVendor()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = Categorise::findOne(['code' => $id,'name' => 'vendor']);
            return $model;
    }

    public function actionCategoriseByCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $code = $this->request->get('code');
        $name = $this->request->get('name');
        $model = CategoriseHelper::CategoriseByCodeName($code, $name);
        if ($model->name == 'position_name') {
            return [
                'position_name' => $model->title,
                'position_group' => $model->category_id,
                'position_group_text' => $model->positionGroup->title,
                'position_type' => $model->positionGroup->category_id,
                'position_type_text' => $model->positionGroup->positionType->title
            ];
        } else if ($model->name == 'asset_name') {
            // $model->code = \mdm\autonumber\AutoNumber::generate($model->code.'/?.???');
            return $model;
        } else {
            return $model;
        }
    }

    public function actionGetFsn()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $asset_item = $this->request->get('asset_item');
        $name = $this->request->get('name');
        $fsnAuto = $this->request->get('fsn_auto');
        $model = CategoriseHelper::CategoriseByCodeName($asset_item, $name);
        $asset = Asset::findOne(['asset_item' => $asset_item]);
        $year = substr((date('Y') + 543), -2, 2);
        $number = $asset_item . '/' . $year . '.';
        // return $asset_item;
        // $auto = \mdm\autonumber\AutoNumber::generate($number.'?');
        if ($asset) {
            $fsn = $asset_item . '/' . date('Y');
        } else {
            $fsn = $asset_item . '/' . $year . '.1';
        }
        if (!$fsnAuto) {
            return [
                'fsn' => $asset_item . '/',
            ];
        }
    }

    public function actionCompanyList($q = null, $id = null)
    {
        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; //กำหนดการแสดงผลข้อมูลแบบ json
        // $out = ['results'=>['id'=>'','text'=>'']];
        // if(!is_null($q)){
        //     $query = new \yii\db\Query();
        //     $query->select('hospcode as id,name as text')
        //             ->from('company')
        //             ->where("name LIKE '%".$q."%'")
        //             ->limit(20);
        //     $command = $query->createCommand();
        //     $data = $command->queryAll();
        //     $out['results'] = array_values($data);
        // }else if($id>0){
        //     $out['results'] = ['id'=>$id, 'text'=>  Company::find($id)->name];
        // }
        // return $out;

        Yii::$app->response->format = Response::FORMAT_JSON;
        // $visit_ = TCDSHelper::getVisit();
        // $med = Medication::find()->where(['vn' => $visit_['vn']])->all();
        $clientCodes = Company::find()
            ->Where([
                'or',
                // ['like', 'general_name', $q . '%', false]]
                ['LIKE', 'name', $q],
                ['LIKE', 'hospcode', $q],
            ])
            ->limit(1000)
            ->orderBy(['hospcode' => SORT_ASC])
            ->all();
        $data = [['id' => '', 'text' => '', 'name' => '']];
        foreach ($clientCodes as $clientCode) {
            $data[] = [
                'id' => $clientCode->hospcode,
                'text' => '(<code>' . $clientCode->hospcode . '</code>)' . '&nbsp;<span class="text-primary">' . $clientCode->name . '</b>',
                'name' => $clientCode->name,
                'address' => $clientCode->addrpart,
                'province' => $clientCode->province_name,
            ];
        }
        return [
            'results' => $data,
            'items' => $clientCodes
        ];
    }

    public function actionHospcode($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $clientCodes = Hospcode::find()
            ->Where([
                'or',
                // ['like', 'general_name', $q . '%', false]]
                ['LIKE', 'hospcode', $q],
                ['LIKE', 'name', $q],
                ['LIKE', 'province_name', $q],
            ])
            ->limit(1000)
            // ->orderBy(['trade_name' => SORT_ASC])
            ->all();
        $data = [['id' => '', 'text' => '', 'province_name' => '']];
        foreach ($clientCodes as $clientCode) {
            $data[] = [
                'id' => $clientCode->hospcode,
                'text' => '(<code>' . $clientCode->hospcode . '</code>)' . '&nbsp;<span class="text-primary">' . $clientCode->name . ' <code>' . $clientCode->province_name . '</code></b>',
                'name' => $clientCode->name,
                'address' => $clientCode->addrpart,
                'province' => $clientCode->province_name,
            ];
        }
        return [
            'results' => $data,
            'items' => $clientCodes
        ];
    }

    public function actionAddress()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = $this->request->get('q');

        $sql = "SELECT 
        provinces.id as province_id, 
        provinces.name_th as province_name, 
        amphures.id as amphure_id, 
        amphures.name_th as amphure_name, 
        districts.id as district_id, 
        districts.name_th as district_name, 
        zip_code
        FROM districts 
        LEFT JOIN amphures ON amphures.id = districts.amphure_id
        LEFT JOIN provinces ON provinces.id = amphures.province_id
        WHERE CONCAT(provinces.name_th ,amphures.name_th , districts.name_th,zip_code)  LIKE '%" . $q . "%'";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->queryAll();

        $data = [[
            'id' => '',
            'text' => '',
            'district_id' => '',
            'district_name' => '',
            'amphure_name' => '',
            'province_id' => '',
            'province_name' => '',
            'fulltext' => ''
        ]];
        foreach ($query as $clientCode) {
            $data[] = [
                'id' => $clientCode['zip_code'],
                'text' => 'ต. ' . $clientCode['district_name'] . ' ' . 'อ. ' . $clientCode['amphure_name'] . ' จ. ' . $clientCode['province_name'] . ' <code>' . $clientCode['zip_code'] . '</code>',
                'fulltext' => 'ตำบล' . $clientCode['district_name'] . ' ' . 'อำเภอ' . $clientCode['amphure_name'] . ' จังหวัด' . $clientCode['province_name'] . ' ' . $clientCode['zip_code'],
                'district_id' => $clientCode['district_id'],
                'district_name' => $clientCode['district_name'],
                'amphure_id' => $clientCode['amphure_id'],
                'amphure_name' => $clientCode['amphure_name'],
                'province_id' => $clientCode['province_id'],
                'province_name' => $clientCode['province_name'],
            ];
        }
        return [
            'results' => $data,
            'items' => $query
        ];
    }

    public function actionCareTream()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = $this->request->get('q');

        $sql = "SELECT *
        FROM profile 
        WHERE CONCAT(fname ,lname )  LIKE '%" . $q . "%'";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->queryAll();

        $data = [[
            'id' => '',
            'text' => '',
        ]];
        foreach ($query as $clientCode) {
            $data[] = [
                'id' => $clientCode['user_id'],
                'text' => $clientCode['fname'] . ' ' . $clientCode['lname'] . ' ' . AppHelper::inithospcode($clientCode['hospcode']),
            ];
        }
        return [
            'results' => $data,
            'items' => $query
        ];
    }
}
