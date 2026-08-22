<?php

namespace app\modules\finance\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

class LoanController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['financeOperate']]]],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}
