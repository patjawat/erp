<?php

namespace app\modules\auth\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use app\models\User; 
use yii\helpers\Json;

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

        $user = \app\models\User::findOne(['hash_cid' => $hashCid]);

        if ($user) {
            // ทำการ Login (Session-based)
            if (Yii::$app->user->login($user)) {
                return $this->goHome(); // หรือ redirect('/')
            }
        }

        // กรณีไม่พบ User หรือ Login ไม่สำเร็จ
        Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลผู้ใช้งานในระบบ หรือคุณไม่มีสิทธิ์เข้าถึง');
        return $this->redirect(['auth/login']); // ส่งกลับหน้า Login หลัก
    }
}