<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use app\modules\dms\models\DocumentsDetail;

class TagDemoController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

        public function actionGetData()
    {
    $query = Yii::$app->request->get('query', '');
    $trigger = Yii::$app->request->get('trigger', '');
    
    $employees = Employees::find()
        ->where(['like', 'fname', $query])
        ->limit(10)
        ->all();
    
    $result = [];
    foreach ($employees as $emp) {
        $result[] = [
            'value' => $emp->username,
            'label' => $emp->name . ' (@' . $emp->username . ')',
            'description' => $emp->email
        ];
    }
    
    return Json::encode([
        'success' => true,
        'data' => $result
    ]);

        return $this->render('index');
    }
}
