<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\ha12\models\Ha12MedCount;
use app\modules\ha12\models\Ha12MedReport;
use app\modules\ha12\services\Ha12MedService;
use app\modules\ha12\services\Ha12ReviewService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ความคลาดเคลื่อนทางยา (กิจกรรม 7) — เฟส 2a
 *
 * รายงานร่วมเภสัช+พยาบาล ต่อหน่วยงาน+ช่วงข้อมูล → 5 หัวข้อหลัก → แถวย่อยความเสี่ยง
 * ตามบท 7-8 ของคู่มือ (ระดับความรุนแรง No Harm/E-I + ตัวหาร/ฐาน/อัตรา)
 */
class MedController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save-report' => ['POST'],
                    'add-other' => ['POST'],
                    'delete-count' => ['POST'],
                    'delete' => ['POST'],
                    'restore' => ['POST'],
                ],
            ],
        ]);
    }

    private function fiscalYear(): int
    {
        $fy = (int) Yii::$app->request->get('fy');
        return $fy ?: (int) AppHelper::YearBudget();
    }

    private function me(): array
    {
        $me = UserHelper::GetEmployee();
        return [
            $me,
            $me ? (int) $me->department : null,
            Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null,
        ];
    }

    private function findReport(int $id): Ha12MedReport
    {
        $r = Ha12MedReport::findOne($id);
        if (!$r) {
            throw new NotFoundHttpException('ไม่พบรายงานยา');
        }
        return $r;
    }

    private function assertManage(Ha12MedReport $r): void
    {
        [, $empUnitId, $userId] = $this->me();
        if (!Ha12MedService::canManage($r, $userId, $empUnitId)) {
            throw new ForbiddenHttpException('จัดการได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }
    }

    /** รายการรายงานยา */
    public function actionIndex()
    {
        [, $empUnitId, $userId] = $this->me();
        $req = Yii::$app->request;
        $fiscalYear = $this->fiscalYear();
        $unitId = (int) $req->get('unit_id') ?: null;
        $showDeleted = (int) $req->get('deleted') === 1;

        $query = Ha12MedReport::find()
            ->with(['ownerUnit'])
            ->where(['fiscal_year' => $fiscalYear, 'deleted' => $showDeleted ? 1 : 0]);
        if (!Ha12ReviewService::isManager()) {
            $scope = Ha12ReviewService::unitScopeIds($empUnitId) ?: [-1];
            $query->andWhere(['or', ['owner_unit_id' => $scope], ['created_by' => $userId ?? -1]]);
        }
        if ($unitId) {
            $query->andWhere(['owner_unit_id' => $unitId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['period_end' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
            'sort' => false,
        ]);

        return $this->render('index', [
            'reports' => $dataProvider->getModels(),
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'units' => Ha12ReviewService::orderedUnits(),
            'filters' => ['unit_id' => $unitId, 'deleted' => $showDeleted ? 1 : 0],
        ]);
    }

    /** สร้างรายงานใหม่ (AJAX modal) — บันทึกแล้วสร้างโครง 5 หัวข้อ + ความเสี่ยงมาตรฐาน */
    public function actionCreate()
    {
        [$me, $empUnitId] = $this->me();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
        $req = Yii::$app->request;

        $model = new Ha12MedReport([
            'fiscal_year' => $this->fiscalYear(),
            'owner_unit_id' => (int) $me->department ?: null,
        ]);

        if ($req->isPost) {
            $model->load($req->post());
            $model->period_start = AppHelper::normalizeDateToDb($req->post('period_start_thai'));
            $model->period_end = AppHelper::normalizeDateToDb($req->post('period_end_thai'));
            $model->review_date = AppHelper::normalizeDateToDb($req->post('review_date_thai'));

            // กันเลือกหน่วยงานนอกสิทธิ์
            $allowed = array_map(static fn ($o) => (int) $o['id'], Ha12ReviewService::selectableUnits($empUnitId));
            if ($model->owner_unit_id && !in_array((int) $model->owner_unit_id, $allowed, true) && !Ha12ReviewService::isManager()) {
                $model->owner_unit_id = $empUnitId;
            }

            if ($model->validate() && Ha12MedService::createWithScaffold($model)) {
                if ($req->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => 'สร้างรายงานแล้ว', 'redirect_url' => \yii\helpers\Url::to(['edit', 'id' => $model->id])];
                }
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        $data = ['model' => $model, 'units' => Ha12ReviewService::selectableUnits($empUnitId)];
        if ($req->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'สร้างรายงานความคลาดเคลื่อนทางยา', 'content' => $this->renderAjax('_form', $data)];
        }
        return $this->render('_form', $data);
    }

    /** หน้าแก้ไขรายงาน (ตารางกรอก 5 หัวข้อ) */
    public function actionEdit($id)
    {
        $report = Ha12MedReport::find()->with(['ownerUnit', 'groups.counts'])->where(['id' => (int) $id])->one();
        if (!$report) {
            throw new NotFoundHttpException('ไม่พบรายงานยา');
        }
        $this->assertManage($report);

        return $this->render('edit', [
            'report' => $report,
            'canManage' => true,
        ]);
    }

    /** ดูรายงาน (อ่านอย่างเดียว) */
    public function actionView($id)
    {
        $report = Ha12MedReport::find()->with(['ownerUnit', 'groups.counts'])->where(['id' => (int) $id])->one();
        if (!$report) {
            throw new NotFoundHttpException('ไม่พบรายงานยา');
        }
        [, $empUnitId, $userId] = $this->me();
        if (!Ha12MedService::canView($report, $userId, $empUnitId)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูรายงานนี้');
        }
        return $this->render('view', [
            'report' => $report,
            'canManage' => Ha12MedService::canManage($report, $userId, $empUnitId),
        ]);
    }

    /** บันทึกทั้งรายงาน (meta + ทุกหัวข้อ + ทุกแถวย่อย) */
    public function actionSaveReport($id)
    {
        $report = Ha12MedReport::find()->with(['groups.counts'])->where(['id' => (int) $id])->one();
        if (!$report) {
            throw new NotFoundHttpException('ไม่พบรายงานยา');
        }
        $this->assertManage($report);
        $post = Yii::$app->request->post();

        // meta
        $report->review_date = AppHelper::normalizeDateToDb(Yii::$app->request->post('review_date_thai')) ?: $report->review_date;
        $report->note = $post['Ha12MedReport']['note'] ?? $report->note;

        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$report->save()) {
                throw new \RuntimeException('รายงาน: ' . implode(' ', $report->getFirstErrors()));
            }
            foreach ($report->groups as $g) {
                $gp = $post['group'][$g->id] ?? [];
                $g->divisor = isset($gp['divisor']) && $gp['divisor'] !== '' ? (int) $gp['divisor'] : null;
                if (!empty($gp['divisor_unit'])) {
                    $g->divisor_unit = $gp['divisor_unit'];
                }
                $g->rate_base = isset($gp['rate_base']) && $gp['rate_base'] !== '' ? (int) $gp['rate_base'] : null;
                $g->note = $gp['note'] ?? $g->note;
                if (!$g->save()) {
                    throw new \RuntimeException($g->groupName() . ': ' . implode(' ', $g->getFirstErrors()));
                }
                foreach ($g->counts as $c) {
                    $cp = $post['count'][$c->id] ?? null;
                    if ($cp === null) {
                        continue;
                    }
                    foreach (Ha12MedCount::SEVERITY_COLS as $col) {
                        $c->$col = isset($cp[$col]) && $cp[$col] !== '' ? (int) $cp[$col] : null;
                    }
                    $c->team = !empty($cp['team']) ? $cp['team'] : null;
                    if ($c->is_other && isset($cp['risk_name']) && trim($cp['risk_name']) !== '') {
                        $c->risk_name = trim($cp['risk_name']);
                    }
                    $c->review_result = isset($cp['review_result']) && trim($cp['review_result']) !== '' ? trim($cp['review_result']) : null;
                    $c->fix = isset($cp['fix']) && trim($cp['fix']) !== '' ? trim($cp['fix']) : null;
                    if (!$c->save()) {
                        throw new \RuntimeException($c->risk_name . ': ' . implode(' ', $c->getFirstErrors()));
                    }
                }
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกรายงานเรียบร้อย');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
        }
        return $this->redirect(['edit', 'id' => $report->id]);
    }

    /** เพิ่มความเสี่ยง "อื่น ๆ" ในหัวข้อ */
    public function actionAddOther($id)
    {
        $group = \app\modules\ha12\models\Ha12MedGroup::findOne((int) $id);
        if (!$group) {
            throw new NotFoundHttpException('ไม่พบหัวข้อ');
        }
        $this->assertManage($group->report);

        $name = trim((string) Yii::$app->request->post('risk_name'));
        if ($name === '') {
            Yii::$app->session->setFlash('error', 'กรุณาระบุชื่อความเสี่ยง');
            return $this->redirect(['edit', 'id' => $group->report_id]);
        }
        // กันชื่อซ้ำในหัวข้อเดียวกัน (บท 7: อื่น ๆ ต้องชื่อไม่ซ้ำ)
        $dup = Ha12MedCount::find()->where(['group_id' => $group->id, 'risk_name' => $name])->exists();
        if ($dup) {
            Yii::$app->session->setFlash('error', 'มีความเสี่ยงชื่อนี้ในหัวข้อแล้ว');
            return $this->redirect(['edit', 'id' => $group->report_id]);
        }
        $sort = (int) Ha12MedCount::find()->where(['group_id' => $group->id])->max('sort');
        $c = new Ha12MedCount(['group_id' => (int) $group->id, 'risk_name' => $name, 'is_other' => 1, 'sort' => $sort + 1]);
        $c->save();
        Yii::$app->session->setFlash('success', 'เพิ่มความเสี่ยงแล้ว');
        return $this->redirect(['edit', 'id' => $group->report_id]);
    }

    /** ลบแถวความเสี่ยง (เฉพาะที่เพิ่มเอง is_other=1) */
    public function actionDeleteCount($id)
    {
        $c = Ha12MedCount::findOne((int) $id);
        if (!$c) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $report = $c->group->report;
        $this->assertManage($report);
        if (!$c->is_other) {
            Yii::$app->session->setFlash('error', 'ลบได้เฉพาะความเสี่ยงที่เพิ่มเอง');
            return $this->redirect(['edit', 'id' => $report->id]);
        }
        $c->delete();
        Yii::$app->session->setFlash('success', 'ลบความเสี่ยงแล้ว');
        return $this->redirect(['edit', 'id' => $report->id]);
    }

    public function actionDelete($id)
    {
        $report = $this->findReport((int) $id);
        $this->assertManage($report);
        $report->deleted = 1;
        $report->save(false);
        Yii::$app->session->setFlash('success', 'ลบรายงานแล้ว (กู้คืนได้จากตัวกรอง)');
        return $this->redirect(['index', 'fy' => $report->fiscal_year]);
    }

    public function actionRestore($id)
    {
        $report = $this->findReport((int) $id);
        $this->assertManage($report);
        $report->deleted = 0;
        $report->save(false);
        Yii::$app->session->setFlash('success', 'กู้คืนรายงานแล้ว');
        return $this->redirect(['index', 'fy' => $report->fiscal_year, 'deleted' => 1]);
    }
}
