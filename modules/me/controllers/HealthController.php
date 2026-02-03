<?php

namespace app\modules\me\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\EmployeeDetail;

class HealthController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }


public function actionView($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $model = $this->findModel($id);

    return [
        'title' => '<i class="fa-solid fa-heart-pulse"></i> ผลตรวจสุขภาพ',
        'content' => $this->renderAjax('view', [
            'model' => $model,
        ]),
        'footer' => Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"])
    ];
}
    protected function findModel($id)
    {
        if (($model = EmployeeDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
