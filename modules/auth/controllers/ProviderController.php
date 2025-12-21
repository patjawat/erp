<?php

namespace app\modules\auth\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\Controller;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;
use app\modules\hr\models\EmployeeDetail;

class ProviderController extends Controller
{
    public function actionHandle()
    {
        $request = Yii::$app->request;
        
        // รับค่า Parameters
        $hashCid = $request->get('hash_cid');
        $ts = $request->get('ts');
        $sig = $request->get('sig');
        $hospcode = $request->get('hospcode');

        // 1. ตรวจสอบ Parameters เบื้องต้น
        if (!$hashCid || !$ts || !$sig) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['error' => 'Missing parameters']);
        }

        // 2. ตรวจสอบ Timestamp (ไม่เกิน 5 นาที)
        if (abs(time() - (int)$ts) > 300) {
            Yii::$app->response->statusCode = 403;
            return $this->asJson(['error' => 'Signature expired']);
        }

        // 3. ตรวจสอบ Signature
        $payloadData = [
            'hash_cid' => $hashCid,
            'ts' => (int)$ts,
        ];
        
        // Yii2 เทียบเท่า JSON_UNESCAPED_UNICODE
        $payload = Json::encode($payloadData);
        
        // ดึง secret จาก config/params.php หรือ .env
        $sharedSecret = Yii::$app->params['sso_shared_secret']; 
        $expectedSig = hash_hmac('sha256', $payload, $sharedSecret);

        if (!hash_equals($expectedSig, $sig)) {
            Yii::$app->response->statusCode = 403;
            return $this->asJson(['error' => 'Invalid signature']);
        }

        $user = User::findOne(['hash_cid' => $hashCid]);


        // ตรวจสอบข้อมูลพนักงาน
        $emp = $this->checkEmployee($thaidData);

        // ถ้าไม่พบข้อมูลพนักงาน
        if (!$emp) {
            $this->redirect(['/auth/login/fail']);
        }

        // ถ้าพบข้อมูลพนักงาน แต่ยังไม่มี user_id
        if ($emp && $emp->hash_cid == 0) {
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



        // if ($user) {
        //     // ทำการ Login (Session-based)
        //     if (Yii::$app->user->login($user)) {
        //         return $this->goHome(); // หรือ redirect('/')
        //     }
        // }

        // กรณีไม่พบ User หรือ Login ไม่สำเร็จ
        Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลผู้ใช้งานในระบบ หรือคุณไม่มีสิทธิ์เข้าถึง');
        return $this->redirect(['auth/login']); // ส่งกลับหน้า Login หลัก
    }

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
    
    
}