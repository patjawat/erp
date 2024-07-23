<?php

namespace app\modules\line\controllers;

use yii\helpers\Html;
use app\components\SiteHelper;
use app\models\SignupForm;
use app\modules\hr\models\Employees;
use app\models\PasswordResetRequestForm;
use app\modules\usermanager\models\User;
use Yii;
use yii\filters\Cors;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\line\models\LoginForm;
use app\models\ContactForm;

class AuthController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function actionRegister()
    {

        $model = new SignupForm([
            'cid' => '0000000000000',
            'email' => 'admin@local.com',
            'password' => '112233'
        ]);


        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->signup()) {
                return [
                    'status' => true,
                    'model' => $model,
                    // 'content' => $this->renderAjax('welcome', ['model' => $emp])
                ];
            }
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }

            return $this->asJson(['validation' => $result]);
        }
        return $this->render('register', [
            'model' => $model,
        ]);

        return $this->render('register');
    }
    public function actionLogin()
    {
        $model = new SignupForm([
            'cid' => '112233',
            'email' => 'admin@local.com',
            'password' => '112233',
        ]);
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionCheckProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($this->request->isPost) {

            // if (Yii::$app->user->isGuest) {
            $line_id =  $this->request->post('line_id');
            $user = User::findOne(['line_id' => $line_id]);

            if ($user) {
                $emp = Employees::findOne(['user_id' => $user->id]);
                $user_  = User::findByUsername($user->username);
                $isLogin = Yii::$app->user->login($user_, 0);
                if ($isLogin) {
                    return [
                        'status' => true,
                        'msg' => 'login Success',
                        'avatar' => $this->renderAjax('@app/modules/line/views/profile/avatar', ['model' => $emp])
                    ];
                } else {
                    return [
                        'status' => false,
                        'msg' => 'login False'
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'msg' => 'No User'
                ];
            }
        }
    }

    //เชื่อม line กับ user ที่ลงทะเบียนไว้แล้ว
    public function actionUserConnect()
    {
        $this->layout = 'blank';
        $model = new LoginForm();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // if ($model->load(Yii::$app->request->post())) {
                $user = User::findOne(['username' => $model->username]);
                if($user->line_id == ''){
                    $user->line_id = $model->line_id;
                    $user->save(false);
                }

                return $this->asJson([
                    'success' => true,
                    'model' => $model,
                ]);
            }
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }

            return $this->asJson(['validation' => $result]);
           
        }
        return $this->render('user_connect',['model' => $model]);
    }
    

    public function actionWelcome()
    {
        return $this->render('welcome');
    }
}
