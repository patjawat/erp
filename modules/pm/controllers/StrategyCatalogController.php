<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\pm\models\{
    StrategyPlan, StrategyGoal, StrategyIndicator, StrategyIndicatorValue,
    StrategySuccessFactor, StrategyMeasure, StrategyProgram
};

class StrategyCatalogController extends Controller
{
    private const TYPES = [
        'indicator' => StrategyIndicator::class,
        'value' => StrategyIndicatorValue::class,
        'factor' => StrategySuccessFactor::class,
        'measure' => StrategyMeasure::class,
        'program' => StrategyProgram::class,
    ];

    public function behaviors(): array
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index'], 'roles' => ['pmStrategyView']],
                ['allow' => true, 'roles' => ['pmStrategyManage']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST']]],
        ];
    }

    public function actionIndex(string $type = 'indicator', ?int $planId = null)
    {
        $class = $this->classFor($type);
        $query = $class::find()->orderBy($this->orderFor($type));
        if ($planId) $this->filterByPlan($query, $type, $planId);
        if (($q = trim((string) Yii::$app->request->get('q'))) !== '' && $type !== 'value') {
            $query->andWhere(['or', ['like', 'code', $q], ['like', 'name', $q]]);
        }
        return $this->render('index', [
            'type' => $type, 'planId' => $planId, 'q' => $q ?? '',
            'plans' => StrategyPlan::find()->orderBy(['start_year' => SORT_DESC, 'version' => SORT_DESC])->all(),
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 20]]),
        ]);
    }

    public function actionCreate(string $type, ?int $planId = null, ?int $parentId = null)
    {
        $class = $this->classFor($type);
        $model = new $class(['is_active' => true]);
        $this->assignParent($model, $type, $planId, $parentId);
        $plan = $this->resolvePlan($model, $type, $planId, $parentId);
        $this->assertEditable($plan);
        return $this->saveForm($model, $type, $plan);
    }

    public function actionUpdate(string $type, int $id)
    {
        $class = $this->classFor($type);
        $model = $class::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบรายการ');
        $plan = $this->planFromModel($model, $type);
        $this->assertEditable($plan);
        return $this->saveForm($model, $type, $plan);
    }

    public function actionDelete(string $type, int $id)
    {
        $class = $this->classFor($type);
        $model = $class::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบรายการ');
        $plan = $this->planFromModel($model, $type);
        $this->assertEditable($plan); $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการแล้ว');
        return $this->redirect(['index', 'type' => $type, 'planId' => $plan->id]);
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
            'indicators' => $this->indicatorItems($plan),
            'measures' => $this->measureItems($plan),
        ]);
    }

    private function classFor(string $type): string
    {
        if (!isset(self::TYPES[$type])) throw new NotFoundHttpException('ไม่รู้จักชนิดทะเบียน');
        return self::TYPES[$type];
    }

    private function assignParent($model, string $type, ?int $planId, ?int $parentId): void
    {
        if ($type === 'indicator' || $type === 'program') $model->plan_id = $planId;
        if ($type === 'value') $model->indicator_id = $parentId;
        if ($type === 'factor' || $type === 'measure') $model->goal_id = $parentId;
    }

    private function resolvePlan($model, string $type, ?int $planId, ?int $parentId): StrategyPlan
    {
        if (in_array($type, ['indicator', 'program'], true)) return StrategyPlan::findOne($planId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน');
        if ($type === 'value') return StrategyIndicator::findOne($parentId)?->plan ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
        $goal = StrategyGoal::findOne($parentId);
        return $goal?->issue?->mission?->plan ?: throw new NotFoundHttpException('ไม่พบเป้าประสงค์');
    }

    private function planFromModel($model, string $type): StrategyPlan
    {
        if ($type === 'indicator' || $type === 'program') return $model->plan;
        if ($type === 'value') return $model->indicator->plan;
        return $model->goal->issue->mission->plan;
    }

    private function filterByPlan($query, string $type, int $planId): void
    {
        if (in_array($type, ['indicator', 'program'], true)) { $query->andWhere(['plan_id' => $planId]); return; }
        if ($type === 'value') { $query->joinWith('indicator')->andWhere(['pm_strategy_indicator.plan_id' => $planId]); return; }
        $query->joinWith('goal.issue.mission')->andWhere(['pm_strategy_mission.plan_id' => $planId]);
    }

    private function orderFor(string $type): array { return $type === 'value' ? ['fiscal_year' => SORT_ASC] : ['sort_order' => SORT_ASC, 'id' => SORT_ASC]; }
    private function assertEditable(StrategyPlan $plan): void { if (!$plan->isEditable()) throw new ForbiddenHttpException('ข้อมูลของแผนที่ประกาศใช้แล้วถูกล็อก'); }
    private function goalItems(StrategyPlan $plan): array { $items=[]; foreach($plan->missions as $m) foreach($m->issues as $i) foreach($i->goals as $g) $items[$g->id]="$g->code — $g->name"; return $items; }
    private function indicatorItems(StrategyPlan $plan): array { $items=[]; foreach($plan->indicators as $i) $items[$i->id]="$i->code — $i->name"; return $items; }
    private function measureItems(StrategyPlan $plan): array { $items=[]; foreach(StrategyMeasure::find()->joinWith('goal.issue.mission')->where(['pm_strategy_mission.plan_id'=>$plan->id])->all() as $m) $items[$m->id]=trim("$m->code — $m->name", ' —'); return $items; }
}
