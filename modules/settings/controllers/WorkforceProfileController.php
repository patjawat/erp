<?php

namespace app\modules\settings\controllers;

use app\modules\hr\models\WorkforceProfile;
use app\modules\plan\components\PlanHelper;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * โปรไฟล์โรงพยาบาล — ตัวขับเคลื่อนที่สูตรกรอบอัตรากำลังต้องใช้
 *
 * ตั้งค่าที่นี่ครั้งเดียวต่อปี แล้วกรอบทั้งระบบคำนวณได้เอง
 * ระดับโรงพยาบาลเป็นค่าในฐานข้อมูล ไม่ใช่ค่าคงที่ในโค้ด ระบบจึงใช้ได้กับ รพ. ทุกขนาด
 */
class WorkforceProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['get', 'post']],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ตั้งค่าโปรไฟล์โรงพยาบาล');
        }

        return true;
    }

    public function actionIndex()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $model = WorkforceProfile::forYear($year);

        if ($model->load($this->request->post())) {
            $model->thai_year = $year;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกโปรไฟล์ปี ' . $year . ' แล้ว');

                return $this->redirect(['index', 'thai_year' => $year]);
            }

            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ ตรวจค่าที่กรอกอีกครั้ง');
        }

        return $this->render('index', [
            'model' => $model,
            'year' => $year,
            'years' => $this->yearOptions($year),
            'levels' => WorkforceProfile::levelOptions(),
        ]);
    }

    /** ปีที่เลือกได้ — ปีที่มีข้อมูลแล้ว บวกปีปัจจุบันและปีถัดไป */
    private function yearOptions(int $current): array
    {
        $years = WorkforceProfile::find()->select('thai_year')->distinct()->column();
        $years = array_map('intval', $years);
        $years[] = $current;
        $years[] = $current + 1;

        $years = array_values(array_unique($years));
        rsort($years);

        return array_combine($years, $years);
    }
}
