<?php

namespace app\modules\dms\controllers;
use Yii;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;

class DashboardController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new DocumentSearch([
            'thai_year' => (Date('Y')+543),
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        

       return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
