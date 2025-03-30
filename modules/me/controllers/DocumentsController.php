<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use app\models\Uploads;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\dms\models\DocumentTagsSearch;
use app\modules\dms\models\DocumentsDetailSearch;
use app\modules\filemanager\components\FileManagerHelper;

class DocumentsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $emp = UserHelper::GetEmployee();
        $department = $emp->department;
        
        $searchModel = new DocumentsDetailSearch([
            'thai_year' => (date('Y')+543)
        ]);
        $dataProviderDepartment = $searchModel->search($this->request->queryParams);
        $dataProviderDepartment->query->joinWith('document');
        $dataProviderDepartment->query->andWhere(['to_id' => $emp->department]);
        $dataProviderDepartment->query->andFilterWhere(['name' => 'department']);
        if ($this->request->isAJax) {
            $dataProviderDepartment->query->andWhere(['IS', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
        }

        
        $dataProviderTags = $searchModel->search($this->request->queryParams);
        $dataProviderTags->query->joinWith('document');
       
        $dataProviderTags->query->andFilterWhere(['to_id' => $emp->id]);
        $dataProviderTags->query->andFilterWhere(['name' => 'tags']);
        if ($this->request->isAJax) {
            $dataProviderTags->query->andWhere(['IS', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
        }
        // if($searchModel->show_reading == 1){
        //     $dataProviderTags->query->andWhere(['IS NOT', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
        // }else{
        //     $dataProviderTags->query->andWhere(['IS', 'doc_read', null]); // เพิ่มเงื่อนไขว่า doc_read ต้องเป็น NULL
        // }
        $dataProviderTags->setSort(['defaultOrder' => [
            // 'doc_regis_number' => SORT_DESC,
            // 'thai_year' => SORT_DESC,
        ]]);


        $dataProviderBookmark = $searchModel->search($this->request->queryParams);
        $dataProviderBookmark->query->joinWith('document');
       
        $dataProviderBookmark->query->andFilterWhere(['to_id' => $emp->id]);
        $dataProviderBookmark->query->andFilterWhere(['bookmark' => 'Y']);
        $dataProviderBookmark->query->andFilterWhere(['name' => 'bookmark']);

        

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('list_show', [
                    'list' => true,
                    'searchModel' => $searchModel,
                    'dataProviderDepartment' => $dataProviderDepartment,
                    'dataProviderTags' => $dataProviderTags,
                    'dataProviderBookmark' => $dataProviderBookmark
                    ])
                ];
            } else {
                return $this->render('index', [
                    'searchModel' => $searchModel,
                'dataProviderDepartment' => $dataProviderDepartment,
                'dataProviderTags' => $dataProviderTags,
                'dataProviderBookmark' => $dataProviderBookmark
            ]);
        }
    }

    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $this->layout = '@app/themes/v3/layouts/theme-v/document_layout';
        $emp = UserHelper::GetEmployee();
        $detail = DocumentsDetail::findOne($id);
        $callback = $this->request->get('callback');
        $model = $this->findModel($detail->document_id);
        
        if($detail->doc_read == null){
             $detail->doc_read = date('Y-m-d H:i:s');
            $detail->save(false);
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
        $bookmark = DocumentsDetail::findOne(['name' => 'bookmark','document_id' => $document->document_id,'to_id' => $emp->id ]);
        if($bookmark){
            $bookmark->bookmark = ($bookmark->bookmark == 'Y') ? 'N' : 'Y';
            $bookmark->save();
            return [
                'action' => 'update',
                'status' => 'success',
                'data' => $bookmark
            ];
        }else{
            $newBookmark = new DocumentsDetail;
            $newBookmark->name = 'bookmark';
            $newBookmark->to_id = $emp->id;
            $newBookmark->bookmark = 'Y';
            $newBookmark->document_id = $document->document_id;
            $newBookmark->save(false);
            return [
                'action' => 'create',
                'status' => 'success',
                'data' => $newBookmark
            ];
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
                'title' =>$this->request->get('title'),
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

    // public function actionUpdateComment($id)
    // {

    //     $emp = UserHelper::GetEmployee();
    //     // $model = DocumentsDetail::findOne($id);
    //     $model = $this->findModel($id);
        
    //     $tags = DocumentsDetail::find()->where(['name' => 'comment','document_id' => $model->document_id])->all();

    //     if ($this->request->isPost && $model->load($this->request->post())) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         if($model->save()){
    //             $model->UpdateDocumentsDetail();
    //             return $this->redirect(['view', 'id' => $model->id]);
    //             // return [
    //             //     'status' => 'success',
    //             //     'data' => $model,
    //             // ];
    //         }
    //     }
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
                return[
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
