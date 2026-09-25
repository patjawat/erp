<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanInvestment;
use app\modules\plan\components\PlanHelper;

/**
 * แผนการลงทุนด้วยเงินบำรุง — ตรงแบบฟอร์มเขต (cfomoph.com/monthlycash) เมนู 1.3 แผนลงทุน 1 ปี / 1.4 แผนลงทุน 3 ปี
 *
 * ชุดแผนผูกกับปีเริ่ม (plan_year) เดียวกับหน้าแผนประจำปี: แผน 1 ปี = ปี plan_year, แผน 3 ปี = plan_year..plan_year+2
 */
class InvestmentController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'delete' => ['POST'], 'copy-prev' => ['POST']],
            ],
        ];
    }

    /** @param string $tab '1' = แผนลงทุน 1 ปี, '3' = แผนลงทุน 3 ปี */
    public function actionIndex($year = null, $tab = '1')
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $tab = $tab === '3' ? '3' : '1';

        $query = PlanInvestment::find()->where(['plan_year' => $year]);
        if ($tab === '1') {
            $query->andWhere(['>', 'qty_y1', 0]);
        }
        $items = $query->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();

        // แยกกลุ่มตามประเภทงบ (ครุภัณฑ์ก่อน สิ่งก่อสร้างทีหลัง เหมือนแม่แบบเขต)
        $groups = array_fill_keys(array_keys(PlanInvestment::types()), []);
        foreach ($items as $it) {
            $groups[$it->budget_type][] = $it;
        }

        // สรุปวงเงินตามแหล่งเงิน: 1 ปี = เงินปีแรก, 3 ปี = รวม 3 ปี
        $amountOf = fn (PlanInvestment $it) => $tab === '1' ? $it->amount(1) : $it->totalAmount();
        $bySource = array_fill_keys(array_keys(PlanInvestment::sources()), 0.0);
        $total = 0.0;
        foreach ($items as $it) {
            $a = $amountOf($it);
            $bySource[$it->source] += $a;
            $total += $a;
        }

        $prevCount = (int) PlanInvestment::find()
            ->where(['plan_year' => $year - 1])
            ->andWhere(['or', ['>', 'qty_y2', 0], ['>', 'qty_y3', 0]])
            ->count();

        return $this->render('index', [
            'year' => $year,
            'tab' => $tab,
            'groups' => $groups,
            'count' => count($items),
            'total' => $total,
            'bySource' => $bySource,
            'hasAny' => PlanInvestment::find()->where(['plan_year' => $year])->exists(),
            'prevCount' => $prevCount,
        ]);
    }

    /** เพิ่ม/แก้ไขรายการ — แท็บ 1 ปี แก้เฉพาะจำนวนปีแรก (ไม่ทับจำนวนปีที่ 2-3 ที่กรอกไว้ในแท็บ 3 ปี) */
    public function actionSave()
    {
        $post = Yii::$app->request->post('PlanInvestment', []);
        $tab = Yii::$app->request->post('tab') === '3' ? '3' : '1';
        $id = (int) Yii::$app->request->post('id');
        $model = $id ? $this->findModel($id) : new PlanInvestment();

        $num = fn ($v) => (float) str_replace([',', ' '], '', (string) $v);
        $model->plan_year = (int) ($post['plan_year'] ?? $model->plan_year ?? PlanHelper::currentPlanYear());
        $model->budget_type = (string) ($post['budget_type'] ?? '');
        $model->name = trim((string) ($post['name'] ?? ''));
        $model->unit = trim((string) ($post['unit'] ?? '')) ?: null;
        $model->unit_price = $num($post['unit_price'] ?? 0);
        $model->qty_y1 = $num($post['qty_y1'] ?? 0);
        if ($tab === '3') {
            $model->qty_y2 = $num($post['qty_y2'] ?? 0);
            $model->qty_y3 = $num($post['qty_y3'] ?? 0);
        }
        $model->source = (string) ($post['source'] ?? PlanInvestment::SOURCE_MAINTENANCE);
        $model->policy = (int) ($post['policy'] ?? 8);
        $model->note = trim((string) ($post['note'] ?? '')) ?: null;
        if ($model->isNewRecord) {
            $model->sort_order = (int) PlanInvestment::find()->where(['plan_year' => $model->plan_year])->max('sort_order') + 1;
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกรายการลงทุน "' . $model->name . '" แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getErrorSummary(true)));
        }
        return $this->redirect(['index', 'year' => $model->plan_year, 'tab' => $tab]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $tab = Yii::$app->request->post('tab') === '3' ? '3' : '1';
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการ "' . $model->name . '" แล้ว');
        return $this->redirect(['index', 'year' => $model->plan_year, 'tab' => $tab]);
    }

    /**
     * ตั้งต้นแผนปีนี้จากแผน 3 ปีของปีก่อน (แผนต่อเนื่อง): ปีที่ 2 เดิม → ปีที่ 1, ปีที่ 3 เดิม → ปีที่ 2
     * ทำได้เฉพาะเมื่อชุดแผนปีนี้ยังว่าง กันรายการซ้ำ
     */
    public function actionCopyPrev($year)
    {
        $year = (int) $year;
        if (PlanInvestment::find()->where(['plan_year' => $year])->exists()) {
            Yii::$app->session->setFlash('warning', "แผนลงทุนปี $year มีรายการอยู่แล้ว — คัดลอกได้เฉพาะเมื่อยังว่าง");
            return $this->redirect(['index', 'year' => $year, 'tab' => '3']);
        }
        $n = 0;
        $prev = PlanInvestment::find()->where(['plan_year' => $year - 1])
            ->andWhere(['or', ['>', 'qty_y2', 0], ['>', 'qty_y3', 0]])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($prev as $p) {
            $copy = new PlanInvestment($p->getAttributes(['budget_type', 'name', 'unit', 'unit_price', 'source', 'policy', 'note']));
            $copy->plan_year = $year;
            $copy->qty_y1 = $p->qty_y2;
            $copy->qty_y2 = $p->qty_y3;
            $copy->qty_y3 = 0;
            $copy->sort_order = ++$n;
            $copy->save(false);
        }
        Yii::$app->session->setFlash('success', "คัดลอกจากแผนลงทุนปี " . ($year - 1) . " แล้ว $n รายการ — ตรวจราคาและจำนวนปีที่ 3 อีกครั้ง");
        return $this->redirect(['index', 'year' => $year, 'tab' => '3']);
    }

    private function findModel($id): PlanInvestment
    {
        $m = PlanInvestment::findOne((int) $id);
        if (!$m) {
            throw new NotFoundHttpException('ไม่พบรายการลงทุน');
        }
        return $m;
    }
}
