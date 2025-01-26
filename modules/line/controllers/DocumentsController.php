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
    
    // แสดง File และแสดงความเห็น
    public function actionComment($id)
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
            return $this->redirect(['view', 'id' => $model->id]);
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
