<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Categorise;
use app\modules\plan\components\PlanHelper;

/**
 * ตั้งค่ารอบการทำแผน (planning period) — ผู้ดูแลแผน (role plan / admin) เปิด/ปิด/เปลี่ยน phase
 */
class PlanPeriodController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save'        => ['POST'],
                    'set-phase'   => ['POST'],
                    'set-current' => ['POST'],
                    'delete'      => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'periods' => PlanHelper::periods(),
            'current' => PlanHelper::currentPlanYear(),
        ]);
    }

    /** เปิดรอบปีใหม่ หรือ อัปเดต phase/ปัจจุบัน ของปีที่มีอยู่ */
    public function actionSave()
    {
        $year    = (int) $this->request->post('thai_year');
        $phase   = (string) $this->request->post('phase', PlanHelper::PHASE_OPEN);
        $current = (int) $this->request->post('current', 0);

        if ($year >= 2500 && $year <= 2600) {
            $p = PlanHelper::period($year) ?: new Categorise([
                'name' => 'plan_period', 'code' => (string) $year, 'active' => 1,
            ]);
            $p->title = 'รอบทำแผนปี ' . $year;
            $dj = $this->json($p);
            $dj['phase'] = $phase;
            if ($current) {
                $this->clearCurrent();
                $dj['current'] = 1;
            }
            $p->data_json = $dj;
            $p->save(false);
            Yii::$app->session->setFlash('success', 'บันทึกรอบทำแผนปี ' . $year . ' แล้ว');
        }
        return $this->redirect(['index']);
    }

    /** เปลี่ยน phase อย่างเดียว */
    public function actionSetPhase()
    {
        $year  = (int) $this->request->post('thai_year');
        $phase = (string) $this->request->post('phase');
        $p = PlanHelper::period($year);
        if ($p) {
            $dj = $this->json($p);
            $dj['phase'] = $phase;
            $p->data_json = $dj;
            $p->save(false);
            Yii::$app->session->setFlash('success', 'เปลี่ยนสถานะรอบปี ' . $year . ' เป็น "' . PlanHelper::phaseLabel($phase) . '"');
        }
        return $this->redirect(['index']);
    }

    /** ตั้งปีนี้เป็นปีที่เปิดทำแผนปัจจุบัน */
    public function actionSetCurrent()
    {
        $year = (int) $this->request->post('thai_year');
        $p = PlanHelper::period($year);
        if ($p) {
            $this->clearCurrent();
            $dj = $this->json($p);
            $dj['current'] = 1;
            $p->data_json = $dj;
            $p->save(false);
            Yii::$app->session->setFlash('success', 'ตั้งปี ' . $year . ' เป็นปีที่เปิดทำแผนปัจจุบัน');
        }
        return $this->redirect(['index']);
    }

    public function actionDelete()
    {
        $year = (int) $this->request->post('thai_year');
        $p = PlanHelper::period($year);
        if ($p) {
            $p->delete();
            Yii::$app->session->setFlash('success', 'ลบรอบปี ' . $year . ' แล้ว');
        }
        return $this->redirect(['index']);
    }

    /** ยกเลิกธง current ของทุกปี */
    private function clearCurrent()
    {
        foreach (PlanHelper::periods() as $p) {
            $dj = $this->json($p);
            if (!empty($dj['current'])) {
                unset($dj['current']);
                $p->data_json = $dj;
                $p->save(false);
            }
        }
    }

    private function json($model)
    {
        $dj = $model->data_json;
        if (!is_array($dj)) {
            $dj = json_decode((string) $dj, true) ?: [];
        }
        return $dj;
    }
}
