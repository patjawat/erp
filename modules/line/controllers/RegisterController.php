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

class RegisterController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $model = new SignupForm([
            'cid' =>'112233',
            'email' =>'admin@local.com',
            'password' =>'112233',
        ]);
        

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->signup()) {
                $emp = Employees::findOne(['cid' => $model->cid]);
                return $this->asJson([
                    'success' => true,
                    'model' => $model,
                    'content' => $this->renderAjax('signup_success',['model' => $emp])
                ]);
            }
    
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
    
            return $this->asJson(['validation' => $result]);
        }
        return $this->render('index', [
            'model' => $model,
        ]);

        return $this->render('index');
    }
    public function actionWelcome()
    {
        return $this->render('welcome');
    }

}
