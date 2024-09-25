<?php

namespace app\modules\settings\controllers;

use app\components\SiteHelper;
use Yii;
use app\models\Categorise;
use yii\web\Response;
use yii\helpers\ArrayHelper;


class LineOfficialController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $model = Categorise::findOne(['name' => 'site']);
        if ($this->request->isPost) {
            $old = $model->data_json;
            if ($model->load($this->request->post())) {
                $model->data_json = ArrayHelper::merge($old,$model->data_json);
                $model->save();
                return $this->redirect('/settings/line-official');
            }
        }
        return $this->render('index',['model' => $model]);
    }

}
