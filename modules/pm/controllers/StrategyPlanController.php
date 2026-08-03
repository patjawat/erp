<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\models\StrategyPlan;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class StrategyPlanController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['pmStrategyView']],
                ['allow' => true, 'roles' => ['pmStrategyManage']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['publish' => ['POST'], 'delete' => ['POST'], 'clone' => ['POST']]],
        ];
    }

    public function actionIndex()
    {
        $query = StrategyPlan::find()->orderBy(['start_year' => SORT_DESC, 'version' => SORT_DESC]);
        if (($q = trim((string) Yii::$app->request->get('q'))) !== '') {
            $query->andWhere(['or', ['like', 'code', $q], ['like', 'name', $q]]);
        }
        if ($status = Yii::$app->request->get('status')) $query->andWhere(['status' => $status]);
        return $this->render('index', ['dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 20]]), 'q' => $q ?? '', 'status' => $status ?? '']);
    }

    public function actionView($id) { return $this->render('view', ['model' => $this->findModel($id)]); }

    public function actionCreate()
    {
        $model = new StrategyPlan(['status' => StrategyPlan::STATUS_DRAFT, 'version' => 1]);
        return $this->saveForm($model, 'create');
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!$model->isEditable()) throw new ForbiddenHttpException('แผนที่ประกาศใช้แล้วแก้ไขไม่ได้ กรุณาสร้างรุ่นใหม่');
        return $this->saveForm($model, 'update');
    }

    private function saveForm(StrategyPlan $model, string $view)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกทะเบียนแผนยุทธศาสตร์แล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render($view, ['model' => $model]);
    }

    public function actionPublish($id)
    {
        $model = $this->findModel($id);
        if (!$model->isEditable()) return $this->redirect(['view', 'id' => $id]);
        if (!$model->missions) {
            Yii::$app->session->setFlash('warning', 'ต้องมีพันธกิจอย่างน้อย 1 รายการก่อนประกาศใช้');
        } else {
            $model->status = StrategyPlan::STATUS_PUBLISHED;
            $model->published_at = date('Y-m-d H:i:s');
            $model->published_by = Yii::$app->user->id;
            $model->save(false, ['status', 'published_at', 'published_by', 'updated_at', 'updated_by']);
            Yii::$app->session->setFlash('success', 'ประกาศใช้แผนยุทธศาสตร์แล้ว ข้อมูลชุดนี้ถูกล็อก');
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionClone($id)
    {
        $source = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();
        $copy = new StrategyPlan($source->attributes);
        $copy->id = null; $copy->ref = null; $copy->isNewRecord = true;
        $copy->version = (int) StrategyPlan::find()->where(['code' => $source->code])->max('version') + 1;
        $copy->status = StrategyPlan::STATUS_DRAFT; $copy->published_at = null; $copy->published_by = null;
        if ($copy->save()) {
            $this->cloneHierarchy($source, $copy);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'สร้างฉบับร่างรุ่นใหม่พร้อมคัดลอกโครงสร้างแล้ว');
            return $this->redirect(['view', 'id' => $copy->id]);
        }
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'ไม่สามารถสร้างรุ่นใหม่ได้');
        return $this->redirect(['view', 'id' => $id]);
    }

    private function cloneHierarchy(StrategyPlan $source, StrategyPlan $copy): void
    {
        $goalMap = [];
        foreach ($source->missions as $mission) {
            $m = new \app\modules\pm\models\StrategyMission($mission->attributes); $m->id = null; $m->ref = null; $m->isNewRecord = true; $m->plan_id = $copy->id; $m->save(false);
            foreach ($mission->issues as $issue) {
                $i = new \app\modules\pm\models\StrategyIssue($issue->attributes); $i->id = null; $i->ref = null; $i->isNewRecord = true; $i->mission_id = $m->id; $i->save(false);
                foreach ($issue->goals as $goal) {
                    $g = new \app\modules\pm\models\StrategyGoal($goal->attributes); $g->id = null; $g->ref = null; $g->isNewRecord = true; $g->issue_id = $i->id; $g->save(false);
                    $goalMap[$goal->id] = $g->id;
                }
            }
        }

        $indicatorMap = [];
        foreach ($source->indicators as $indicator) {
            $item = new \app\modules\pm\models\StrategyIndicator($indicator->attributes);
            $item->id = null; $item->ref = null; $item->isNewRecord = true; $item->plan_id = $copy->id;
            $item->goal_id = $indicator->goal_id ? ($goalMap[$indicator->goal_id] ?? null) : null;
            $item->parent_id = null; $item->save(false);
            $indicatorMap[$indicator->id] = $item->id;
            foreach ($indicator->values as $value) {
                $v = new \app\modules\pm\models\StrategyIndicatorValue($value->attributes);
                $v->id = null; $v->ref = null; $v->isNewRecord = true; $v->indicator_id = $item->id; $v->actual_value = null; $v->save(false);
            }
        }
        foreach ($source->indicators as $indicator) {
            if ($indicator->parent_id && isset($indicatorMap[$indicator->id], $indicatorMap[$indicator->parent_id])) {
                \app\modules\pm\models\StrategyIndicator::updateAll(['parent_id' => $indicatorMap[$indicator->parent_id]], ['id' => $indicatorMap[$indicator->id]]);
            }
        }

        $measureMap = [];
        foreach ($goalMap as $oldGoalId => $newGoalId) {
            foreach (\app\modules\pm\models\StrategySuccessFactor::findAll(['goal_id' => $oldGoalId]) as $factor) {
                $f = new \app\modules\pm\models\StrategySuccessFactor($factor->attributes); $f->id = null; $f->ref = null; $f->isNewRecord = true; $f->goal_id = $newGoalId; $f->save(false);
            }
            foreach (\app\modules\pm\models\StrategyMeasure::findAll(['goal_id' => $oldGoalId]) as $measure) {
                $m = new \app\modules\pm\models\StrategyMeasure($measure->attributes); $m->id = null; $m->ref = null; $m->isNewRecord = true; $m->goal_id = $newGoalId; $m->save(false); $measureMap[$measure->id] = $m->id;
            }
        }
        foreach ($source->programs as $program) {
            $p = new \app\modules\pm\models\StrategyProgram($program->attributes); $p->id = null; $p->ref = null; $p->isNewRecord = true; $p->plan_id = $copy->id;
            $p->measure_id = $program->measure_id ? ($measureMap[$program->measure_id] ?? null) : null; $p->save(false);
        }
    }

    private function findModel($id): StrategyPlan
    {
        if ($model = StrategyPlan::findOne($id)) return $model;
        throw new NotFoundHttpException('ไม่พบชุดแผนยุทธศาสตร์');
    }
}
