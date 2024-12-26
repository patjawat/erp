<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Response;
use app\models\Uploads;
use yii\web\Controller;
use yii\bootstrap5\Html;  // ค่าที่นำเข้าจาก component ที่เราเขียนเอง
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;
use app\modules\filemanager\components\FileManagerHelper;

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
            // 'thai_year' => AppHelper::YearBudget(),
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
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Documents model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Documents([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10)
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
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
        $model->doc_date = AppHelper::convertToThai($model->doc_date);
        $model->doc_receive_date = AppHelper::convertToThai($model->doc_receive_date);
        $old_json = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
            $model->doc_receive_date = AppHelper::convertToGregorian($model->doc_receive_date);
            $model->data_json = ArrayHelper::merge($model->data_json, $old_json);
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
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
    public function actionFileComment($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('file_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('file_comment', [
                'model' => $model,
            ]);
        }
    }

    // แสดง File และแสดงความเห็น
    public function actionShareFile($id)
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

    public function actionShow($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest) {
            $id = Yii::$app->request->get('id');
            $fileUpload = Uploads::findOne(['ref' => $model->ref]);
            $type = 'pdf';
            if (!$fileUpload) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            } else {
                $filename = $fileUpload->real_filename;
                $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            }
            if (!$fileUpload && !file_exists($filepath)) {
                throw new \yii\web\NotFoundHttpException('The requested file does not exist.');
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

    /**
     * Deletes an existing Documents model.
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
