<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Response;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;


class ServiceController extends \yii\web\Controller
{
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
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
    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        $model->request_repair_date = AppHelper::convertToThai($model->request_repair_date);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                try {
                    $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date);
                } catch (\Throwable $th) {
                }

                if ($model->save()) {
                   
                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

public function UpdateAssetStatus($model)
{
    if ($model->asset_number !== '' && $model->status == 'success') {
        $asset = Asset::findOne(['code' => $model->asset_number]);
        if ($asset) {
            $asset->asset_status = 1;
            return $asset->save(false);
        }
    }
    return false;
}



    public function actionUpdateStatus($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {

                if ($model->save()) {
                    $this->UpdateAssetStatus($model);
                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_update_status', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_update_status', [
                'model' => $model,
            ]);
        }
    }

    public function actionReceive($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        //บันทึกเปลี่ยนสถานะและออกเลขใขรับซ่อม
        $model = $this->findModel($id);
        $model->status = 'receive';
        //ออกระหัสรับงานซ่อม
        $model->receive_date = date('Y-m-d H:i:s');
        $model->save();

        //เขียนลงบน timeline
        $serviceRecord = new HelpdeskDetail;
        $serviceRecord->helpdesk_id = $model->id;
        $serviceRecord->name = 'service_record';
        $serviceRecord->status = 'รับเรื่อง';
        $serviceRecord->title = 'รับเรื่องเรียบร้อยแล้วรอให้ช่างดำเนินการตรวจเช็ค';
        $serviceRecord->save();
          return ['status' => 'success'];
    }


    public function actionTechnician()
    {
        return $this->render('technician/index');
    }


    public function actionCancel($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->status = 'Cancel';
        $model->save();
        return ['status' => 'success'];
        
    }
        public function actionDelete($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 'success'];
        
    }

    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
