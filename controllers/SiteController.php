<?php

namespace app\controllers;

use Yii;
use Google\Client;
use yii\filters\Cors;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use Google\Service\Docs;
use app\models\LoginForm;
use Google\Service\Drive;
use app\models\SignupForm;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use yii\filters\AccessControl;
use app\modules\hr\models\Employees;
use app\models\PasswordResetRequestForm;
use app\modules\usermanager\models\User;
use app\modules\hr\models\EmployeeDetail;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
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
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Allow-Headers' => ['content-type'],
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
        ];
    }

    // public function actions()
    // {
    //     return [
    //         'error' => [
    //             'class' => 'yii\web\ErrorAction',
    //         ],
    //         'captcha' => [
    //             'class' => 'yii\captcha\CaptchaAction',
    //             'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
    //         ],
    //     ];
    // }

    public function actionError()
    {
        // $this->layout = 'error_404';
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'content' => $this->renderAjax('error'),
            ];
        } else {
            return $this->render('error');
        }
    }

    public function actionIndex()
    {
        return $this->redirect(['/me']);
    }

    public function actionIndex3()
    {
        return $this->render('index3');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = 'login';
        if (!\Yii::$app->user->isGuest) {
            // return $this->goHome();
             return $this->redirect(['/me']);
        }

        $model = new LoginForm();
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(\Yii::$app->request->post()) && $model->login()) {
                // return $this->goBack();
                return $this->redirect(['/me']);
                // return $this->asJson([
                //     'success' => true,
                //     'model' => $model,

                // ]);
            }
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }

            return $this->asJson(['validation' => $result]);
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        \Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSignUp()
    {
        $this->layout = 'login';
        $model = new SignupForm();

        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(\Yii::$app->request->post()) && $model->validate() && $model->signup()) {
                $emp = Employees::findOne(['cid' => $model->cid]);

                return $this->asJson([
                    'success' => true,
                    'model' => $model,
                    'content' => $this->renderAjax('signup_success', ['model' => $emp]),
                ]);
            }

            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }

            return $this->asJson(['validation' => $result]);
        }

        if(!Yii::$app->session->get('accept_condition')){
            return $this->redirect(['site/conditions-register']);
        }else{
            return $this->render('signup', [
                'model' => $model,
            ]);
        }
    }

    public function actionConditionsRegister()
    {
        $this->layout = 'blank';
        // \Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->render('conditions_register');
        // return [
        //     'title' => 'ข้อกำหนดและเงื่อนไข',
        //     'content' => $this->renderAjax('conditions_register'),
        // ];
    }

    public function actionAcceptCondition()
    {
        Yii::$app->session->set('accept_condition', [
            'date' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);

        if (!Yii::$app->user->isGuest) {
            $me = UserHelper::GetEmployee();
            $createPdpa = new EmployeeDetail();
            $createPdpa->emp_id =  $me->id;
            $createPdpa->name = 'pdpa';
            $createPdpa->data_json = Yii::$app->session->get('accept_condition');
            $createPdpa->save(false);
            return $this->redirect(['/me']);
        }else{
            return $this->redirect(['/site/sign-up']);
        }
        
        // Yii::$app->session->remove('accept_condition');
        
    }

    // public function actionSuccess(){
    //     $this->layout = 'blank';
    //     $model = Employees::findOne(['cid' => '3650200206817']);
    //     Yii::$app->session->setFlash('success', 'ยินดีด้วยคุณลงทะเบียนสำเร็จ!.');

    //     return $this->render('signup_success',['model' => $model]);
    // }

    public function actionForgotPassword()
    {
        $this->layout = 'blank';
        $model = new PasswordResetRequestForm();
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->sendEmail()) {
                return $this->render('confirm_email', ['model' => $model]);
            } else {
                $model->addError('email', 'ไม่พบที่อยู่อีเมลนี้');
            }
        }

        return $this->render('forgot_password', ['model' => $model]);
    }

    public function actionResetPassword()
    {
        $this->layout = 'blank';
        $token = \Yii::$app->request->get('token');
        $model = new PasswordResetForm(['token' => $token]);
        $user = User::findOne(['password_reset_token' => $token]);
        if ($model->load(\Yii::$app->request->post()) && $model->setPassword()) {
            \Yii::$app->session->setFlash('success', 'เปลี่ยนรหัสผ่านสำเร็จ !');

            return $this->redirect(['/site/login']);
        }
        if ($user) {
            return $this->render('reset_password', ['model' => $model]);
        } else {
            return $this->render('reset_false');
        }
    }



}


