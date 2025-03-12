<?php

namespace app\modules\settings\controllers;

use Yii;
use yii\web\Response;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\LogHelper;
use app\components\SiteHelper;
use app\components\UserHelper;

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
                $me = UserHelper::GetEmployee();
                $data = [
                    "fullname" =>$me->fullname,
                    'title' => 'แก้ไขข้อมูลองกรณ์',
                    'data' => $model
                ];
                LogHelper::log('update_setting',$data);
                return $this->redirect('/settings/company');
            }
        }
        return $this->render('index',['model' => $model]);
    }

}
