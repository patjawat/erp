<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\dms\models\DocumentTagsSearch;
use app\modules\dms\models\DocumentsDetailSearch;

class DocumentsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $emp = UserHelper::GetEmployee();
        $department = $emp->department;
        $searchModel = new DocumentsDetailSearch();
        $dataProviderDepartment = $searchModel->search($this->request->queryParams);
        $dataProviderDepartment->query->joinWith('document');
        $dataProviderDepartment->query->andFilterWhere(['to_id' => $emp->department]);
        $dataProviderDepartment->query->andFilterWhere(['name' => 'department']);
        if($searchModel->show_reading == 1){
            $dataProviderDepartment->query->andWhere(['IS NOT', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
            
        }else{
            $dataProviderDepartment->query->andWhere(['IS', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL

        }
        
        $dataProviderEmployee = $searchModel->search($this->request->queryParams);
        $dataProviderEmployee->query->joinWith('document');
       
        $dataProviderEmployee->query->andFilterWhere(['to_id' => $emp->id]);
        $dataProviderEmployee->query->andFilterWhere(['name' => 'employee']);
        if($searchModel->show_reading == 1){
            $dataProviderEmployee->query->andWhere(['IS NOT', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
        }else{
            $dataProviderEmployee->query->andWhere(['IS', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
        }
        $dataProviderEmployee->setSort(['defaultOrder' => [
            // 'doc_regis_number' => SORT_DESC,
            // 'thai_year' => SORT_DESC,
        ]]);

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('list_show', [
                    'list' => true,
                    'searchModel' => $searchModel,
                    'dataProviderDepartment' => $dataProviderDepartment,
                    'dataProviderEmployee' => $dataProviderEmployee
                ])
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProviderDepartment' => $dataProviderDepartment,
                'dataProviderEmployee' => $dataProviderEmployee
            ]);
        }
    }

    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = UserHelper::GetEmployee();
        $docDetail = DocumentsDetail::findOne($id);
        $docDetail->doc_read = date('Y-m-d H:i:s');
        $docDetail->save(false);

        $view_count[] = [
            'date_time' => date('Y-m-d H:i:s'),
            'emp_id' => $emp->id,
            'fullname' => $emp->fullname,
            'department' => $emp->departmentName(),
        ];
        $model = $this->findModel($docDetail->document_id);
        if ($model->view_json === null) {
            $model->view_json = [];
        }
        $model->view_json = ArrayHelper::merge($view_count, $model->view_json);
        $model->save(false);

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->renderAjax('@app/modules/dms/views/documents/view_title', ['model' => $model]),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/view', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/view', [
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

    public function actionShow($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest) {
            $id = Yii::$app->request->get('id');
            $fileUpload = Uploads::findOne(['ref' => $model->ref]);
            $filename = $fileUpload->real_filename;
            $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            if (!file_exists($filepath)) {
                throw new \yii\web\NotFoundHttpException('The requested file does not exist.');
            }

            $this->setHttpHeaders($fileUpload->type);
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
