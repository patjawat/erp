<?php

namespace app\modules\usermanager\controllers;

use Yii;
use app\modules\usermanager\models\User;
use app\modules\usermanager\models\LoginForm;
use app\modules\usermanager\components\AuthHandler;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AuthController extends \yii\web\Controller
{

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    public function onAuthSuccess($client)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        (new AuthHandler($client))->handle();
    }

    public function actionIndex()
    {
        // $this->layout = '@app/modules/usermanager/views/layouts/auth';
        return $this->render('@app/modules/usermanager/views/auth/index');
    }
    
    public function actionSignup()
    {
        // $this->layout = '@app/modules/usermanager/views/layouts/auth';
        $model = new User();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->password);
            $model->generateAuthKey();
            
            if($model->save()){
                $model->assignment();
            }
            // return $this->redirect(['success', 'id' => $model->id]);
            return $this->render('@app/modules/usermanager/views/auth/signup_success', [
                'model' => $this->findModel($model->id),
            ]);
        } else {
            return $this->render('@app/modules/usermanager/views/auth/_form', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionLogin()
    {
        // $this->layout = '@app/modules/usermanager/views/layouts/auth';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new \app\models\LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        // if ($model->load(Yii::$app->request->post())){
        //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        //     // return $model->login();
        //     return $this->goBack();

        // }

        $model->password = '';
        return $this->render('@app/modules/usermanager/views/auth/login', [
            'model' => $model,
        ]);
    }


    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
