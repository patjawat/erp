<?php

namespace app\modules\hr\controllers;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\EmployeeDetailSearch;
use app\modules\hr\models\Employees;
use yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\bootstrap5\ActiveForm;
use app\modules\hr\models\Organization;

/**
 * EmployeeDetailController implements the CRUD actions for EmployeeDetail model.
 */
class EmployeeDetailController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all EmployeeDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
    /**
     * Displays a single EmployeeDetail model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $name = $this->request->get('name');
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax('./views/' . $name, [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('./views/' . $name, [
                'model' => $model,
            ]);
        }

    }

    /**
     * Creates a new EmployeeDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $id = $this->request->get('id');
        $emp_id = $this->request->get('emp_id');
        $name = $this->request->get('name');
        // ดึงตำแหน่งล่าสุกมา ปิดไว้ก่อนยังไม่ได้ใช้
        if($name == "position"){
            $last = EmployeeDetail::find()->where(['emp_id' => $emp_id])
            ->orderBy(
                new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
            )->one();
        }else{
            $last=false;
        }
        

        $model = new EmployeeDetail([
            // 'data_json' => $last ? $last->data_json : '',
            'emp_id' => $emp_id,
            'name' => $name,
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $array2 = [
                    'date_start' => isset($model->data_json['date_start']) ? AppHelper::DateToDb($model->data_json['date_start']) : '',
                    'date_end' => isset($model->data_json['date_end']) ? AppHelper::DateToDb($model->data_json['date_end']) : '',
                ];

                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->data_json = ArrayHelper::merge($model->data_json, $array2);
                $model->save(false);

                //update ประวติทั่วไป
                // if($name == 'posision' && $model->data_json['update_status'] == "2")
                if ($name == 'position') {
                    $this->UpdatePosition($model);
                }

                if ($model->name == 'education') {
                    $this->UpdateEducation($model);
                }

                return [
                    'status' => 'success',
                    'data' => $model,
                    'container' => '#hr-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    
    /**
     * Updates an existing EmployeeDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $name = $this->request->get('name');
        $model = $this->findModel($id);

        $arrayUpdate = [
            'date_start' => isset($model->data_json['date_start']) ? AppHelper::DateFormDb($model->data_json['date_start']) : '',
            'date_end' => isset($model->data_json['date_end']) ? AppHelper::DateFormDb($model->data_json['date_end']) : '',
        ];
        $model->data_json = ArrayHelper::merge($model->data_json, $arrayUpdate);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $array2 = [
                'date_start' => isset($model->data_json['date_start']) ? AppHelper::DateToDb($model->data_json['date_start']) : '',
                'date_end' => isset($model->data_json['date_end']) ? AppHelper::DateToDb($model->data_json['date_end']) : '',
            ];

            $model->data_json = ArrayHelper::merge($model->data_json, $array2);
            Yii::$app->response->format = Response::FORMAT_JSON;
            //return $model;
            $model->save();
            //update ประวติทั่วไป

            if ($model->name == 'position') {
                $this->UpdatePosition($model);
            }

            if ($model->name == 'education') {
                $this->UpdateEducation($model);
            }

            return [
                'status' => 'success',
                'data' => $model,
                'container' => '#hr-container',
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function UpdatePosition($data)
    {
        try {

            $emp = Employees::findOne($data->emp_id);

            //หาตำแหน่งล่าสุดจากวันที่
            $model = EmployeeDetail::find()->where(['name' => 'position', 'emp_id' => $data->emp_id])
                ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc"))->one();
            // get position By Tree diagram
            //$position = CategoriseHelper::TreeById($model->data_json['position_name']);

            // get poditionBy Category design
            if ($model) {
                        $department_name = Organization::findOne($model->data_json['department']);
                        $emp->position_name = $model->data_json['position_name'];
                        $emp->position_group = $model->data_json['position_group'];
                        $emp->position_type = $model->data_json['position_type'];
                        if( $model->data_json['position_type_text'] != 'ข้าราชการ'){
                            $emp->position_level = null;
                        }
                        $array2 = [
                            'position_name_text' => $model->data_json['position_name_text'],
                            'position_group' => $model->data_json['position_group_text'],
                            'position_type' => $model->data_json['position_type_text'],
                            'position_level_text' => $model->data_json['position_level_text'],
                            'status_text' => $model->data_json['status_text'],
                            'department_text' => $department_name ? $department_name->name : ''
                        ];
                        $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);

                if (isset($model->data_json['status'])) {
                    $emp->status = $model->data_json['status'];
                }

                if (isset($model->data_json['position_number'])) {
                    $emp->position_number = $model->data_json['position_number'];
                }
                if (isset($model->data_json['position_type'])) {
                    $emp->position_type = $model->data_json['position_type'];
                }
                if (isset($model->data_json['position_level'])) {
                    $emp->position_level = $model->data_json['position_level'];
                }
                if (isset($model->data_json['salary'])) {
                    $emp->salary = $model->data_json['salary'];
                }
                $emp->department = $model->data_json['department'];
                // return $emp->position_name;
            } else {
                $emp->position_name = null;
                $emp->position_group = null;
                $emp->position_type = null;
                $emp->position_level = null;
                $array2 = [
                    'position_name_text' => '',
                    'position_group' => '',
                    'position_type' => ''
                ];
                $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);
            }
            return $emp->save(false);
            //code...
        } catch (\Throwable $th) {
            return throw $th;
            //     return 'not save';
        }
    }

    public function UpdateEducation($data)
    {
        try {

            $emp = Employees::findOne($data->emp_id);

            $model = EmployeeDetail::find()->where(['name' => 'education', 'emp_id' => $data->emp_id])
                ->orderBy(
                    new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
                )->one();

            if (isset($model->data_json['education'])) {
                $emp->education = $model->data_json['education'];
            }

            if (isset($position['position_group'])) {
                $emp->position_group = $position['position_group_id'];
            }

            return $emp->save(false);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new EmployeeDetail();
        
        
        if ($this->request->isPost && $model->load($this->request->post()))
         { 
        $requiredName = "ต้องระบุ"; 
            //ตรวจสอบตำแหน่ง
        if($model->name == "position"){
            $model->data_json['statuslist'] == "" ? $model->addError('data_json[statuslist]',$requiredName) : null;
            $model->data_json['position_name'] == "" ? $model->addError('data_json[position_name]',$requiredName) : null;
            $model->data_json['position_number'] == "" ? $model->addError('data_json[position_number]',$requiredName) : null;
            // $model->data_json['department'] == "" ? $model->addError('data_json[department]',$requiredName) : null;

            if($model->data_json['position_type_text'] == 'ข้าราชการ')
            {
                $model->data_json['position_level'] == "" ? $model->addError('data_json[position_level]','ข้าราชการ'.$requiredName) : null;
            }
            $model->data_json['status'] == "" ? $model->addError('data_json[status]', 'สถานะต้องไม่ว่าง') : null;
            $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
        }
        
        // ตรวจสอบการศึกษา
        if($model->name == "education")
        {
            $model->data_json['education'] == "" ? $model->addError('data_json[education]',$requiredName) : null;
            $model->data_json['major'] == "" ? $model->addError('data_json[major]',$requiredName) : null;
            $model->data_json['institute'] == "" ? $model->addError('data_json[institute]',$requiredName) : null;
            $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
            $model->data_json['date_end'] == "__/__/____" ? $model->addError('data_json[date_end]',$requiredName) : null;
        
        }

        //ตรวจสอบรางวัลเชิดชูเกียรติ
        if($model->name == "award")
        {
            $model->data_json['award_name'] == "" ? $model->addError('data_json[award_name]', 'ชื่อรางวัลต้องไม่ว่าง') : null;
            $model->data_json['award_company_name'] == "" ? $model->addError('data_json[award_company_name]',$requiredName) : null;
            $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
            $model->data_json['date_end'] == "__/__/____" ? $model->addError('data_json[date_end]',$requiredName) : null;
        
        }

          //ตรวจสอบประวัติครอบครัว
          if($model->name == "family")
          {
              $model->data_json['prefix'] == "" ? $model->addError('data_json[prefix]',$requiredName) : null;
              $model->data_json['fname'] == "" ? $model->addError('data_json[fname]',$requiredName) : null;
              $model->data_json['lname'] == "" ? $model->addError('data_json[lname]',$requiredName) : null;
              $model->data_json['family_relation'] == "" ? $model->addError('data_json[family_relation]',$requiredName) : null;
              $model->data_json['status'] == "" ? $model->addError('data_json[status]',$requiredName) : null;
              $model->data_json['status'] == "" ? $model->addError('data_json[status]',$requiredName) : null;
          }
          
          
           //ครื่องราชอิสริยาภรณ์
           if($model->name == "insignia")
           {
               $model->data_json['name'] == "" ? $model->addError('data_json[name]', 'ชื่อรางวัลต้องไม่ว่าง') : null;
               $model->data_json['company_name'] == "" ? $model->addError('data_json[company_name]',$requiredName) : null;
               $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
               $model->data_json['date_end'] == "__/__/____" ? $model->addError('data_json[date_end]',$requiredName) : null;
           }

             //ประวัติการเปลี่ยนชื่อ
             if($model->name == "rename")
             {
                 $model->data_json['rename_type'] == "" ? $model->addError('data_json[rename_type]', $requiredName) : null;
                 $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
             }

             foreach ($model->getErrors() as $attribute => $errors) {
                 $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }
            }
    }


    /**
     * Deletes an existing EmployeeDetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $item = $this->findModel($id);
        $this->findModel($id)->delete();

        if ($item->name == 'position') {
            $this->UpdatePosition($item);
        }

        if ($item->name == 'education') {
            $this->UpdateEducation($item);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 'success',
            'data' => $item,
            'container' => '#hr-container',
        ];
    }

    /**
     * Finds the EmployeeDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return EmployeeDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EmployeeDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDemo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = EmployeeDetail::find()->where(['emp_id' => 5])
            ->orderBy(
                new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
            )->one();

        return $model;

    }
}
