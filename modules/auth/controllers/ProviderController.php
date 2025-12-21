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

    public function beforeAction($action)
{
    if ($action->id === 'callback') {
        $this->enableCsrfValidation = false;
    }
    return parent::beforeAction($action);
}




    public function actionIndex()
    {
        return 'Hello';
    }
    public function actionCallback()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $sharedSecret = env('SSO_SHARED_SECRET');
        // 1. รับค่า Parameters
        $hashCid = $this->request->post('hash_cid');
        $ts = $this->request->post('ts');
        $sig = $this->request->post('sig');

        // 2. ตรวจสอบ Parameters เบื้องต้น
        if (!$hashCid || !$ts || !$sig) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['error' => 'Missing parameters']);
        }

        // 3. ตรวจสอบ Timestamp (ไม่เกิน 5 นาที)
        if (abs(time() - (int)$ts) > 300) {
            Yii::$app->response->statusCode = 403;
            return $this->asJson(['error' => 'Signature expired']);
        }

        // 4. ตรวจสอบ Signature
        $payloadData = [
            'hash_cid' => $hashCid,
            'ts' => (int)$ts,
        ];
        $payload = Json::encode($payloadData);
        $expectedSig = hash_hmac('sha256', $payload, $sharedSecret);

        if (!hash_equals($expectedSig, $sig)) {
            Yii::$app->response->statusCode = 403;
            return $this->asJson(['error' => 'Invalid signature']);
        }

        // 5. ตรวจสอบข้อมูลพนักงาน (คืนค่าเป็น Array จาก DAO)
        $empData = $this->checkEmployee($hashCid);

        if (!$empData) {
            // กรณีไม่พบข้อมูลพนักงานในตาราง employees
            return $this->redirect(['/auth/login/fail']);
        }

        // 6. ตรวจสอบว่ามี User หรือยัง
        $user = null;
        if (!empty($empData['user_id'])) {
            // ถ้ามี user_id ผูกอยู่แล้ว ให้ดึงข้อมูล User มา
            $user = User::findOne($empData['user_id']);
        }

        // 7. ถ้ายังไม่มี User ให้ทำการ Register
        if (!$user) {
            $user = $this->registerUser($empData, $hashCid);
        }

        // 8. ทำการ Login
        if ($user && Yii::$app->user->login($user)) {
            return $this->redirect(['/me']);
        }

        // กรณีเกิดข้อผิดพลาดในการสร้าง User หรือ Login
        Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลผู้ใช้งานในระบบ หรือคุณไม่มีสิทธิ์เข้าถึง');
        return $this->redirect(['auth/login']);
    }

    // ตรวจสอบข้อมูลพนักงานจากฐานข้อมูลด้วย SHA2
    private function checkEmployee($hashCid)
    {
        return Yii::$app->db->createCommand('SELECT * FROM employees WHERE SHA2(cid, 256) = :hash')
            ->bindValue(':hash', $hashCid)
            ->queryOne(); // คืนค่าเป็น Array หรือ false
    }

    // สร้าง user ใหม่
    private function registerUser($empData, $hashCid)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // ดึง Model Employees เพื่อเตรียมอัปเดต
            $emp = Employees::findOne($empData['id']);
            if (!$emp) {
                return null;
            }

            $password = Yii::$app->security->generateRandomString(12);
            
            $user = new User();
            // ใช้ Email จากพนักงาน ถ้าไม่มีให้ใช้ cid@local
            $user->username = !empty($emp->email) ? $emp->email : $emp->cid;
            $user->email = !empty($emp->email) ? $emp->email : $emp->cid . '@local';
            $user->setPassword($password);
            $user->hash_cid = $hashCid; // เก็บค่า Hash SHA256 ที่ส่งมาจากต้นทาง
            $user->generateAuthKey();
            $user->status = 10; // Active status

            if ($user->save(false)) {
                // 1. อัปเดต user_id กลับไปที่ตาราง employees
                $emp->user_id = $user->id;
                $emp->save(false);

                // 2. บันทึกข้อมูล PDPA (ถ้ามี)
                $acceptCondition = Yii::$app->session->get('accept_condition');
                if ($acceptCondition) {
                    $createPdpa = new EmployeeDetail();
                    $createPdpa->emp_id = $emp->id;
                    $createPdpa->name = 'pdpa';
                    $createPdpa->data_json = Json::encode($acceptCondition);
                    $createPdpa->save(false);
                }

                // 3. กำหนดสิทธิ์ (ถ้ามี method นี้ใน Model User)
                if (method_exists($user, 'assignment')) {
                    $user->assignment();
                }

                $transaction->commit();
                return $user;
            }

            $transaction->rollBack();
            return null;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Register Error: " . $e->getMessage());
            return null;
        }
    }
}