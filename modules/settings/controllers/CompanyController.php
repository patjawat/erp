<?php

namespace app\modules\settings\controllers;

use app\components\SiteHelper;
use Yii;
use app\models\Categorise;
use yii\web\Response;
use yii\helpers\ArrayHelper;

class CompanyController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model = Categorise::findOne(['name' => 'site']);
        $old = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($old,$model->data_json);
                $model->save();
                return $this->redirect('/settings/company');
            }
        }
        return $this->render('index',['model' => $model]);
    }

}
