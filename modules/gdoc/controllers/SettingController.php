<?php

namespace app\modules\gdoc\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;


class SettingController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTemplateLeave()
    {
        return $this->render('template_leave');
    }

    public function actionUploadCredentials()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('credentialsFile');

        if ($uploadedFile && $uploadedFile->extension === 'json') {
            $json = file_get_contents($uploadedFile->tempName);

            try {
                $data = json_decode($json, true);

                if (!isset($data['project_id'], $data['private_key'], $data['client_email'])) {
                    Yii::$app->session->setFlash('error', 'ไฟล์ JSON ไม่ครบถ้วน');
                }

                $model = new Categorise();
                $model->name = "google";
                $model->code = "account_service";
                $model->data_json = $data;

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'บันทึกไฟล์ Credentials สำเร็จ');
                } else {
                    Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . json_encode($model->errors));
                }
            } catch (\Throwable $e) {
                Yii::$app->session->setFlash('error', 'ไม่สามารถอ่านไฟล์ JSON ได้');
            }
        } else {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ JSON ที่ถูกต้อง');
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionDeleteCredentials($id)
    {
        $model = Categorise::findOne($id);
        if($model){
            $model->delete();
              return $this->redirect(Yii::$app->request->referrer);
            
        }
    }

    public function actionUpdateDrive()
    {
         \Yii::$app->response->format = Response::FORMAT_JSON;
         $post = $this->request->post('gdrive_id');
         $check = Categorise::findOne(['name' => 'google','code' => 'gdrive']);
         if(!$check){
            $model = new Categorise;
              $model->name = 'google';
                $model->code = 'gdrive';
         }else{
            $model = $check;
         }
       
         $model->data_json = [
            'drive_id' => $post
         ];
        return  $model->save(false);
         return $this->redirect(Yii::$app->request->referrer);

    }
}
