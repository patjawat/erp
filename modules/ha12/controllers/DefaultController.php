<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Activity;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * HA12-PCT DefaultController
 *
 * เฟส 0: หน้าภาพรวม (landing) — แสดงทะเบียน 12 กิจกรรมและชนิดฟอร์ม พร้อม page-nav
 * การบันทึกทบทวน (เฟส 1) และรอบสรุป/ประเมิน PCT (เฟส 3) จะทยอยเติมภายหลัง
 *
 * สิทธิ์: เฟส 0 เปิดให้ผู้ล็อกอินทุกคน (roles => ['@'])
 * เฟสถัดไปจะคุมด้วยขอบเขตหน่วยงาน (owner = tree.id) แบบเดียวกับโมดูล task/km
 */
class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ]);
    }

    /** ปีงบประมาณที่กำลังดู (พ.ศ.) — ?fy= override, ค่าเริ่มต้น = ปีงบปัจจุบัน */
    private function fiscalYear(): int
    {
        $fy = (int) Yii::$app->request->get('fy');
        return $fy ?: (int) AppHelper::YearBudget();
    }

    public function actionIndex()
    {
        $fiscalYear = $this->fiscalYear();
        $activities = Ha12Activity::activeList();

        return $this->render('index', [
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 2),
            'activities' => $activities,
        ]);
    }
}
