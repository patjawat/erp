<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\dms\models\DocumentsDetail;

class TagDemoController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

        public function actionGetData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
    $query = Yii::$app->request->get('query', '');
    $trigger = Yii::$app->request->get('trigger', '');
    
    $keywords = Categorise::find()
        ->where(['name' => 'document_keyword'])
        ->andWhere(['like', 'title', $query])
        ->limit(10)
        ->all();
    
    $result = [];
    foreach ($keywords as $item) {
        $result[] = [
            'value' => $item->title,
            // 'label' => $item->title . ' (@' . $item->title . ')',
            // 'description' => $item->title
        ];
    }
    
    return [
        'success' => true,
        'data' => $result
    ];

        return $this->render('index');
    }
}
