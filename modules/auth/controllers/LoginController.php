<?php

namespace app\modules\auth\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\LoginForm;


class LoginController extends Controller
{
    //หน้า login
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/none';
        $model = new LoginForm();
        $model->password = '';
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(\Yii::$app->request->post()) && $model->login()) {
                return [
                    'success' => true,
                    'redirect' => \yii\helpers\Url::to(['/me']),
                ];
            }
        }

        return $this->render('index', ['model' => $model]);
    }


    public function actionFail()
    {
        $this->layout = '@app/views/layouts/none';
        return $this->render('login_fail');
    }
}
