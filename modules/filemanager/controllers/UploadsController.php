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
        if(!Yii::$app->user->isGuest){

            $id = Yii::$app->request->get('id');
            $model = Uploads::findOne($id);
            $filename = $model->real_filename;
            $filepathCheck = FileManagerHelper::getUploadPath().$model->ref.'/thumbnail/'. $filename;
            if (!file_exists($filepathCheck)) {
                $filepath = FileManagerHelper::getUploadPath().$model->ref.'/'. $filename;
            }else{
                $filepath = $filepathCheck;
            }
            $this->setHttpHeaders($model->type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;

        }else{
            return false;
        }

    }

    public function actionShowPdf()
    {
        if(!Yii::$app->user->isGuest){

            $id = Yii::$app->request->get('id');
            $model = Uploads::findOne($id);
            $filename = $model->real_filename;
            $filepath = FileManagerHelper::getUploadPath().$model->ref.'/'. $filename;
            $this->setHttpHeaders($model->type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;

        }else{
            return false;
        }

    }

    protected function setHttpHeaders($type)
    {
        
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        if($type == 'png'){
            \Yii::$app->response->headers->add('content-type','image/png');
        }
        
        if($type == 'pdf'){
            \Yii::$app->response->headers->add('content-type','application/pdf');

        }
    

        // Yii::$app->getResponse()->getHeaders()
        //     ->set('Pragma', 'public')
        //     ->set('Expires', '0')
        //     ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
        //     ->set('Content-Transfer-Encoding', 'binary')
        //     ->set('Content-type', 'image/*');
        //     // header('Content-type: application/pdf');
        //     // header('Content-type', 'video/mp4');
        //     header('Content-type', 'image/*');
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

    public function actionDeletefileAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('key');
        if(FileManagerHelper::Deletefile($id)){

            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
}
