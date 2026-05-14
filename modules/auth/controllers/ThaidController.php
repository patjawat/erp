<?php

namespace app\modules\auth\controllers;

use Yii;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;
use app\modules\hr\models\EmployeeDetail;

class ThaidController extends \yii\web\Controller
{
    /** Session key: route array e.g. ['/mobile/default/index'] after ThaiD login (จากโมดูล mobile) */
    private const SESSION_THAID_SUCCESS_REDIRECT = 'thaid_success_redirect';

    public function actionIndex()
    {
        if (Yii::$app->request->get('mobile') === '1') {
            Yii::$app->session->set(self::SESSION_THAID_SUCCESS_REDIRECT, ['/mobile/default/index']);
        } else {
            Yii::$app->session->remove(self::SESSION_THAID_SUCCESS_REDIRECT);
        }

        return $this->redirect(Yii::$app->thaidAuth->getLoginUrl());
    }

    // เริ่ม flow "ดึงข้อมูลมาเติมฟอร์มเพิ่มบุคลากร" (เปิดผ่าน popup)
    public function actionFillForm()
    {
        return $this->redirect(Yii::$app->thaidAuth->getFillFormUrl());
    }

    // callback กลับมาจาก ThaiD
    public function actionCallback($code = null, $state = null)
    {
        // flow: เติมข้อมูลฟอร์มเพิ่มบุคลากร — แยกออกจาก flow login เดิม
        $fillState = Yii::$app->session->get('thaid_fill_form_state');
        if ($fillState && $state !== null && hash_equals($fillState, (string) $state)) {
            Yii::$app->session->remove('thaid_fill_form_state');
            $user = Yii::$app->thaidAuth->getUserFromCode($code);

            return $this->renderPartial('fill-callback', [
                'data' => [
                    'prefix' => $user['title'] ?? '',
                    'fname' => $user['given_name'] ?? '',
                    'lname' => $user['family_name'] ?? '',
                    'cid' => $user['pid'] ?? ($user['sub'] ?? ''),
                    'birthday' => $user['birthdate'] ?? '',
                    'address' => $user['address'] ?? '',
                ],
            ]);
        }

        $successRedirect = Yii::$app->session->get(self::SESSION_THAID_SUCCESS_REDIRECT);

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
            Yii::$app->session->remove(self::SESSION_THAID_SUCCESS_REDIRECT);
            if (is_array($successRedirect)) {
                return $this->redirect(['/mobile/auth/login']);
            }
            return $this->redirect(['/auth/login/fail']);
        }

        $afterLogin = is_array($successRedirect) ? $successRedirect : ['/me'];
        Yii::$app->session->remove(self::SESSION_THAID_SUCCESS_REDIRECT);

        // ถ้าพบข้อมูลพนักงาน แต่ยังไม่มี user_id
        if ($emp && $emp->user_id == 0) {
            $user = $this->registerUser($emp);
            if ($user) {
                Yii::$app->user->login($user);
                return $this->redirect($afterLogin);
            }
        }

        // ถ้าพบข้อมูลพนักงาน และมี user_id อยู่แล้ว
        if ($emp && $emp->user_id >= 1) {
            $user = User::findOne($emp->user_id);
            Yii::$app->user->login($user);
            return $this->redirect($afterLogin);
        }
    }

    // ตรวจสอบข้อมูลพนักงาน
    private function checkEmployee($data)
    {
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
            $user->hash_cid = Yii::$app->security->generatePasswordHash($data['cid']);
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

