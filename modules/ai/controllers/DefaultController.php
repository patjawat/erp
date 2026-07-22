<?php

declare(strict_types=1);

namespace app\modules\ai\controllers;

use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    public function actionIndex(): Response
    {
        return $this->redirect(['/ai/chat/index']);
    }
}
