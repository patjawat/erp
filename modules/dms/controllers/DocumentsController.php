<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Response;
use app\models\Uploads;
use yii\web\Controller;
use yii\bootstrap5\Html;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentTags;
use app\modules\dms\models\DocumentSearch;
use app\modules\filemanager\components\FileManagerHelper;  // ค่าที่นำเข้าจาก component ที่เราเขียนเอง

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends Controller
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
     * Lists all Documents models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $group = $this->request->get('document_group');
        $searchModel = new DocumentSearch([
            'document_group' => $group,
            'thai_year' => (Date('Y')+543),
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],
            // ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
        ]);
        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
        ]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Documents model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->renderAjax('view_title',['model' => $model]),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Documents model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        //ถ้าเป็นหนังสทือราชการถ้ปีปัจจบัน
        $model = new Documents([
            'thai_year' => (Date('Y')+543),
            'document_group' => 'receive',
        ]);
        // $model->ref =  substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

        $model->doc_regis_number = $model->runNumber();
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                
                if($model->req_approve == 1){
                    $model->status = 'DS3';
                }
                
                if($model->data_json['department_tag'] == ""){
                    $model->status = 'DS1';
                }else{
                    $model->status = 'DS2';
                }
                $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
                $model->doc_receive_date = AppHelper::convertToGregorian($model->doc_receive_date);
                if($model->doc_expire !=='__/__/____'){
                    $model->doc_expire = AppHelper::convertToGregorian($model->doc_expire);
                }else{
                    $model->doc_expire = "";
                }

                if(!$model->save()){
                    return $model->getErrors();
                }
                return $this->redirect(['index']);
            }
        } else {
            // $model->loadDefaultValues();
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(),10);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Documents model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        // $model->doc_date = AppHelper::convertToThai($model->doc_date);
        // $model->doc_receive_date = AppHelper::convertToThai($model->doc_receive_date);
        $old_json = $model->data_json;
        $model->doc_date = AppHelper::convertToThai($model->doc_date);
        $model->doc_receive_date = AppHelper::convertToThai($model->doc_receive_date);
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
            $model->doc_receive_date = AppHelper::convertToGregorian($model->doc_receive_date);
            if($model->doc_expire !=='__/__/____'){
                $model->doc_expire = AppHelper::convertToGregorian($model->doc_expire);
            }else{
                $model->doc_expire = "";
            }

            if($model->status !== 'DS4'){
                
                if($model->data_json['department_tag'] == ""){
                    $model->status = 'DS1';
                }else{
                    $model->status = 'DS2';
                }
            }
           
            if ($model->save()) {
                
                
                try {
                    
                    $message = $model->topic;
                    $model->sendMessage();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
        // $old = $model->data_json;

        // if ($this->request->isPost && $model->load($this->request->post())) {
        //     Yii::$app->response->format = Response::FORMAT_JSON;
        //     $model->data_json = ArrayHelper::merge($old, $model->data_json);
        //     // return $model;
        //     $model->save();
        //     return $this->redirect(['view', 'id' => $model->id]);
    }


     // ตรวจสอบความถูกต้อง
     public function actionValidator()
     {
         \Yii::$app->response->format = Response::FORMAT_JSON;
         $model = new Documents();
         $requiredName = 'ต้องระบุ';
         if ($this->request->isPost && $model->load($this->request->post())) {
             if (isset($model->doc_date)) {
                 preg_replace('/\D/', '', $model->doc_date) == '' ? $model->addError('doc_date', $requiredName) : null;
             }
             if (isset($model->doc_receive_date)) {
                 preg_replace('/\D/', '', $model->doc_receive_date) == '' ? $model->addError('doc_receive_date', $requiredName) : null;
             }
            
            //  $model->data_json['reason'] == '' ? $model->addError('data_json[reason]', $requiredName) : null;
         }
         foreach ($model->getErrors() as $attribute => $errors) {
             $result[Html::getInputId($model, $attribute)] = $errors;
         }
         if (!empty($result)) {
             return $this->asJson($result);
         }
     }
     
    public function actionGetItems()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // ดึงข้อมูลจากฐานข้อมูลหรือแหล่งข้อมูลอื่น
        $items = Employees::find()
            ->select(['id', 'fname', 'lname'])  // เลือกเฉพาะฟิลด์ที่ต้องการ
            ->andWhere(['status' => 1])
            ->andWhere(['>', 'id', 1])
            ->asArray()
            ->all();

        // คืนค่าในรูปแบบ ['id' => 'value', 'name' => 'label']
        return array_map(function ($item) {
            return ['id' => $item['id'], 'name' => ($item['fname'] . ' ' . $item['lname'])];
        }, $items);
        // เตรียมข้อมูลในรูปแบบ [id => HTML]
        //  $result = [];
        //  foreach ($items as $item) {
        //      $result[$item->id] = $item->getImg() . $item->fname . ' ' . $item->lname;
        //  }

        //  return $result;
    }

    // แสดง File และแสดงความเห็น
    public function actionComment($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = new DocumentTags([
            'document_id' => $id,
            'emp_id' => $emp->id
        ]);
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($model->save()){
                
                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'xxx',
                'content' => $this->renderAjax('_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_comment', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdateComment($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = DocumentTags::findOne($id);
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($model->save()){
                
                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'xxx',
                'content' => $this->renderAjax('_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_comment', [
                'model' => $model,
            ]);
        }
    }


    public function actionDeleteComment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = DocumentTags::findOne($id);
        if($model->created_by == Yii::$app->user->id){

            $model->delete();
            return [
                'status' => 'success',
                'data' => $model,
            ];
        }else{
            return [
                'status' => 'error',
            ];
        }
    }
    
// แสดง File และแสดงความเห็น
public function actionListComment($id)
{
   
    $model = $this->findModel($id);
    
    if ($this->request->isAJax) {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => 'xxx',
            'content' => $this->renderAjax('list_comment', [
                'model' => $model,
            ])
        ];
    } else {
        return $this->render('list_comment', [
            'model' => $model,
        ]);
    }
}



    // แสดง File และแสดงความเห็น
    public function actionClipFile($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fas fa-share"></i> ส่งต่อ',
                'content' => $this->renderAjax('share_file', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('share_file', [
                'model' => $model,
            ]);
        }
    }

    public function actionShow($ref)
    {
        // $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest) {
            $id = Yii::$app->request->get('id');
            $fileUpload = Uploads::findOne(['ref' => $ref]);
            $type = 'pdf';
            if (!$fileUpload) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            } else {
                $filename = $fileUpload->real_filename;
                $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            }
            if (!$fileUpload && !file_exists($filepath)) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            }

            $this->setHttpHeaders($type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;
        } else {
            return false;
        }
    }

    protected function setHttpHeaders($type)
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        if ($type == 'png') {
            \Yii::$app->response->headers->add('content-type', 'image/png');
        }

        if ($type == 'pdf') {
            \Yii::$app->response->headers->add('content-type', 'application/pdf');
        }
    }

    public function actionUploadFile($ref)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fas fa-share"></i> อัพโหลดไฟล์',
                'content' => $this->renderAjax('_upload_file', [
                    'ref' => $ref,
                ])
            ];
        } else {
            return $this->render('_upload_file', [
                'ref' => $ref,
            ]);
        }
    }
    /**
     * Deletes an existing Documents model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $ref = $model->ref;
        if($model->delete()){
            FileManagerHelper::removeUploadDir($ref);   
        }
        

        return $this->redirect(['index']);
    }

    /**
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
