<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\StockOrder;

class SubStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
        public function actionDashboard()
    {
        return $this->render('dashboard');
    }

        public function actionRequisition()
    {
            $model = new StockOrder();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('_form_requisition', [
            'model' => $model,
        ]);
    }


}
