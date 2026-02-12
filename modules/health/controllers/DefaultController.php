<?php

namespace app\modules\health\controllers;

use yii\web\Controller;
use app\modules\hr\models\EmployeeDetailSearch;

/**
 * Default controller for the `health` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
     public function actionIndex()
    {
        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

         public function actionList()
    {
        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
