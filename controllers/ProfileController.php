<?php

namespace app\controllers;

use app\components\UserHelper;
use app\models\Amphure;
use app\models\District;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeDetail;
use app\modules\usermanager\models\User;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Response;
use yii\helpers\Url;

class ProfileController extends \yii\web\Controller

{
    public function actionIndex()
    {
        $name = $this->request->get('name');
        // $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        // if($model){
        return $this->render('@app/modules/hr/views/employees/view', [
            'model' => $model ? $model : new Employees(),
            'name' => $name,
        ]);
        // }else{
        //     return $this->renderContent('<h1 class="text-center">ไม่พบข้อมูลพนักงาน</h1>');
        // }
    }
    public function actionIndex2()
    {
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        // if($model){
        return $this->render('index2', [
            'model' => $model,
        ]);

    }

    public function actionSetting()
    {
        $emp = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        $id = Yii::$app->user->id;
        $model = User::findOne($id);
        $model->getRoleByUser();
        $model->password = $model->password_hash;
        $model->confirm_password = $model->password_hash;
        $oldPass = $model->password_hash;
        $model->fullname = $model->employee->fullname;
        $model->phone = $model->employee->phone;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($oldPass !== $model->password) {
                    $model->setPassword($model->password);
                }
                if ($model->save()) {
                    $model->assignment();
                    $emp->email = $model->email;
                    $emp->phone = $model->phone;
                    $emp->save(false);
                    return $this->asJson(['success' => true, 'url' => Url::to(['/profile'])]);
                }

            }
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
    
            return $this->asJson(['validation' => $result]);

            //     return [
            //         'status' => 'success',
            //         'url' => Url::to(['/profile'])

            //     ];
            //  }else{
            //      return $this->redirect(['/profile']);

            //  }
        }

        return $this->render('setting', [
            'model' => $model,
        ]);

    }

    public function actionHrd()
    {
        return $this->render('hrd');

    }

    public function actionFormGeneral()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        $amphure = isset($model->province) ? ArrayHelper::map($this->getAmphure($model->province), 'id', 'name') : null;
        $district = isset($model->amphure) ? ArrayHelper::map($this->getDistrict($model->amphure), 'id', 'name') : null;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            } else {

                return ActiveForm::validate($model);
            }

        } else {
            return [
                'title' => 'ข้อมูลทั่วไป',
                'content' => $this->renderAjax('_form_general', [
                    'model' => $model,
                    'amphure' => $amphure,
                    'district' => $district,
                ]),
            ];

        }
    }

    public function actionFormPersonal()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>ข้อมูลส่วนตัว</h4>',
                'content' => $this->renderAjax('_form_personal', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormFamily()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>ครอบครัว</h4>',
                'content' => $this->renderAjax('_form_family', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormAddress()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>ข้อมูลที่อยู่</h4>',
                'content' => $this->renderAjax('_form_address', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormChangname()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>เพิ่มประวัติการเปลี่ยนชื่อ</h4>',
                'content' => $this->renderAjax('_form_changname', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormExperience()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>เพิ่มประสบการณ์</h4>',
                'content' => $this->renderAjax('_form_experience', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormEducation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {

            return [
                'status' => 'success',
                'data' => $model,
            ];
        } else {

            return [
                'title' => 'เพิ่มข้อมูลการศึกษา',
                'content' => $this->renderAjax('_form_education', [
                    'model' => $model,
                ]),
            ];
        }
    }

    protected function getAmphure($id)
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

    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fileName = 'avatar';
        $uploadPath = Yii::getAlias('@webroot') . '/files';

        if (isset($_FILES[$fileName])) {
            $file = \yii\web\UploadedFile::getInstanceByName($fileName);
            $data = \yii\web\UploadedFile::getInstanceByName($fileName);

            // return $file;

            //Print file data
            //print_r($file);

            $model = UserHelper::GetEmployee();
            $fileName = $file->baseName . '.' . $file->extension;
            $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
            $savePath = Yii::getAlias('@webroot') . '/avatar/' . $realFileName;

            if ($file->saveAs($savePath)) {
                $model = UserHelper::GetEmployee();

                // if (!empty($model->avatar)) {
                @unlink(Yii::getAlias('@webroot') . '/avatar/' . $model->avatar);
                // }

                $model->avatar = $realFileName;
                $model->save(false);
                return '/avatar/' . $model->avatar;
            }
        }

        return false;
    }

public function actionHistory(){

    Yii::$app->response->format = Response::FORMAT_JSON;
    $sql = "select CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.date_start')) as DATE) as date_start, year(CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.date_start')) as DATE)) as date_year, CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.salary')) as UNSIGNED) as salary FROM employee_detail WHERE name = 'position' AND emp_id = 8 GROUP BY year(date_start) ORDER BY year(date_start) DESC;";
    $querys =  Yii::$app->db->createCommand($sql)->queryAll();

    $employeesDetail = EmployeeDetail::find()->where(['emp_id'=>8,'name' => 'position'])->all();
    $data = [];
    $categories = [];
    foreach($querys as $model){
        $data[] = $model['salary'];
        $categories[] = $model['date_year'];
    }

    foreach($employeesDetail as $model){
        // $data[] = $model->data_json['salary'];
        // $categories[] = Yii::$app->thaiFormatter->asDate($model->data_json['date_start'],'medium');
    }


    return [
        'name' => '',
        'data' => $data,
        'categories' => $categories
    ];
}

}
