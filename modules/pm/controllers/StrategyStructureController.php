<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\pm\models\{StrategyPlan, StrategyMission, StrategyIssue, StrategyGoal};

class StrategyStructureController extends Controller
{
    private const TYPES = [
        'mission' => [StrategyMission::class, 'plan_id'],
        'issue' => [StrategyIssue::class, 'mission_id'],
        'goal' => [StrategyGoal::class, 'issue_id'],
    ];

    public function behaviors(): array
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['pmStrategyManage']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST']]],
        ];
    }

    public function actionCreate(string $type, int $parentId)
    {
        [$class, $foreignKey] = $this->type($type);
        $model = new $class([$foreignKey => $parentId, 'is_active' => true]);
        $plan = $this->resolvePlan($type, $parentId);
        $this->assertEditable($plan);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มรายการในโครงสร้างแล้ว');
            return $this->redirect(['/pm/strategy-plan/view', 'id' => $plan->id]);
        }
        return $this->render('form', ['model' => $model, 'type' => $type, 'plan' => $plan]);
    }

    public function actionUpdate(string $type, int $id)
    {
        [$class] = $this->type($type);
        $model = $class::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบรายการ');
        $plan = $this->planFromModel($type, $model);
        $this->assertEditable($plan);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'แก้ไขรายการแล้ว');
            return $this->redirect(['/pm/strategy-plan/view', 'id' => $plan->id]);
        }
        return $this->render('form', ['model' => $model, 'type' => $type, 'plan' => $plan]);
    }

    public function actionDelete(string $type, int $id)
    {
        [$class] = $this->type($type);
        $model = $class::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบรายการ');
        $plan = $this->planFromModel($type, $model);
        $this->assertEditable($plan); $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการแล้ว');
        return $this->redirect(['/pm/strategy-plan/view', 'id' => $plan->id]);
    }

    private function type(string $type): array
    {
        if (!isset(self::TYPES[$type])) throw new NotFoundHttpException('ไม่รู้จักชนิดข้อมูล');
        return self::TYPES[$type];
    }
    private function resolvePlan(string $type, int $parentId): StrategyPlan
    {
        if ($type === 'mission') return StrategyPlan::findOne($parentId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน');
        if ($type === 'issue') { $m = StrategyMission::findOne($parentId); return $m?->plan ?: throw new NotFoundHttpException('ไม่พบพันธกิจ'); }
        $g = StrategyIssue::findOne($parentId); return $g?->mission?->plan ?: throw new NotFoundHttpException('ไม่พบประเด็นยุทธศาสตร์');
    }
    private function planFromModel(string $type, $model): StrategyPlan
    {
        return $type === 'mission' ? $model->plan : ($type === 'issue' ? $model->mission->plan : $model->issue->mission->plan);
    }
    private function assertEditable(StrategyPlan $plan): void
    {
        if (!$plan->isEditable()) throw new ForbiddenHttpException('ไม่สามารถแก้ไขชุดแผนที่ประกาศใช้แล้ว');
    }
}
