<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\models\Amphure;
use app\models\Company;
use app\models\District;
use app\models\Categorise;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\EmployeePosition;
use app\modules\sm\models\Product;
use app\components\CategoriseHelper;
use app\components\DateFilterHelper;
use app\modules\hr\models\Employees;
use yii\helpers\Html;

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

    public function actionAssetType()
    {
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
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
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
            ->andWhere(['status' => 1])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
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

    // พนักงานขอบรถ
    public function actionDriver()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = Yii::$app->request->get('q');

        if ($q == '') {
            $querys = Employees::find()
                ->from('employees e')
                ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
                ->where(['a.item_name' => 'driver'])
                ->all();
        } else {
            $querys = Employees::find()
                ->from('employees e')
                ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
                ->where(['a.item_name' => 'driver'])
                ->andWhere([
                    'or',
                    ['LIKE', 'fname', $q],
                    ['LIKE', 'lname', $q],
                ])
                ->all();
        }

        $data = [['id' => '', 'text' => '']];
        foreach ($querys as $model) {
            $data[] = [
                'id' => $model->id,
                'text' => $model->getAvatar(false),
                'fullname' => $model->fullname,
                'position_type_id' => $model->position_type,
                'position_name' => $model->positionName(),
                'month_of_service' => $model->workLife()['month'],
                'years_of_service' => $model->workLife()['year'],
                'position_name_text' => $model->data_json['position_name_text'],
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $querys
        ];
    }

    // บุคลากร (รองรับ exclude_emp_id สำหรับฟอร์มที่ไม่ให้เลือกตัวเอง เช่น คำขอบคุณ)
    public function actionEmployeeById($q = null, $id = null, $exclude_emp_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = Employees::find()
            ->andWhere(['<>', 'user_id', '0'])
            ->andWhere(['status' => 1]);

        $q = trim((string) $q);
        if ($q !== '') {
            $query->andWhere(['or',
                ['LIKE', 'fname', $q],
                ['LIKE', 'lname', $q],
            ]);
        }
        if ($exclude_emp_id !== null && $exclude_emp_id !== '') {
            $query->andWhere(['!=', 'id', (int) $exclude_emp_id]);
        }
        $query->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->limit(30);
        $querys = $query->all();

        $data = [['id' => '', 'text' => '']];
        foreach ($querys as $model) {
            $data[] = [
                'id' => $model->id,
                'text' => $model->getAvatar(false),
                'fullname' => $model->fullname,
                'position_type_id' => $model->position_type,
                'position_name' => $model->positionName(),
                'month_of_service' => $model->workLife()['month'],
                'years_of_service' => $model->workLife()['year'],
                'position_name_text' => $model->data_json['position_name_text'] ?? '-',
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model ?? []
        ];
    }

    public function actionEmployeeByUserId($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Employees::find()
            ->where(['is not', 'user_id', null])
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
            'items' => $model ?? []
        ];
    }

    public function actionProduct($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Product::find()
            ->where(['name' => 'asset_item', 'group_id' => 'EQUIP'])
            ->andWhere(['or', ['LIKE', 'title', $q]])
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



    // บุคลากร
    public function actionCar($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $querys = Asset::find()
            ->Where(['or', 
            ['LIKE', 'code', $q],
            ])
            // ->andWhere(['<>', 'user_id', '0'])
            ->limit(10)
            ->all();

        $data = [['id' => '', 'text' => '']];
        foreach ($querys as $model) {
            $data[] = [
                'id' => $model->id,
                'text' => $model->getAvatar(false),
                'fullname' => $model->fullname,
                'position_type_id' => $model->position_type,
                'position_name' => $model->positionName(),
                'month_of_service' => $model->workLife()['month'],
                'years_of_service' => $model->workLife()['year'],
                'position_name_text' => $model->data_json['position_name_text'] ?? '-',
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $model ?? []
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
            ->where(['name' => 'position_name'])
            ->andWhere(['or', ['LIKE', 'title', $q]])
            ->limit(10)
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

    // ตำแหน่งพนักงานแบบใหม่
    public function actionEmployeePositionList($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = EmployeePosition::find()
            ->alias('p')
            ->with(['employeePositionGroup'])
            ->joinWith(['employeePositionGroup pg'])
            ->where(['p.active' => 1]);

        $q = trim((string) $q);
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'p.title', $q],
                ['like', 'p.legacy_code', $q],
                ['like', 'pg.title', $q],
            ]);
        }

        $models = $query
            ->orderBy(['pg.sort' => SORT_ASC, 'p.sort' => SORT_ASC, 'p.id' => SORT_ASC])
            ->all();

        $data = [['id' => '', 'text' => '']];
        $uniqueModels = [];
        foreach ($models as $model) {
            $key = $this->normalizeEmployeePositionTitleKey($model->title ?? '');
            if ($key === '' || isset($uniqueModels[$key])) {
                continue;
            }

            $uniqueModels[$key] = $model;
        }

        foreach ($uniqueModels as $model) {
            $groupTitle = trim((string) ($model->employeePositionGroup->title ?? ''));
            $label = trim((string) $model->title);
            if ($groupTitle !== '') {
                $label .= ' (' . $groupTitle . ')';
            }

            $data[] = [
                'id' => $model->id,
                'text' => $label,
                'html' => Html::tag(
                    'div',
                    Html::tag('div', Html::encode($model->title), ['class' => 'fw-semibold']) .
                    ($groupTitle !== ''
                        ? Html::tag('div', 'กลุ่ม: ' . Html::encode($groupTitle), ['class' => 'small text-muted'])
                        : '')
                ),
                'title' => $model->title,
                'employee_position_id' => $model->id,
                'employee_position_text' => $label,
                'employee_position_legacy_code' => $model->legacy_code ?? '',
                'employee_position_group_id' => $model->employee_position_group_id,
                'employee_position_group_text' => $groupTitle,
            ];
        }

        return [
            'results' => $data,
            'items' => array_values($uniqueModels),
            'total_count' => count($uniqueModels),
        ];
    }

    private function normalizeEmployeePositionTitleKey($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', ' ', $value);
        if ($value === null) {
            return '';
        }

        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    public function actionGetVendor()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = Categorise::findOne(['code' => $id, 'name' => 'vendor']);
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
        $year = substr(AppHelper::YearBudget(), -2, 2);
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
                'text' => 'ต. ' . $clientCode['district_name'] . ' ' . 'อ. ' . $clientCode['amphure_name'] . ' จ. ' . $clientCode['province_name'] . $clientCode['zip_code'],
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

    public function actionThaiYear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $thaiYear = $this->request->get('thai_year');
        $date = AppHelper::BudgetYearRange($thaiYear);
        return [
            'date_start' => $date['start'],
            'date_end' => $date['end'],
            'thai_date_start' => AppHelper::convertToThai($date['start']),
            'thai_date_end' => AppHelper::convertToThai($date['end']),
        ];
    }

    public function actionDateFilter()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $dateStart = '';
        $dateEnd = '';
        $dateFilter = $this->request->get('date_filter');
            if ($dateFilter) {
                $range = DateFilterHelper::getRange($dateFilter);
                $dateStart = AppHelper::convertToThai($range[0]);
                $dateEnd = AppHelper::convertToThai($range[1]);
        }
        return [
            'date_start' => $dateStart,
            'date_end' => $dateEnd
        ];
    }



    // บุคลากร ที่เป็นหัวหน้า
    public function actionGetLeader($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = (new \yii\db\Query())
            ->select(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader1'))"))
            ->from('tree')
            ->union(
                (new \yii\db\Query())
                    ->select(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader2'))"))
                    ->from('tree')
            )
            ->column();

        $models = Employees::find()
            ->where(['like', 'fname', $q])
            ->andWhere(['id' => $ids])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'text' => $model->fullname,
                'title' => $model->fname,
                // 'avatar' => Html::img($model->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])
                'avatar' => $model->getAvatar(false)
            ];
        }
        return [
            'results' => $data,
            'items' => $ids
        ];
    }

}
