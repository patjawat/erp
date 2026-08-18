<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\pm\models\{StrategyPlan, StrategyMission, StrategyIssue, StrategyGoal, StrategyTactic, StrategyIndicator, Projects};
use app\modules\plan\components\PlanHelper;

/**
 * โครงกระดูกของแผนยุทธศาสตร์ — กรอกเฉพาะรหัสและชื่อ
 *
 * พันธกิจ → ประเด็นยุทธศาสตร์ → เป้าประสงค์ → ตัวชี้วัดหลัก → ตัวชี้วัดรอง
 *                                          → ปัจจัยความสำเร็จ/RCA
 *
 * กลยุทธ์อยู่ใต้ตัวชี้วัด (หลักหรือรอง) และมีมาตรการกับโครงการอยู่ใต้กลยุทธ์อีกชั้น
 * รายละเอียดตัวชี้วัดไปกรอกที่หน้าตัวชี้วัด และรายละเอียดโครงการที่หน้าโครงการ
 */
class StrategyStructureController extends Controller
{
    private const TYPES = [
        'mission' => [StrategyMission::class, 'plan_id'],
        'issue' => [StrategyIssue::class, 'mission_id'],
        'goal' => [StrategyGoal::class, 'issue_id'],
        'indicator' => [StrategyIndicator::class, 'goal_id'],
        'sub-indicator' => [StrategyIndicator::class, 'parent_id'],
        'tactic' => [StrategyTactic::class, 'indicator_id'],
        'project' => [Projects::class, 'tactic_id'],
        'activity' => [Projects::class, 'tactic_id'],
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
        $model = new $class([$foreignKey => $parentId]);
        $this->applyDefaults($model, $type, $parentId);
        $plan = $this->resolvePlan($type, $parentId);
        $this->assertEditable($plan);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มรายการในโครงสร้างแล้ว');
            return $this->redirect(['/pm/strategy-plan/view', 'id' => $plan->id]);
        }
        return $this->render('form', ['model' => $model, 'type' => $type, 'plan' => $plan]);
    }

    /**
     * ค่าตั้งต้นที่ผูกกับชนิดข้อมูล — ตัวชี้วัดรองต้องสืบเป้าประสงค์จากตัวชี้วัดหลัก
     * ส่วนโครงการต้องมีชุดแผนและปีงบประมาณตั้งแต่แรกเพื่อให้บันทึกผ่าน
     */
    private function applyDefaults($model, string $type, int $parentId): void
    {
        if ($model->hasAttribute('is_active')) {
            $model->is_active = true;
        }
        if ($type === 'indicator') {
            $model->plan_id = StrategyGoal::findOne($parentId)?->issue?->mission?->plan_id;
            $model->level = 'hospital';
        }
        if ($type === 'sub-indicator') {
            $parent = StrategyIndicator::findOne($parentId) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัดหลัก');
            $model->plan_id = $parent->plan_id;
            $model->goal_id = $parent->goal_id;
            $model->level = $parent->level;
        }
        if ($type === 'tactic') {
            // goal_id เป็นค่าที่ derive จากตัวชี้วัด แต่ต้องมีตั้งแต่ตอน validate
            $model->goal_id = StrategyIndicator::findOne($parentId)?->goal_id;
        }
        if ($type === 'project' || $type === 'activity') {
            $model->work_type = $type === 'activity' ? Projects::WORK_ACTIVITY : Projects::WORK_PROJECT;
            $model->thai_year = (int) PlanHelper::currentPlanYear(); // ปีเดียวกับระบบแผน = ปีที่ทะเบียนหน่วยงานจัดชุดไว้
            $model->status = Projects::STATUS_DRAFT;
            // โครงการต้องมีหน่วยงานเจ้าของเสมอ ตั้งต้นจากหน่วยงานของผู้สร้างไว้ก่อน
            if ($me = \app\components\UserHelper::GetEmployee()) {
                $model->department_id = $me->department;
                $model->syncOrgUnit();
            }
        }
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
        $this->assertEditable($plan);
        if ($type === 'project' || $type === 'activity') {
            // โครงการและกิจกรรมมีรายละเอียดกับประวัติของตัวเอง จึงลบแบบ soft ให้ตรงกับหน้าโครงการ
            $model->deleted_at = date('Y-m-d H:i:s');
            $model->deleted_by = Yii::$app->user->id;
            $model->save(false, ['deleted_at', 'deleted_by']);
        } else {
            $model->delete();
        }
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
        return match ($type) {
            'mission' => StrategyPlan::findOne($parentId) ?: throw new NotFoundHttpException('ไม่พบชุดแผน'),
            'issue' => StrategyMission::findOne($parentId)?->plan ?: throw new NotFoundHttpException('ไม่พบพันธกิจ'),
            'goal' => StrategyIssue::findOne($parentId)?->mission?->plan ?: throw new NotFoundHttpException('ไม่พบประเด็นยุทธศาสตร์'),
            'indicator' => StrategyGoal::findOne($parentId)?->issue?->mission?->plan ?: throw new NotFoundHttpException('ไม่พบเป้าประสงค์'),
            'sub-indicator', 'tactic' => StrategyIndicator::findOne($parentId)?->plan ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัด'),
            'project', 'activity' => StrategyTactic::findOne($parentId)?->goal?->issue?->mission?->plan ?: throw new NotFoundHttpException('ไม่พบกลยุทธ์'),
        };
    }
    private function planFromModel(string $type, $model): StrategyPlan
    {
        return match ($type) {
            'mission' => $model->plan,
            'issue' => $model->mission->plan,
            'goal' => $model->issue->mission->plan,
            'indicator', 'sub-indicator' => $model->plan,
            'tactic' => $model->goal->issue->mission->plan,
            'project', 'activity' => $model->tactic->goal->issue->mission->plan,
        };
    }
    private function assertEditable(StrategyPlan $plan): void
    {
        if (!$plan->isEditable()) throw new ForbiddenHttpException('ไม่สามารถแก้ไขชุดแผนที่ประกาศใช้แล้ว');
    }
}
