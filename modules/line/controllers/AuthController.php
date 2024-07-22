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
use app\models\LoginForm;
use app\models\ContactForm;

class AuthController extends \yii\web\Controller
{

    public function beforeAction($action)
    {


        // if (!Yii::$app->user->isGuest) {
        //     return $this->redirect(['/line/profile']);
        // }

        return parent::beforeAction($action);
    }


    public function actionRegister()
    {

        $model = new SignupForm([
            'cid' => '112233',
            'email' => 'admin@local.com',
            'password' => '112233',
        ]);


        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->signup()) {
                // if ($model->load(Yii::$app->request->post())) {
                $emp = Employees::findOne(['cid' => $model->cid]);
                return $this->asJson([
                    'status' => true,
                    'model' => $model,
                    // 'content' => $this->renderAjax('welcome', ['model' => $emp])
                ]);
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
                            'msg' => 'login Success'
                        ];
                    } else {
                        return [
                            'status' => false,
                             'msg' => 'login False'
                        ];
                    }
                }else{
                    return [
                        'status' => false,
                         'msg' => 'No User'
                    ];
                }
            // } else {
            //     return [
            //         'status' => false,
            //          'msg' => 'xx'
            //     ];
            // }
        }
    }

    public function actionWelcome()
    {
        return $this->render('welcome');
    }
}
