<?php

namespace app\modules\filemanager\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;

class UploadsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }



    public function actionShow()
    {
        $id = Yii::$app->request->get('id');
        $model = Uploads::findOne($id);
        $filename = $model->real_filename;
        $filepathCheck = FileManagerHelper::getUploadPath() . $model->ref . '/thumbnail/' . $filename;
        if (!file_exists($filepathCheck)) {
            $filepath = FileManagerHelper::getUploadPath() . $model->ref . '/' . $filename;
        } else {
            $filepath = $filepathCheck;
        }
        $this->setHttpHeaders($model->type);
        \Yii::$app->response->data = file_get_contents($filepath);
        if ($model->name == 'logo') {
            return \Yii::$app->response;
        }
        if (!Yii::$app->user->isGuest) {
            return \Yii::$app->response;
        } else {
            return false;
        }
    }


    public function actionGetImage($id)
    {
        $model = Uploads::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('File not found.');
        }

        $filePath = Yii::getAlias('@app/modules/filemanager/fileupload/')
            . $model->ref . '/' . $model->real_filename;

        if (!file_exists($filePath)) {
            throw new \yii\web\NotFoundHttpException('File not found.');
        }

        return Yii::$app->response->sendFile($filePath, null, [
            'inline' => true,
            'cache' => true,
        ]);
    }


    public function actionShowPdf()
    {
        if (!Yii::$app->user->isGuest) {

            $id = Yii::$app->request->get('id');
            $model = Uploads::findOne($id);
            $filename = $model->real_filename;
            $filepath = FileManagerHelper::getUploadPath() . $model->ref . '/' . $filename;
            $this->setHttpHeaders($model->type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;
        } else {
            return false;
        }
    }

    protected function setHttpHeaders($type)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $mimeTypes = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $type = strtolower($type);

        if (isset($mimeTypes[$type])) {
            Yii::$app->response->headers->set('Content-Type', $mimeTypes[$type]);
        } else {
            // Default fallback
            Yii::$app->response->headers->set('Content-Type', 'application/octet-stream');
        }

    }


    /* |*********************************************************************************|
    |================================ Upload Ajax ====================================|
    |*********************************************************************************| */

    public function actionUploadAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return  FileManagerHelper::Uploads();
    }

    public function actionSingle()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return  FileManagerHelper::UploadsSingle();
    }

    public function actionUploadPdf()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return  FileManagerHelper::UploadPdf();
    }


    public function actionDeletefileAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('key');
        if (FileManagerHelper::Deletefile($id)) {

            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

}
