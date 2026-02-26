<?php

namespace app\modules\development\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * การตั้งค่าแบบฟอร์มไปราชการ (ลิงก์ไปจัดการ master data ที่ใช้ในแบบฟอร์ม)
 */
class SettingController extends Controller
{
    /**
     * เฉพาะ hr หรือ admin เข้าหน้าตั้งค่าได้
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['hr', 'admin'],
                    ],
                ],
            ],
        ];
    }

    /**
     * หน้ารวมลิงก์ตั้งค่าแบบฟอร์มไปราชการ
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
