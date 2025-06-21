<?php
namespace app\modules\line\controllers;


use Yii;
use DateTime;
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
use app\modules\dms\models\DocumentsDetail;
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
        $emp = UserHelper::GetEmployee();
        $checkReading = DocumentsDetail::find()->where(['document_id' => $model->document_id, 'name' => 'read', 'to_id' => $emp->id, 'from_id' => $id])->one();
        if (!$checkReading) {
            $reading = new DocumentsDetail;
            $reading->document_id = $model->document_id;
            $reading->name = 'read';
            $reading->to_id = $emp->id;
            $reading->from_id = $id;
            $reading->doc_read = date('Y-m-d H:i:s');
            $reading->save(false);
        } else {
            if ($checkReading->doc_read == null) {
                $checkReading->doc_read = date('Y-m-d H:i:s');
                $checkReading->save(false);
            }
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->renderAjax('view',['model' => $model]),
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

    
    public function actionShow($ref)
{
    // if (Yii::$app->user->isGuest) {
    //     return $this->redirect(['/line/profile']);
    // }

    $fileUpload = Uploads::findOne(['ref' => $ref]);

    if (!$fileUpload) {
        $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
        $filename = 'pdf-placeholder.pdf';
    } else {
        $filename = $fileUpload->real_filename;
        $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
    }

    if (!file_exists($filepath)) {
        $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
        $filename = 'pdf-placeholder.pdf';
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    Yii::$app->response->headers->set('Content-Type', 'application/pdf');
    Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
    Yii::$app->response->headers->set('Accept-Ranges', 'bytes');
    Yii::$app->response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    Yii::$app->response->headers->set('Pragma', 'no-cache');
    Yii::$app->response->headers->set('Expires', '0');

    return Yii::$app->response->sendFile($filepath, $filename, [
        'mimeType' => 'application/pdf',
        'inline' => true,
    ]);
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
    

       // แสดง File และแสดงความเห็น
       public function actionComment($id)
       {
        try {
      
           $emp = UserHelper::GetEmployee();
           $documentDetail = DocumentsDetail::findOne($id);
           $model = new DocumentsDetail([
               'document_id' => $documentDetail->document_id,
               'to_id' => $emp->id,
               'name' => 'comment'
           ]);
           
           if ($this->request->isPost && $model->load($this->request->post())) {
               Yii::$app->response->format = Response::FORMAT_JSON;
   
               if($model->save()){
                   $model->UpdateDocumentsDetail();
                   return[
                       'status' => 'success'
                   ];
               // ส่งข้อมูลกลับไปยังหน้า view เพื่อให้เห็นว่ามีการ comment เข้ามา'
               // return $this->redirect(['view', 'id' => $model->id]);
               }
           }
           if ($this->request->isAJax) {
               Yii::$app->response->format = Response::FORMAT_JSON;
   
               return [
                   'title' =>'',
                //    'title' =>$this->request->get('title'),
                   'content' => $this->renderAjax('_form_comment', [
                       'model' => $model,
                   ])
               ];
           } else {
               return $this->render('_form_comment', [
                   'model' => $model,
               ]);
           }
                 //code...
        } catch (\Throwable $th) {
            return $this->redirect(['/line/profile']);
        }
       }
       
    // แสดง File และแสดงความเห็น
    public function actionCommentxx($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = new DocumentsDetail([
            'document_id' => $id,
            'to_id' => $emp->id,
            'name' => 'comment'
        ]);
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->UpdateDocumentsDetail();

            if($model->save()){
            // ส่งข้อมูลกลับไปยังหน้า view เพื่อให้เห็นว่ามีการ comment เข้ามา'
            // return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' =>$this->request->get('title'),
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
