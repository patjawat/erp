<?php

namespace app\modules\auth\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\LoginForm;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;
use app\modules\hr\models\EmployeeDetail;
use app\modules\usermanager\models\Profile;

class LoginController extends Controller
{
    //หน้า login
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/none';
        $model = new LoginForm();
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(\Yii::$app->request->post()) && $model->login()) {
                return $this->redirect(['/me']);
            }
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }

            return $this->asJson(['validation' => $result]);
        }
        $model->password = '';
        return $this->render('index', ['model' => $model]);
    }


    // หน้า login ไม่ผ่าน
    public function actionFail()
    {
        $this->layout = '@app/views/layouts/none';
        return $this->render('login_fail');
    }

    public function actionProvider()
    {
        $this->layout = '@app/views/layouts/none';
        return $this->render('provider');
    }
    // login ผ่าน ThaiD
    public function actionThaid()
    {
        return $this->redirect(Yii::$app->thaidAuth->getLoginUrl());
    }

    // callback กลับมาจาก ThaiD
    public function actionCallbackThaid($code = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $user = Yii::$app->thaidAuth->getUserFromCode($code);
        $cid = $user['sub'] ?? null;
        $birthdate = $user['birthdate'] ?? null;
        $fname = $user['given_name'] ?? null;
        $lname = $user['family_name'] ?? null;
        // ข้อมูลจาก ThaiD
        $thaidData = [
            'cid' => $cid,
            'birthday' => $birthdate,
            'fname' => $fname,
            'lname' => $lname,
        ];

        // ตรวจสอบข้อมูลพนักงาน
        $emp = $this->checkEmployee($thaidData);

        // ถ้าไม่พบข้อมูลพนักงาน
        if (!$emp) {
            $this->redirect(['/auth/login/fail']);
        }

        // ถ้าพบข้อมูลพนักงาน แต่ยังไม่มี user_id
        if ($emp && $emp->user_id == 0) {
            $user = $this->registerUser($emp);
            if ($user) {
                Yii::$app->user->login($user);
                return $this->redirect(['/me']);
            }
        }

        // ถ้าพบข้อมูลพนักงาน และมี user_id อยู่แล้ว
        if ($emp && $emp->user_id >= 1) {
            $user = User::findOne($emp->user_id);
            Yii::$app->user->login($user);
            return $this->redirect(['/me']);
        }
    }

    // ตรวจสอบข้อมูลพนักงาน
    private function checkEmployee($data)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = Employees::find()->where(
            [
                'cid' => $data['cid'],
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'birthday' => $data['birthday']
            ]
        )->one();
        if (!$emp) {
            return false;
        } else {
            return $emp;
        }
    }

    // ตรวจสอบข้อมูล user
    public function checkUser($id)
    {
        $user = User::findOne(['id' => $id]);
    }
    // สร้าง user ใหม่
    private function registerUser($data)
    {

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $password = Yii::$app->security->generateRandomString(12);
            $emp =  Employees::find()->where(['cid' => $data['cid']])->one();

            $email = $data['cid'] . '@local';
            $user = new User([
                'password' => $password,
                'confirm_password' => $password
            ]);

            $user->username = $emp->email;
            $user->email = $emp->email;

            $user->setPassword($password);
            $user->generateAuthKey();
            $user->status = 10;
            if ($user->save(false)) {
                $emp->user_id  =  $user->id;
                $emp->email = $email;
                $emp->save(false);

                $createPdpa = new EmployeeDetail();
                $createPdpa->emp_id =  $emp->id;
                $createPdpa->name = 'pdpa';
                $createPdpa->data_json = Yii::$app->session->get('accept_condition');
                $createPdpa->save(false);

                $user->assignment();
                $transaction->commit();
                return $user;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
