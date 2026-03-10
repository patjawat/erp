<?php

namespace app\modules\mobile\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;

/**
 * ล็อกอิน/ล็อกเอาท์ โมดูล mobile
 */
class AuthController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['login', 'logout'],
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * หน้าเข้าสู่ระบบ
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/mobile/default/index']);
        }

        $this->layout = 'login';
        $model = new LoginForm();
        $model->password = '';

        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                return [
                    'success' => true,
                    'redirect' => Url::to(Yii::$app->user->getReturnUrl(['/mobile/default/index'])),
                ];
            }
            $validation = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $validation[Html::getInputId($model, $attribute)] = $errors;
            }
            return [
                'success' => false,
                'validation' => $validation,
            ];
        }

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack(['/mobile/default/index']);
        }

        return $this->render('login', ['model' => $model]);
    }

    /**
     * ออกจากระบบ
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['/mobile/auth/login']);
    }
}
