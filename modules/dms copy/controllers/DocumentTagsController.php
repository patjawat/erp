<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\bootstrap5\Html;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentTags;
use app\modules\dms\models\DocumentTagsSearch;

/**
 * DocumentTagsController implements the CRUD actions for DocumentTags model.
 */
class DocumentTagsController extends Controller
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
     * Lists all DocumentTags models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentTagsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DocumentTags model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DocumentTags model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new DocumentTags();
        $model->document_id = $this->request->get('document_id');
        $model->ref = $this->request->get('ref');
        $model->name = $this->request->get('name');

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if($model->save()){
                    return [
                        'title' => $this->request->get('title'),
                        'status' => 'success',
                        'container' => '#document-tag',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if($this->request->isAJax){
            Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'title' => $this->request->get('tilte'),
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ])
                 ];
            }else{
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
    }

    public function actionReqApprove()
    {
       
        if ($this->request->isPost) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $info = SiteHelper::getInfo();
            $emp_id = $info['director_name'];
            $document_id =$this->request->post('document_id');
            $name =$this->request->post('name');
            $check = DocumentTags::findOne(['document_id' =>$document_id,'name' => $name,'emp_id' => $emp_id,'status' => 'DS4']);
            if($check){
                return [
                    'status' => 'error',
                    'container' => '#document-tag',
                ];
            }
            
                $model = new DocumentTags();
                $model->document_id = $document_id;
                $model->ref = $this->request->post('ref');
                $model->name = $name;
                $model->status = 'DS3';
                $model->emp_id = $info['director_name'];
                $model->data_json = ['req_approve_date' => date('Y-m-d H:i:s')];
                $document = Documents::findOne($document_id);
                $document->status = 'DS3';
                $document->save();
                // return $model;
                if($model->save()){
                    return [
                        'title' => $this->request->get('title'),
                        'status' => 'success',
                        'container' => '#document-tag',
                    ];
                }
        } 
    }


    /**
     * Updates an existing DocumentTags model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


     // ตรวจสอบความถูกต้อง
     public function actionCommentValidator()
     {
         \Yii::$app->response->format = Response::FORMAT_JSON;
         $model = new DocumentTags();
         $requiredName = 'ต้องระบุ';
         if ($this->request->isPost && $model->load($this->request->post())) {
              $model->data_json['comment'] == '' ? $model->addError('data_json[comment]', $requiredName) : null;
         }
         foreach ($model->getErrors() as $attribute => $errors) {
             $result[Html::getInputId($model, $attribute)] = $errors;
         }
         if (!empty($result)) {
             return $this->asJson($result);
         }
     }
     
     
    //ลงความเห็น
    // public function actionComment($id)
    // {
    //     $emp = UserHelper::GetEmployee();
    //     $model = DocumentTags::findOne(['document_id' => $id,'name' => 'req_approve']);
    //     $old = $model->data_json;
    //     if ($this->request->isPost && $model->load($this->request->post())) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         $model->status = 'DS4';
    //         $commentDate = [
    //             'comment_date' => date('Y-m-d H:i:s'),
    //             'comment_name' => $emp->fullname,
    //         ];
    //         $model->data_json = ArrayHelper::merge($old,$commentDate,$model->data_json);

    //         $model->save();

    //         //เปลี่ยนสถานะเอกสารเป็น ผอ.ลงนาม
    //         $document = Documents::findOne($model->document_id);
    //         $document->status = 'DS4';
    //         $document->save(false);
            
    //         //ถ้าหาไม่มีให้บันทึกโดยอันโนมัติ
    //          $checkNewTag  = Categorise::findOne(['name' => 'document_comment_tags','title' => $model->data_json['comment']]);
    //          if(!$checkNewTag){
    //              $newTag = new Categorise();
    //              $newTag->name = 'document_comment_tags';
    //              $newTag->title = $model->data_json['comment'];
    //              $newTag->save();
    //          }
            
    //         return [
    //             'status' => 'success',
    //             'container' => '#document-tag',
    //             'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว', 
    //         ];
    //     }


    //     if($this->request->isAJax){
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //             return [
    //                 'title' => $this->request->get('tilte'),
    //                 'content' => $this->renderAjax('_form_comment', [
    //                     'model' => $model,
    //                 ])
    //              ];
    //         }else{
    //             return $this->render('_form_comment', [
    //                 'model' => $model,
    //             ]);
    //         }

    // }

    /**
     * Deletes an existing DocumentTags model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the DocumentTags model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DocumentTags the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DocumentTags::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}