<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use app\modules\dms\models\DocumentsDetail;

class CommitteeController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model= new DocumentsDetail([
            'name' => 'committee',
            'document_id' => Yii::$app->request->get('id'),
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'status' => "success",
            ];
        }

        if($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "Create Commitee",
                'content' => $this->renderAjax('_form', [
                    'model' => $model
                ])
            ];
        }else{
            return $this->render('_form', [
                'model' => $model
            ]);
        }
    }

    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model=  DocumentsDetail::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'status' => "success",
            ];
        }

        if($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "แก้ไข",
                'content' => $this->renderAjax('_form', [
                    'model' => $model
                ])
            ];
        }else{
            return $this->render('_form', [
                'model' => $model
            ]);
        }
    }
    


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DocumentsDetail();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->to_id)) {
                // preg_replace('/\D/', '', $model->to_id) == '' ? $model->addError('to_id', 'ลงวันที่ต้องระบุ') : null;
                $model->to_id == '' ? $model->addError('to_id', 'ลงวันที่ต้องระบุ') : null;
            }

         
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    public function actionDelete($id)
    {
        $model = DocumentsDetail::findOne($id);
        $model->delete();
        return $this->redirect(['/dms/documents/view','id' => $model->document_id]);
    }
    

}
