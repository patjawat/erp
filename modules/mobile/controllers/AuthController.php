<?php

namespace app\modules\mobile\controllers;

use app\models\LoginForm;
use app\modules\usermanager\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * ล็อกอิน/ล็อกเอาท์ โมดูล mobile
 */
class AuthController extends Controller
{
    public $enableCsrfValidation = false;

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
            return $this->goBack(Url::to(['/mobile/default/index']));
        }

        $this->layout = 'login';
        $model = new LoginForm();
        $model->password = '';

        $telegram_id = Yii::$app->request->post('telegram_id');

        // -------------------------
        // AJAX LOGIN
        // -------------------------
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model;
                // bind telegram_id ครั้งแรก
                // if ($telegram_id && Yii::$app->user->identity->telegram_id == null) {

                //     $user = Yii::$app->user->identity;
                //     $user->telegram_id = $telegram_id;
                //     $user->save(false);

                // }

                return [
                    'success' => true,
                    'redirect' => Url::to(['/mobile/default/index']),
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

        // -------------------------
        // POST LOGIN ปกติ
        // -------------------------
        if ($model->load(Yii::$app->request->post()) && $model->login()) {

            $data = Yii::$app->request->post('LoginForm');
            /** @var User $user */
            $user = Yii::$app->user->identity;
            if ($user->telegram_id == null) {
                $user->telegram_id = $data['telegram_id'];
                $user->save(false);
            }


            return $this->goBack(Url::to(['/mobile/default/index']));
        }

        return $this->render('login', [
            'model' => $model
        ]);
    }


    public function actionTelegramLogin()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $telegram_id = Yii::$app->request->post('telegram_id');
        $username    = Yii::$app->request->post('username');
        $name        = Yii::$app->request->post('name');

        if (!$telegram_id) {
            return [
                'success' => false,
                'message' => 'Missing telegram_id'
            ];
        }

        // 🔍 1. หา user จาก telegram_id ก่อน
        $user = User::findOne(['telegram_id' => $telegram_id]);

        // 🟡 2. ถ้าไม่เจอ → สร้างใหม่
        if (!$user) {
            $user = new User();
            $user->username = $username ?: ('tg_' . $telegram_id);
            $user->name = $name;
            $user->telegram_id = $telegram_id;

            if (!$user->save()) {
                return [
                    'success' => false,
                    'message' => 'Cannot create user',
                    'error' => $user->errors
                ];
            }
        }

        // 🔐 3. login หลังจาก bind เสร็จ
        Yii::$app->user->login($user, 3600 * 24 * 30);
        $redirect = Yii::$app->user->getReturnUrl(Url::to(['/mobile/default/index']));

        return [
            'success' => true,
            'user_id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'message' => 'Login success',
            'redirect' => $redirect,
        ];
    }

    public function actionTelegramAutoLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $telegram_id = Yii::$app->request->post('telegram_id');

        if (!$telegram_id) {
            return [
                'success' => false
            ];
        }

        // หา user จาก telegram_id
        $user = User::findOne(['telegram_id' => $telegram_id]);

        if (!$user) {
            return [
                'success' => false
            ];
        }

        Yii::$app->user->login($user, 3600 * 24 * 30);
        $redirect = Yii::$app->user->getReturnUrl(Url::to(['/mobile/default/index']));

        return [
            'success' => true,
            'user_id' => $user->id,
            'redirect' => $redirect,
        ];
    }


// public function actionCheckTelegram()
// {
//     Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

//     $telegram_id = Yii::$app->request->post('telegram_id');

//     if (!$telegram_id) {
//         return [
//             'exists' => false
//         ];
//     }

//     $user = User::findOne(['telegram_id' => $telegram_id]);

//     if ($user) {

//         // 🔐 login ได้เลย
//         Yii::$app->user->login($user, 3600 * 24 * 30);

//         return [
//             'exists' => true,
//             'user_id' => $user->id
//         ];
//     }

//     return [
//         'exists' => false
//     ];
// }

    /**
     * ออกจากระบบ
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['/mobile/auth/login']);
    }
}
