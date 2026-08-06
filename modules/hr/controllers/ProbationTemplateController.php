<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\ProbationTemplate;
use app\modules\hr\models\ProbationTemplateItem;

class ProbationTemplateController extends Controller
{
    public function behaviors() { return [
        'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        'verbs' => ['class' => VerbFilter::class, 'actions' => ['activate' => ['POST']]],
    ]; }
    public function beforeAction($action) { if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) throw new ForbiddenHttpException('สำหรับ HR เท่านั้น'); return parent::beforeAction($action); }
    public function actionIndex() { return $this->render('index', ['dataProvider' => new ActiveDataProvider(['query' => ProbationTemplate::find()->with('positionGroup')->orderBy(['updated_at' => SORT_DESC]), 'pagination' => ['pageSize' => 20]])]); }
    public function actionForm($id = null)
    {
        $model = $id ? $this->findModel($id) : new ProbationTemplate(['revision_no' => 1, 'status' => 'draft']);
        if (!$model->isNewRecord && $model->status !== 'draft') throw new ForbiddenHttpException('Template ที่ใช้งานแล้วแก้ไขไม่ได้ กรุณาสร้าง revision ใหม่');
        $isNewRecord = $model->isNewRecord;
        if ($model->load(Yii::$app->request->post())) {
            if ($isNewRecord) {
                $model->revision_no = ((int) ProbationTemplate::find()
                    ->where(['position_group_id' => $model->position_group_id])
                    ->max('revision_no')) + 1;
            }
            if ($model->save()) return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model, 'positionGroups' => EmployeePositionGroup::listItems()]);
    }
    public function actionView($id) { return $this->render('view', ['model' => $this->findModel($id)]); }
    public function actionCategory($template_id)
    {
        $template = $this->findModel($template_id);
        if ($template->status !== 'draft') throw new ForbiddenHttpException('เพิ่มหมวดได้เฉพาะฉบับร่าง');

        $category = trim((string) Yii::$app->request->post('category'));
        $questions = array_values(array_filter(array_map(
            static fn($question) => trim((string) $question),
            (array) Yii::$app->request->post('questions', [])
        ), static fn($question) => $question !== ''));
        if (Yii::$app->request->isPost) {
            if ($category === '') Yii::$app->session->setFlash('danger', 'กรุณาระบุชื่อหมวด');
            elseif (!$questions) Yii::$app->session->setFlash('danger', 'กรุณาเพิ่มรายการประเมินอย่างน้อย 1 รายการ');
            else {
                $tx = Yii::$app->db->beginTransaction();
                try {
                    $sequence = ((int) ProbationTemplateItem::find()->where(['template_id' => $template->id])->max('sequence')) + 1;
                    foreach ($questions as $question) {
                        $item = new ProbationTemplateItem([
                            'template_id' => $template->id,
                            'category' => $category,
                            'question' => $question,
                            'max_score' => 5,
                            'sequence' => $sequence++,
                            'active' => 1,
                        ]);
                        if (!$item->save()) throw new \RuntimeException(implode(', ', $item->getFirstErrors()));
                    }
                    $tx->commit();
                    Yii::$app->session->setFlash('success', 'เพิ่มหมวดและรายการประเมินแล้ว');
                    return $this->redirect(['view', 'id' => $template->id]);
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    throw $e;
                }
            }
        }
        return $this->render('category', compact('template', 'category', 'questions'));
    }
    public function actionItem($template_id, $id = null)
    {
        $template = $this->findModel($template_id);
        if ($template->status !== 'draft') throw new ForbiddenHttpException('แก้ข้อประเมินได้เฉพาะฉบับร่าง');
        $model = $id ? ProbationTemplateItem::findOne(['id' => $id, 'template_id' => $template->id]) : new ProbationTemplateItem(['template_id' => $template->id, 'max_score' => 5, 'active' => 1, 'sequence' => ((int) ProbationTemplateItem::find()->where(['template_id' => $template->id])->max('sequence')) + 1]);
        if (!$model) throw new NotFoundHttpException('ไม่พบข้อประเมิน');
        if ($model->load(Yii::$app->request->post())) {
            $model->max_score = 5;
            if ($model->save()) return $this->redirect(['view', 'id' => $template->id]);
        }
        return $this->render('item', compact('model', 'template'));
    }
    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        if (!$model->items) { Yii::$app->session->setFlash('danger', 'ต้องมีข้อประเมินอย่างน้อย 1 ข้อ'); return $this->redirect(['view', 'id' => $id]); }
        ProbationTemplate::updateAll(['status' => 'retired'], ['position_group_id' => $model->position_group_id, 'status' => 'active']);
        $model->status = 'active'; $model->effective_date = date('Y-m-d'); $model->save(false);
        Yii::$app->session->setFlash('success', 'เปิดใช้งาน Template แล้ว'); return $this->redirect(['view', 'id' => $id]);
    }
    public function actionRevision($id)
    {
        $source = $this->findModel($id); $tx = Yii::$app->db->beginTransaction();
        try {
            $revision = new ProbationTemplate(['position_group_id' => $source->position_group_id, 'name' => $source->name, 'revision_no' => ((int) ProbationTemplate::find()->where(['position_group_id' => $source->position_group_id])->max('revision_no')) + 1, 'status' => 'draft', 'description' => $source->description]);
            if (!$revision->save()) throw new \RuntimeException(implode(', ', $revision->getFirstErrors()));
            foreach ($source->items as $item) { $copy = new ProbationTemplateItem(['template_id' => $revision->id, 'category' => $item->category, 'question' => $item->question, 'max_score' => $item->max_score, 'sequence' => $item->sequence, 'active' => 1]); if (!$copy->save()) throw new \RuntimeException(implode(', ', $copy->getFirstErrors())); }
            $tx->commit(); return $this->redirect(['view', 'id' => $revision->id]);
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
    }
    private function findModel($id): ProbationTemplate { $model = ProbationTemplate::find()->with(['items', 'positionGroup'])->where(['id' => $id])->one(); if (!$model) throw new NotFoundHttpException('ไม่พบ Template'); return $model; }
}
