<?php

namespace app\modules\settings\controllers;

use Yii;
use yii\web\Response;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;


class LineMessagingController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model = Categorise::findOne(['name' => 'site']);
        if ($this->request->isPost) {
            $old = $model->data_json;
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($old,$model->data_json);
                $model->save();
                return $this->redirect('/settings/line-messaging');
            }
        }
        return $this->render('index',['model' => $model]);
    }

}
