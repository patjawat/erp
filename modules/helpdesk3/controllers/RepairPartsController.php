<?php

namespace app\modules\helpdesk3\controllers;

use app\modules\helpdesk3\models\HelpdeskDetail;

class RepairPartsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate($helpdesk_id)
    {
        $model = new HelpdeskDetail([
            'helpdesk_id' => $helpdesk_id
        ]);
        return $this->render('create', ['model' => $model]);
    }
}
