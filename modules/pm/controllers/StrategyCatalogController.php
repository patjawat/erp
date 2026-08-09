<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\pm\models\{
    StrategyPlan, StrategyGoal, StrategyIndicator, StrategyIndicatorYear, StrategyIndicatorBaseline,
    StrategySuccessFactor, StrategyMeasure, StrategyProgram, StrategyTactic
};
use app\modules\pm\services\StrategyIndicatorYearCopier;

class StrategyCatalogController extends Controller
{
    /** ตัวชี้วัดรายปีจัดการผ่าน type=indicator เสมอ จึงไม่เปิดเป็นทะเบียนแยก */
    private const TYPES = [
        'indicator' => StrategyIndicator::class,
        'factor' => StrategySuccessFactor::class,
        'measure' => StrategyMeasure::class,
        'program' => StrategyProgram::class,
    ];

    public function behaviors(): array
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'template'], 'roles' => ['pmStrategyView']],
                ['allow' => true, 'roles' => ['pmStrategyManage']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'delete' => ['POST'], 'copy-year' => ['POST'], 'cancel-year' => ['POST'],
                'restore-year' => ['POST'], 'adopt' => ['POST'],
            ]],
        ];
    }

    public function actionIndex(string $type = 'indicator', ?int $planId = null, ?int $year = null)
    {
        if ($type === 'value' || $type === 'year') return $this->redirect(['index', 'type' => 'indicator', 'planId' => $planId, 'year' => $year]);
        $class = $this->classFor($type);
        $plan = $planId ? StrategyPlan::findOne($planId) : null;
        $q = trim((string) Yii::$app->request->get('q'));

        // ทะเบียนตัวชี้วัดทำงานรายปีเสมอ — แสดงชุดตัวชี้วัดของปีงบประมาณที่เลือก
        if ($type === 'indicator' && $plan) {
            $year = $this->resolveYear($plan, $year);
            $query = StrategyIndicatorYear::find()
                ->joinWith('indicator')
                ->where(['pm_strategy_indicator.plan_id' => $plan->id, 'pm_strategy_indicator_year.fiscal_year' => $year])
                ->orderBy(['pm_strategy_indicator_year.sort_order' => SORT_ASC, 'pm_strategy_indicator.code' => SORT_ASC]);
            if ($q !== '') $query->andWhere(['or', ['like', 'pm_strategy_indicator.code', $q], ['like', 'pm_strategy_indicator.name', $q], ['like', 'pm_strategy_indicator_year.name_override', $q]]);
            $copier = new StrategyIndicatorYearCopier($plan);
            return $this->render('index', [
                'type' => $type, 'planId' => $plan->id, 'plan' => $plan, 'year' => $year, 'q' => $q,
                'plans' => $this->planItems(), 'sourceYears' => array_diff($copier->yearsWithData(), [$year]),
                'adoptable' => $this->adoptableIndicators($plan, $year),
                'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 50]]),
            ]);
        }

        $query = $class::find()->orderBy($this->orderFor($type));
        if ($planId) $this->filterByPlan($query, $type, $planId);
        if ($q !== '') $query->andWhere(['or', ['like', 'code', $q], ['like', 'name', $q]]);
        return $this->render('index', [
            'type' => $type, 'planId' => $planId, 'plan' => $plan, 'year' => $year, 'q' => $q,
            'plans' => $this->planItems(), 'sourceYears' => [], 'adoptable' => [],
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 20]]),
        ]);
    }

    /** เพิ่มตัวชี้วัดใหม่เข้าปีงบประมาณ — สร้างทั้งตัวชี้วัดแม่และข้อมูลของปีนั้นพร้อมกัน */
    public function actionCreate(string $type, ?int $planId = null, ?int $parentId = null, ?int $year = null)
    {
        if ($type === 'indicator') {
            $plan = StrategyPlan::findOne($planId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน');
            $this->assertEditable($plan);
            $year = $this->resolveYear($plan, $year);
            return $this->indicatorForm(new StrategyIndicator(['plan_id' => $plan->id, 'is_active' => true]), new StrategyIndicatorYear(['fiscal_year' => $year, 'status' => StrategyIndicatorYear::STATUS_ACTIVE]), $plan, $year);
        }
        $class = $this->classFor($type);
        $model = new $class(['is_active' => true]);
        $this->assignParent($model, $type, $planId, $parentId);
        $plan = $this->resolvePlan($model, $type, $planId, $parentId);
        $this->assertEditable($plan);
        return $this->saveForm($model, $type, $plan);
    }

    public function actionUpdate(string $type, int $id)
    {
        if ($type === 'indicator') {
            $entry = StrategyIndicatorYear::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัดของปีนี้');
            $plan = $entry->indicator->plan;
            $this->assertEditable($plan);
            return $this->indicatorForm($entry->indicator, $entry, $plan, (int) $entry->fiscal_year);
        }
        $class = $this->classFor($type);
        $model = $class::findOne($id) ?: throw new NotFoundHttpException('ไม่พบรายการ');
        $plan = $this->planFromModel($model, $type);
        $this->assertEditable($plan);
        return $this->saveForm($model, $type, $plan);
    }

    public function actionDelete(string $type, int $id)
    {
        $class = $this->classFor($type);
        $model = $class::findOne($id) ?: throw new NotFoundHttpException('ไม่พบรายการ');
        $plan = $this->planFromModel($model, $type);
        $this->assertEditable($plan); $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการแล้ว');
        return $this->redirect(['index', 'type' => $type, 'planId' => $plan->id]);
    }

    /** คัดลอกชุดตัวชี้วัดทั้งชุดจากปีหนึ่งไปยังอีกปีหนึ่ง */
    public function actionCopyYear(int $planId, int $fromYear, int $toYear)
    {
        $plan = StrategyPlan::findOne($planId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน');
        $this->assertEditable($plan);
        $copier = new StrategyIndicatorYearCopier($plan);
        if ($copier->copy($fromYear, $toYear)) {
            Yii::$app->session->setFlash('success', $copier->summary());
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $copier->errors));
        }
        return $this->redirect(['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $toYear]);
    }

    /** นำตัวชี้วัดแม่ที่มีอยู่แล้วในแผนเข้ามาใช้ในปีที่เลือก */
    public function actionAdopt(int $planId, int $year, int $indicatorId)
    {
        $plan = StrategyPlan::findOne($planId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน');
        $this->assertEditable($plan);
        $indicator = StrategyIndicator::findOne(['id' => $indicatorId, 'plan_id' => $plan->id]) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
        $entry = new StrategyIndicatorYear(['indicator_id' => $indicator->id, 'fiscal_year' => $year, 'status' => StrategyIndicatorYear::STATUS_ACTIVE]);
        Yii::$app->session->setFlash(...($entry->save() ? ['success', "เพิ่ม {$indicator->code} เข้าปี {$year} แล้ว"] : ['error', 'เพิ่มตัวชี้วัดเข้าปีนี้ไม่สำเร็จ']));
        return $this->redirect(['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $year]);
    }

    /** ยกเลิกการใช้ตัวชี้วัดในปีนั้น โดยไม่ลบข้อมูลออกจากทะเบียน */
    public function actionCancelYear(int $id)
    {
        $entry = $this->editableEntry($id);
        $entry->cancel(trim((string) Yii::$app->request->post('reason')) ?: null);
        Yii::$app->session->setFlash('success', 'ยกเลิกการใช้ตัวชี้วัดในปีนี้แล้ว (ข้อมูลยังคงอยู่ในทะเบียน)');
        return $this->redirect(['index', 'type' => 'indicator', 'planId' => $entry->indicator->plan_id, 'year' => $entry->fiscal_year]);
    }

    public function actionRestoreYear(int $id)
    {
        $entry = $this->editableEntry($id);
        $entry->restore();
        Yii::$app->session->setFlash('success', 'กลับมาใช้ตัวชี้วัดนี้ในปีดังกล่าวแล้ว');
        return $this->redirect(['index', 'type' => 'indicator', 'planId' => $entry->indicator->plan_id, 'year' => $entry->fiscal_year]);
    }

    /** ฟอร์มตัวชี้วัด (KPI Template) — บันทึกตัวชี้วัดแม่ ข้อมูลรายปี และตารางลูกในรายการเดียว */
    private function indicatorForm(StrategyIndicator $indicator, StrategyIndicatorYear $entry, StrategyPlan $plan, int $year)
    {
        $scores = $entry->scoreRows();
        $periods = $entry->periodRows();
        $baselines = $this->baselineRows($entry, $plan, $year);
        $post = Yii::$app->request->post();

        if ($post && $indicator->load($post) && $entry->load($post)) {
            Model::loadMultiple($scores, $post);
            Model::loadMultiple($periods, $post);
            Model::loadMultiple($baselines, $post);
            $indicator->plan_id = $plan->id;
            $entry->fiscal_year = $year;
            $indicatorWasNew = $indicator->isNewRecord;
            $entryWasNew = $entry->isNewRecord;
            $tx = Yii::$app->db->beginTransaction();
            if ($indicator->save()) {
                $entry->indicator_id = $indicator->id;
                if ($entry->save()) {
                    $this->saveChildren($entry, $scores, fn($s) => trim((string) $s->description) === '' && $s->min_value === null && $s->max_value === null);
                    $this->saveChildren($entry, $periods, fn($p) => !$p->is_selected && $p->target_value === null && $p->actual_value === null);
                    $this->saveChildren($entry, $baselines, fn($b) => !$b->fiscal_year || $b->value === null);
                    $tx->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลแล้ว');
                    return $this->redirect(['template', 'id' => $entry->id]);
                }
            }
            // แถวที่เพิ่งบันทึกถูกย้อนกลับไปแล้ว จึงต้องคืนสถานะให้ฟอร์มบันทึกใหม่ได้
            $tx->rollBack();
            if ($indicatorWasNew) { $indicator->isNewRecord = true; $indicator->id = null; }
            if ($entryWasNew) { $entry->isNewRecord = true; $entry->id = null; }
        }
        return $this->render('indicator-form', [
            'indicator' => $indicator, 'entry' => $entry, 'plan' => $plan, 'year' => $year,
            'scores' => $scores, 'periods' => $periods, 'baselines' => $baselines,
            'goals' => $this->goalItems($plan),
        ]);
    }

    /** แสดง/พิมพ์รายละเอียดตัวชี้วัดตามแบบฟอร์ม KPI Template */
    public function actionTemplate(int $id)
    {
        $entry = StrategyIndicatorYear::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัดของปีนี้');
        return $this->render('template', [
            'entry' => $entry, 'indicator' => $entry->indicator, 'plan' => $entry->indicator->plan,
            'siblings' => StrategyIndicatorYear::find()->where(['indicator_id' => $entry->indicator_id])->orderBy(['fiscal_year' => SORT_ASC])->all(),
            'canEdit' => $entry->indicator->plan->isEditable() && Yii::$app->user->can('pmStrategyManage'),
        ]);
    }

    /** บันทึกผลงานจริงรายเดือนของตัวชี้วัดในปีงบประมาณ */
    public function actionMonthly(int $id)
    {
        $entry = StrategyIndicatorYear::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัดของปีนี้');
        if (!Yii::$app->user->can('pmStrategyManage')) throw new ForbiddenHttpException('ไม่มีสิทธิ์บันทึกผลงาน');
        $months = $entry->monthRows();
        $post = Yii::$app->request->post();
        if ($post && Model::loadMultiple($months, $post)) {
            $tx = Yii::$app->db->beginTransaction();
            $entry->load($post);
            $entry->save(false);
            $this->saveChildren($entry, $months, fn($m) => $m->value === null && $m->numerator === null && $m->denominator === null && trim((string) $m->note) === '');
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกผลงานรายเดือนแล้ว');
            return $this->redirect(['monthly', 'id' => $entry->id]);
        }
        return $this->render('monthly', ['entry' => $entry, 'months' => $months, 'plan' => $entry->indicator->plan]);
    }

    /** บันทึกแถวที่มีข้อมูล และลบแถวเดิมที่ถูกล้างค่าจนว่าง */
    private function saveChildren(StrategyIndicatorYear $entry, array $rows, callable $isEmpty): void
    {
        foreach ($rows as $row) {
            $row->indicator_year_id = $entry->id;
            if ($isEmpty($row)) {
                if (!$row->isNewRecord) $row->delete();
                continue;
            }
            $row->save();
        }
    }

    /** แถวข้อมูลพื้นฐาน — ที่มีอยู่เดิม บวกช่องว่างให้กรอกเพิ่มจนครบ 3 ปีย้อนหลังเป็นอย่างน้อย */
    private function baselineRows(StrategyIndicatorYear $entry, StrategyPlan $plan, int $year): array
    {
        $rows = $entry->isNewRecord ? [] : $entry->baselines;
        $used = array_map(fn($row) => (int) $row->fiscal_year, $rows);
        foreach ([$year - 3, $year - 2, $year - 1] as $past) {
            if (!in_array($past, $used, true)) $rows[] = new StrategyIndicatorBaseline(['fiscal_year' => $past]);
        }
        usort($rows, fn($a, $b) => (int) $a->fiscal_year <=> (int) $b->fiscal_year);
        return $rows;
    }

    private function saveForm($model, string $type, StrategyPlan $plan)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลแล้ว');
            return $this->redirect(['index', 'type' => $type, 'planId' => $plan->id]);
        }
        return $this->render('form', [
            'model' => $model, 'type' => $type, 'plan' => $plan,
            'goals' => $this->goalItems($plan),
            'tactics' => $this->tacticItems($plan),
            'measures' => $this->measureItems($plan),
        ]);
    }

    private function classFor(string $type): string
    {
        if (!isset(self::TYPES[$type])) throw new NotFoundHttpException('ไม่รู้จักชนิดทะเบียน');
        return self::TYPES[$type];
    }

    private function editableEntry(int $id): StrategyIndicatorYear
    {
        $entry = StrategyIndicatorYear::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัดของปีนี้');
        $this->assertEditable($entry->indicator->plan);
        return $entry;
    }

    /** ปีที่เลือก ถ้าไม่ระบุใช้ปีงบประมาณปัจจุบันเมื่ออยู่ในช่วงแผน มิฉะนั้นใช้ปีเริ่มต้น */
    private function resolveYear(StrategyPlan $plan, ?int $year): int
    {
        if ($year && $plan->coversYear($year)) return $year;
        $current = (int) date('Y') + 543 + ((int) date('n') >= 10 ? 1 : 0);
        return $plan->coversYear($current) ? $current : (int) $plan->start_year;
    }

    /** ตัวชี้วัดในแผนที่ยังไม่ถูกนำเข้าปีที่เลือก */
    private function adoptableIndicators(StrategyPlan $plan, int $year): array
    {
        $used = StrategyIndicatorYear::find()->select('indicator_id')->where(['fiscal_year' => $year]);
        $items = [];
        foreach (StrategyIndicator::find()->where(['plan_id' => $plan->id, 'is_active' => true])->andWhere(['not in', 'id', $used])->orderBy(['code' => SORT_ASC])->all() as $item) {
            $items[$item->id] = "$item->code — $item->name";
        }
        return $items;
    }

    /** มาตรการสร้างจากกลยุทธ์ ส่วนปัจจัยความสำเร็จ/RCA ยังผูกกับเป้าประสงค์โดยตรง */
    private function assignParent($model, string $type, ?int $planId, ?int $parentId): void
    {
        if ($type === 'program') $model->plan_id = $planId;
        if ($type === 'factor') $model->goal_id = $parentId;
        if ($type === 'measure') {
            $tactic = StrategyTactic::findOne($parentId) ?: throw new NotFoundHttpException('ไม่พบกลยุทธ์');
            $model->tactic_id = $tactic->id;
            $model->goal_id = $tactic->goal_id;
        }
    }

    private function resolvePlan($model, string $type, ?int $planId, ?int $parentId): StrategyPlan
    {
        if ($type === 'program') return StrategyPlan::findOne($planId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน');
        $goal = $type === 'measure' ? StrategyTactic::findOne($parentId)?->goal : StrategyGoal::findOne($parentId);
        return $goal?->issue?->mission?->plan ?: throw new NotFoundHttpException('ไม่พบเป้าประสงค์');
    }

    private function planFromModel($model, string $type): StrategyPlan
    {
        if ($type === 'indicator' || $type === 'program') return $model->plan;
        return $model->goal->issue->mission->plan;
    }

    private function filterByPlan($query, string $type, int $planId): void
    {
        if (in_array($type, ['indicator', 'program'], true)) { $query->andWhere(['plan_id' => $planId]); return; }
        $query->joinWith('goal.issue.mission')->andWhere(['pm_strategy_mission.plan_id' => $planId]);
    }

    private function planItems(): array { return StrategyPlan::find()->orderBy(['start_year' => SORT_DESC, 'version' => SORT_DESC])->all(); }
    private function orderFor(string $type): array { return ['sort_order' => SORT_ASC, 'id' => SORT_ASC]; }
    private function assertEditable(StrategyPlan $plan): void { if (!$plan->isEditable()) throw new ForbiddenHttpException('ข้อมูลของแผนที่ประกาศใช้แล้วถูกล็อก'); }
    private function goalItems(StrategyPlan $plan): array { $items=[]; foreach($plan->missions as $m) foreach($m->issues as $i) foreach($i->goals as $g) $items[$g->id]="$g->code — $g->name"; return $items; }
    private function tacticItems(StrategyPlan $plan): array { $items=[]; foreach($plan->missions as $m) foreach($m->issues as $i) foreach($i->goals as $g) foreach($g->tactics as $t) $items[$t->id]=($t->indicator?->code ?: $g->code).' · '.$t->label(); return $items; }
    private function measureItems(StrategyPlan $plan): array { $items=[]; foreach(StrategyMeasure::find()->joinWith('goal.issue.mission')->where(['pm_strategy_mission.plan_id'=>$plan->id])->all() as $m) $items[$m->id]=trim("$m->code — $m->name", ' —'); return $items; }
}
