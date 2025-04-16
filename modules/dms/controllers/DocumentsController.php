<?php

namespace app\modules\dms\controllers;

use Yii;
use DateTime;
use yii\web\Response;
use app\models\Uploads;
use yii\web\Controller;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;  // ค่าที่นำเข้าจาก component ที่เราเขียนเอง
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentTags;
use app\modules\dms\models\DocumentSearch;
use app\modules\dms\models\DocumentsDetail;
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
    public function actionReceive()
    {
        $searchModel = new DocumentSearch([
            'thai_year' => (Date('Y') + 543),
            'document_group' => 'receive',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['document_group' => 'receive']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
            ['like', 'doc_number', $searchModel->q],
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
        ]]);
        return $this->render('list_receive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSend()
    {
        $searchModel = new DocumentSearch([
            'thai_year' => (Date('Y') + 543),
            'document_group' => 'send',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['document_group' => 'send']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
            ['like', 'doc_number', $searchModel->q],
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
        ]]);
        return $this->render('list_send', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAppointment()
    {
        $searchModel = new DocumentSearch([
            'thai_year' => (Date('Y') + 543),
            'document_group' => 'appointment',
            'document_type' => 'DT9',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['document_group' => 'appointment']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
            ['like', 'doc_number', $searchModel->q],
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
        ]]);
        return $this->render('list_appointment', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAnnounce()
    {
        $searchModel = new DocumentSearch([
            'thai_year' => (Date('Y') + 543),
            'document_group' => 'announce',
            'document_type' => 'DT5',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['document_group' => 'announce']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
            ['like', 'doc_number', $searchModel->q],
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
        ]]);
        return $this->render('list_announce', [
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
        // $this->layout = '@app/views/layouts/document';
        $this->layout = '@app/themes/v3/layouts/theme-v/document_layout';
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->renderAjax('view_title', ['model' => $model]),
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
        // ถ้าเป็นหนังสทือราชการถ้ปีปัจจบัน
        $model = new Documents([
            'thai_year' => (Date('Y') + 543),
            'document_group' => $this->request->get('document_group'),
            'document_type' => $this->request->get('document_type')
        ]);
        $model->doc_transactions_date = AppHelper::convertToThai(date('Y-m-d'));
        $dateTime = new DateTime();
        $time = $dateTime->format('H:i');
        $model->doc_time = $time;
        // $model->ref =  substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

        $model->doc_regis_number = $model->runNumber();
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
                    $model->doc_transactions_date = AppHelper::convertToGregorian($model->doc_transactions_date);
                    if ($model->doc_expire !== '') {
                        $model->doc_expire = AppHelper::convertToGregorian($model->doc_expire);
                    } else {
                        $model->doc_expire = '';
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }

                if (!is_numeric($model->document_org)) {
                    $model->document_org = $this->UpdateDocOrg($model);
                }

                if ($model->save()) {
                    $model->UpdateDocumentTags();
                } else {
                    return $model->getErrors();
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            // $model->loadDefaultValues();

            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
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
        // $model->doc_transactions_date = AppHelper::convertToThai($model->doc_transactions_date);
        $old_json = $model->data_json;
        try {
            $model->doc_expire = AppHelper::convertToThai($model->doc_expire);
            $model->doc_date = AppHelper::convertToThai($model->doc_date);
            $model->doc_transactions_date = AppHelper::convertToThai($model->doc_transactions_date);
        } catch (\Throwable $th) {
            // throw $th;
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            // return $model->data_json['send_line'];

            // $result = '[' . $model->data_json['tags_department'] . ']'; // เพิ่ม [ และ ] รอบสตริง
            try {
                $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
                $model->doc_transactions_date = AppHelper::convertToGregorian($model->doc_transactions_date);
                if ($model->doc_expire !== '') {
                    $model->doc_expire = AppHelper::convertToGregorian($model->doc_expire);
                } else {
                    $model->doc_expire = '';
                }
            } catch (\Throwable $th) {
                // throw $th;
            }

            if (!is_numeric($model->document_org)) {
                $model->document_org = $this->UpdateDocOrg($model);
            }

            if ($model->save()) {
                $model->UpdateDocumentTags();
                return $this->redirect([$model->document_group]);
            } else {
                return $model->getErrors();
            }
        } else {
            $model->loadDefaultValues();
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

    // ตรวจสอบว่ามีอุปกรณ์รายการใหม่หรือไม่
    protected function UpdateDocOrg($model)
    {
        // try {
        $title = $model->document_org;
        $check = Categorise::find()->where(['name' => 'document_org', 'title' => $title])->one();
        if (!$check) {
            $maxCode = Categorise::find()->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])->where(['like', 'name', 'document_org'])->scalar();
            $newModel = new Categorise();
            $newModel->code = ($maxCode + 1);
            $newModel->name = 'document_org';
            $newModel->title = $title;
            $newModel->save(false);
            return $newModel->code;
        }

        // } catch (\Throwable $th) {
        // }
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
            if (isset($model->doc_transactions_date)) {
                preg_replace('/\D/', '', $model->doc_transactions_date) == '' ? $model->addError('doc_transactions_date', $requiredName) : null;
            }

            // $docRegisNumber = Documents::find()->where(['document_group' => $model->document_group,'doc_regis_number' => $model->doc_regis_number,'thai_year' => $model->thai_year])->one();
            // if($docRegisNumber){
            //     if($docRegisNumber->id !== $model->id){
            //         $model->addError('doc_regis_number', 'เลขทะเบียนซ้ำ');
            //     }

            // }

            // $docNumber = Documents::find()->where(['document_group' => $model->document_group,'doc_number' => $model->doc_number,'thai_year' => $model->thai_year])->one();
            // if($docNumber){
            //     $model->addError('doc_number', 'เลขทะเบียนซ้ำ');
            // }

            //  $model->data_json['reason'] == '' ? $model->addError('data_json[reason]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    // ตรวจสอบความถูกต้องของ comment
    public function actionCommentValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DocumentsDetail();
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

            if ($model->save()) {
                $model->UpdateDocumentsDetail();
                return [
                    'status' => 'success'
                ];
                // ส่งข้อมูลกลับไปยังหน้า view เพื่อให้เห็นว่ามีการ comment เข้ามา'
                // return $this->redirect(['view', 'id' => $model->document_id]);
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
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
        $model = DocumentsDetail::findOne($id);

        $tags = DocumentsDetail::find()->where(['name' => 'comment', 'document_id' => $model->document_id])->all();
        $list = ArrayHelper::map($tags, 'tag_id', 'tag_id');

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->save()) {
                $model->UpdateDocumentsDetail();
                return [
                    'status' => 'success'
                ];
                // return [
                //     'status' => 'success',
                //     'data' => $model,
                // ];
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
        $model = DocumentsDetail::findOne($id);
        if ($model->created_by == Yii::$app->user->id) {
            $model->delete();
            return [
                'status' => 'success',
                'data' => $model,
            ];
        } else {
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
                'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น',
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
        if ($model->delete()) {
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
