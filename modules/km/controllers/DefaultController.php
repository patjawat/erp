<?php

namespace app\modules\km\controllers;

use app\components\AppHelper;
use app\modules\km\models\KmActivity;
use app\modules\km\models\KmCategory;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * KM DefaultController
 *
 * เฟส 0: หน้าภาพรวม (landing) ของคลังกิจกรรม KM — โครงเปล่าพร้อม page-nav
 * ทะเบียนกิจกรรม/หมวดหมู่/แม่แบบ + คลังภาพ + ลิงก์หลักฐาน จะทยอยเติมในเฟส 1 เป็นต้นไป
 *
 * สิทธิ์: เฟส 0 เปิดให้ผู้ล็อกอินทุกคน (roles => ['@'])
 * เฟสถัดไปจะคุมด้วยขอบเขตหน่วยงาน (owner = tree.id) แบบเดียวกับโมดูล task
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

        $activityCount = KmActivity::find()
            ->where(['fiscal_year' => $fiscalYear])
            ->count();
        $publishedCount = KmActivity::find()
            ->where(['fiscal_year' => $fiscalYear, 'status' => KmActivity::STATUS_PUBLISHED])
            ->count();
        $categoryCount = KmCategory::find()->where(['is_active' => 1])->count();

        return $this->render('index', [
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 2),
            'stats' => [
                'activities' => (int) $activityCount,
                'published' => (int) $publishedCount,
                'categories' => (int) $categoryCount,
            ],
        ]);
    }
}
