<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use app\models\Uploads;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\data\ActiveDataProvider;
use app\components\DateFilterHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;
use app\modules\dms\models\DocumentsDetail;
use app\modules\dms\models\DocumentTagsSearch;
use app\modules\dms\models\DocumentsDetailSearch;
use app\modules\filemanager\components\FileManagerHelper;

class DocumentsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $emp = UserHelper::GetEmployee();
        $searchModel = new DocumentSearch([
            'thai_year' => (date('Y') + 543),
            'date_filter' => 'today'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith([
            'documentTags' => function ($query) {
                $query->alias('d_tags')
                    ->andOnCondition(['d_tags.name' => 'tags']);
            }
        ]);
        $dataProvider->query->joinWith([
            'docRead' => function ($query) {
                $query->alias('d_read')
                    ->andOnCondition(['d_read.name' => 'read']);
            }
        ]);

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
            ['like', 'doc_number', $searchModel->q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, '$.des'))"), $searchModel->q],
        ]);
        if ($searchModel->q_status == 'Y') {
            $dataProvider->query->andFilterWhere(['d_read.bookmark' => 'Y']);
        } else {
            $dataProvider->query->andFilterWhere(['status' => $searchModel->q_status]);
        }
        $dataProvider->query->andFilterWhere(['d_tags.to_id' => $emp->id]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->date_filter == '' && $searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        $dataProvider->setSort(['defaultOrder' => [
            // 'doc_regis_number' => SORT_DESC,
            // 'thai_year' => SORT_DESC,
        ]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => 'index'
        ]);
    }

    //ถึงหน่วยงาน
    public function actionDepartment()
    {
        $emp = UserHelper::GetEmployee();
        $department = $emp->department;

        $searchModel = new DocumentSearch([
            'thai_year' => (date('Y') + 543),
            'date_filter' => 'today'
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith([
            'documentDepartment' => function ($query) {
                $query->alias('d_department')
                    ->andOnCondition(['d_department.name' => 'department']);
            }
        ]);
        $dataProvider->query->joinWith([
            'docRead' => function ($query) {
                $query->alias('d_read')
                    ->andOnCondition(['d_read.name' => 'read']);
            }
        ]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
            ['like', 'doc_number', $searchModel->q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, '$.des'))"), $searchModel->q],
        ]);
        if ($searchModel->q_status == 'Y') {
            $dataProvider->query->andFilterWhere(['d_read.bookmark' => 'Y']);
        } else {
            $dataProvider->query->andFilterWhere(['status' => $searchModel->q_status]);
        }

        $dataProvider->query->andWhere(['d_department.to_id' => $emp->department]);

       if($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->date_filter == '' && $searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => 'department'
        ]);
    }

    //แสดงหน้า Mydashboard
    public function actionShowHome()
    {

        $emp = UserHelper::GetEmployee();
        $department = $emp->department;

        $sql = "SELECT d.id
                FROM documents_detail d
                LEFT JOIN documents_detail r ON r.from_id = d.id AND r.name = 'read'
                WHERE d.name = 'department' AND d.to_id = :department AND r.doc_read IS NULL

                UNION

                SELECT d.id
                FROM documents_detail d
                LEFT JOIN documents_detail r ON r.from_id = d.id AND r.name = 'read'
                WHERE d.name = 'tags' AND d.to_id = :emp_id AND r.doc_read IS NULL;";

        $ids = Yii::$app->db->createCommand($sql, [
            ':department' => $department,
            ':emp_id' => $emp->id
        ])->queryColumn();

          $searchModel = new DocumentsDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['IN','id', $ids]);

        


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('show_home', [
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('show_home', [
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionView($id)
    {
        // $this->layout = '@app/themes/v3/layouts/theme-v/document_layout';

        $emp = UserHelper::GetEmployee();
        $detail = DocumentsDetail::findOne($id);
        $callback = $this->request->get('callback');
        $model = $this->findModel($detail->document_id);
        
        $checkReading = DocumentsDetail::find()->where(['document_id' => $detail->document_id, 'name' => 'read', 'to_id' => $emp->id, 'from_id' => $id])->one();
        if (!$checkReading) {
            $reading = new DocumentsDetail;
            $reading->document_id = $detail->document_id;
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
                'title' => $this->renderAjax('@app/modules/dms/views/documents/view_title', ['model' => $model]),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/view', [
                    'model' => $model,
                    'detail' => $detail
                ])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'detail' => $detail,
                'callback' => $callback
            ]);
        }
    }


    //สร้าง bookmark บันทึกหนังสือ
    public function actionBookmark($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = UserHelper::GetEmployee();
        $document = DocumentsDetail::findOne($id);
        // return $document;
        $checkBookmark = DocumentsDetail::findOne(['name' => 'read', 'document_id' => $document->document_id, 'to_id' => $emp->id]);
        // $bookmark = DocumentsDetail::findOne($id);

        if ($checkBookmark) {
            $bookmark = $checkBookmark;
        } else {
            $bookmark = new DocumentsDetail;
            $bookmark->name = 'read';
            $bookmark->document_id = $document->document_id;
            $bookmark->to_id = $emp->id;
        }

        $bookmark->bookmark = ($bookmark->bookmark == 'Y') ? 'N' : 'Y';
        $bookmark->save(false);
        return [
            'action' => 'update',
            'status' => 'success',
            'data' => $bookmark
        ];
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

            //## ตรวจสอบสถานะส่งเสนอ ผอ.
            $director = SiteHelper::getInfo()['director']->id;
            $me = UserHelper::GetEmployee();
            if ($me->id == $director) {
                $docStatus =  $model->document;
                $docStatus->status = 'DS4';
                $docStatus->save(false);
            }

            try {
            //ตรวจว่ามีการ Tags ถึง ผอฬหรือไม่
            if (in_array($director, $model->tags_employee)) {
                $docStatus =  $model->document;
                $docStatus->status = 'DS3';
                $docStatus->save(false);
            }
                        } catch (\Throwable $th) {
            }



            if ($model->save()) {
                $model->UpdateDocumentsDetail();
                return [
                    'status' => 'success'
                ];
                // ส่งข้อมูลกลับไปยังหน้า view เพื่อให้เห็นว่ามีการ comment เข้ามา'
                // return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/_form_comment', [
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
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/_form_comment', [
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
