<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
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
        $model = new DocumentsDetail();
        $model->document_id = $this->request->get('document_id');
        $model->ref = $this->request->get('ref');
        $model->name = 'tags';

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                $targets = [];
                if (is_array($model->tags_employee) && !empty($model->tags_employee)) {
                    $targets = array_filter(array_map('trim', $model->tags_employee), 'strlen');
                } elseif (!empty($model->tag_id)) {
                    $targets = [$model->tag_id];
                } elseif (!empty($model->to_id)) {
                    $targets = [$model->to_id];
                }

                if (empty($targets)) {
                    Yii::$app->response->statusCode = 422;
                    return [
                        'status' => 'error',
                        'message' => 'กรุณาเลือกบุคคลอย่างน้อย 1 คน',
                    ];
                }

                $comment = '';
                if (is_array($model->data_json) && isset($model->data_json['comment'])) {
                    $comment = $model->data_json['comment'];
                }

                $created = 0;
                $skipped = 0;
                foreach ($targets as $tagId) {
                    $exists = DocumentsDetail::find()
                        ->where([
                            'document_id' => $model->document_id,
                            'name' => 'tags',
                            'to_id' => $tagId,
                        ])
                        ->one();
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                    $row = new DocumentsDetail();
                    $row->document_id = $model->document_id;
                    $row->ref = $model->ref;
                    $row->name = 'tags';
                    $row->to_id = (string) $tagId;
                    $row->to_type = 'employee';
                    $row->data_json = $comment !== '' ? ['comment' => $comment] : null;
                    if ($row->save()) {
                        $created++;
                        try {
                            $line_id = $row->employee->user->line_id;
                            if (!empty($line_id)) {
                                LineMsg::sendDocument($row, $line_id);
                            }
                        } catch (\Throwable $th) {
                        }
                    }
                }

                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#document-tag',
                    'created' => $created,
                    'skipped' => $skipped,
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionReqApprove()
    {
        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $info = SiteHelper::getInfo();
            $directorId = (int) ($info['director_name'] ?? 0);
            $documentId = (int) $this->request->post('document_id');

            if ($directorId <= 0 || $documentId <= 0) {
                Yii::$app->response->statusCode = 422;
                return [
                    'status' => 'error',
                    'container' => '#document-tag',
                    'message' => 'ข้อมูลไม่ครบถ้วน',
                ];
            }

            $exists = DocumentsDetail::findOne([
                'document_id' => $documentId,
                'name' => 'req_approve',
                'to_id' => (string) $directorId,
            ]);
            if ($exists) {
                return [
                    'status' => 'error',
                    'container' => '#document-tag',
                ];
            }

            $model = new DocumentsDetail();
            $model->document_id = $documentId;
            $model->ref = $this->request->post('ref');
            $model->name = 'req_approve';
            $model->to_id = (string) $directorId;
            $model->to_type = 'employee';
            $model->data_json = ['req_approve_date' => date('Y-m-d H:i:s')];

            $document = Documents::findOne($documentId);
            if ($document) {
                $document->status = 'DS3';
                $document->save(false);
            }

            if ($model->save()) {
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
        $model = $this->findOwnedDetailModel($id);
        if ($model instanceof DocumentsDetail) {
            $model->tag_id = $model->to_id;
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model instanceof DocumentsDetail) {
                $model->name = 'tags';
                $model->to_id = !empty($model->tag_id) ? (string) $model->tag_id : $model->to_id;
                $model->to_type = 'employee';
                if (is_string($model->data_json)) {
                    $decoded = json_decode($model->data_json, true);
                    $model->data_json = is_array($decoded) ? $decoded : [];
                }
                if (!is_array($model->data_json)) {
                    $model->data_json = [];
                }
            }

            if ($model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'title' => $this->request->get('title'),
                        'status' => 'success',
                        'container' => '#document-tag',
                    ];
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
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
        $model = $this->findOwnedDetailModel($id);
        $model->delete();

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'container' => '#document-tag',
            ];
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the DocumentsDetail model used by the tag UI.
     * Falls back to the legacy DocumentTags record only if needed.
     */
    protected function findEditableTagModel($id)
    {
        if (($model = DocumentsDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        return $this->findModel($id);
    }

    /**
     * โหลด tag ที่แก้ไข/ลบได้และตรวจว่าเป็นเจ้าของ
     */
    protected function findOwnedDetailModel($id)
    {
        $model = $this->findEditableTagModel($id);
        if ((int) $model->created_by !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('คุณสามารถแก้ไขหรือลบได้เฉพาะ tag ที่ตัวเองสร้างเท่านั้น');
        }
        return $model;
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

    /**
     * โหลด tag และตรวจว่าเป็นเจ้าของ ถ้าไม่ใช่ทิ้ง 403
     * @param int $id
     * @return DocumentTags
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    protected function findOwnedModel($id)
    {
        $model = $this->findModel($id);
        if (!$model->isOwnedByCurrentUser()) {
            throw new ForbiddenHttpException('คุณสามารถแก้ไขหรือลบได้เฉพาะ tag ที่ตัวเองสร้างเท่านั้น');
        }
        return $model;
    }
}
