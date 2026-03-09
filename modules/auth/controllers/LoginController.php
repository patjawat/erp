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
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/me']);
        }
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
            // ส่ง validation errors กลับเป็น JSON เพื่อให้ฟอร์มแสดงข้อความผิดพลาด (เช่น username/password ไม่ถูกต้อง)
            $validation = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $validation[Html::getInputId($model, $attribute)] = $errors;
            }
            return [
                'success' => false,
                'validation' => $validation,
            ];
        }

        return $this->render('index', ['model' => $model]);
    }


    public function actionFail()
    {
        $this->layout = '@app/views/layouts/none';
        return $this->render('login_fail');
    }
}
