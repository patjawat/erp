<?php

namespace app\controllers;
use Yii;
use app\models\SiteSetting;

class SettingController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model =  SiteSetting::findOne(1);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return $this->redirect('/setting');
            }
        } else {
            $model->loadDefaultValues();
        }

        if($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return[
                'title' => '<i class="fas fa-user-plus"></i> สร้างใหม่',
                'content' => $this->renderAjax('create', ['model' => $model]),
                'footer' =>''
            ];
        }else {

            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }

    public function actionGenkey(){
        
        $model =  SiteSetting::findOne(1);
        $model->data_json = [
            'logo' =>  isset($model->data_json['logo']) ? $model->data_json['logo'] : '',
            'dashbroad_url' =>  isset($model->data_json['dashbroad_url']) ? $model->data_json['dashbroad_url'] :'',
            'site_name' =>  isset($model->data_json['site_name']) ? $model->data_json['site_name'] :'',
            'manual_url' =>  isset($model->data_json['manual_url']) ? $model->data_json['manual_url'] :'',
            'token' => substr(Yii::$app->getSecurity()->generateRandomString(),10)
        ];
        if($model->save(false)){
            return $this->redirect('/setting');
        }

    }

}
