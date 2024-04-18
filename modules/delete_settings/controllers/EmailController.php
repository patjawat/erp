<?php

namespace app\modules\settings\controllers;

use Yii;
use app\components\SiteHelper;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class EmailController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model= SiteHelper::getCategoriseByname('site');

        if ($this->request->isPost) {
            $post =  $this->request->post('Categorise');
            $model->data_json =  ArrayHelper::merge($model->data_json, $post['data_json']);
            if($model->save()){
            return $this->redirect(['/settings/email']);
            }
        } else {
            $model->loadDefaultValues();
        }
        return $this->render('index',[
            'model' => $model
        ]);

    }

}
