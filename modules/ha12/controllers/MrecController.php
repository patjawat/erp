<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\ha12\models\Ha12MrecAudit;
use app\modules\ha12\models\Ha12MrecItem;
use app\modules\ha12\services\Ha12MrecService;
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
 * ความสมบูรณ์ของเวชระเบียน (กิจกรรม 9) — เฟส 2b
 */
class MrecController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'save-audit' => ['POST'], 'add-item' => ['POST'], 'delete-item' => ['POST'],
                'delete' => ['POST'], 'restore' => ['POST'],
            ]],
        ]);
    }

    private function fiscalYear(): int
    {
        return (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();
    }

    private function me(): array
    {
        $me = UserHelper::GetEmployee();
        return [$me, $me ? (int) $me->department : null, Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null];
    }

    private function findAudit(int $id): Ha12MrecAudit
    {
        $a = Ha12MrecAudit::findOne($id);
        if (!$a) {
            throw new NotFoundHttpException('ไม่พบการตรวจ');
        }
        return $a;
    }

    private function assertManage(Ha12MrecAudit $a): void
    {
        [, $empUnitId, $userId] = $this->me();
        if (!Ha12MrecService::canManage($a, $userId, $empUnitId)) {
            throw new ForbiddenHttpException('จัดการได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }
    }

    public function actionIndex()
    {
        [, $empUnitId, $userId] = $this->me();
        $req = Yii::$app->request;
        $fiscalYear = $this->fiscalYear();
        $unitId = (int) $req->get('unit_id') ?: null;
        $showDeleted = (int) $req->get('deleted') === 1;

        $query = Ha12MrecAudit::find()->with(['ownerUnit', 'items'])
            ->where(['fiscal_year' => $fiscalYear, 'deleted' => $showDeleted ? 1 : 0]);
        if (!Ha12ReviewService::isManager()) {
            $scope = Ha12ReviewService::unitScopeIds($empUnitId) ?: [-1];
            $query->andWhere(['or', ['owner_unit_id' => $scope], ['created_by' => $userId ?? -1]]);
        }
        if ($unitId) {
            $query->andWhere(['owner_unit_id' => $unitId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['review_date' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20], 'sort' => false,
        ]);

        return $this->render('mrec/index', [
            'audits' => $dataProvider->getModels(), 'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear, 'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'units' => Ha12ReviewService::orderedUnits(),
            'filters' => ['unit_id' => $unitId, 'deleted' => $showDeleted ? 1 : 0],
        ]);
    }

    public function actionCreate()
    {
        [$me, $empUnitId] = $this->me();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
        $req = Yii::$app->request;
        $model = new Ha12MrecAudit(['fiscal_year' => $this->fiscalYear(), 'owner_unit_id' => (int) $me->department ?: null]);

        if ($req->isPost) {
            $model->load($req->post());
            $model->period_start = AppHelper::normalizeDateToDb($req->post('period_start_thai'));
            $model->period_end = AppHelper::normalizeDateToDb($req->post('period_end_thai'));
            $model->review_date = AppHelper::normalizeDateToDb($req->post('review_date_thai'));
            $allowed = array_map(static fn ($o) => (int) $o['id'], Ha12ReviewService::selectableUnits($empUnitId));
            if ($model->owner_unit_id && !in_array((int) $model->owner_unit_id, $allowed, true) && !Ha12ReviewService::isManager()) {
                $model->owner_unit_id = $empUnitId;
            }
            if ($model->validate() && Ha12MrecService::createWithScaffold($model)) {
                if ($req->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'redirect_url' => \yii\helpers\Url::to(['edit', 'id' => $model->id])];
                }
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }
        $data = ['model' => $model, 'units' => Ha12ReviewService::selectableUnits($empUnitId)];
        if ($req->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'สร้างการตรวจเวชระเบียน', 'content' => $this->renderAjax('mrec/_form', $data)];
        }
        return $this->render('mrec/_form', $data);
    }

    public function actionEdit($id)
    {
        $audit = Ha12MrecAudit::find()->with(['ownerUnit', 'items'])->where(['id' => (int) $id])->one();
        if (!$audit) {
            throw new NotFoundHttpException('ไม่พบการตรวจ');
        }
        $this->assertManage($audit);
        return $this->render('mrec/edit', ['audit' => $audit]);
    }

    public function actionView($id)
    {
        $audit = Ha12MrecAudit::find()->with(['ownerUnit', 'items'])->where(['id' => (int) $id])->one();
        if (!$audit) {
            throw new NotFoundHttpException('ไม่พบการตรวจ');
        }
        [, $empUnitId, $userId] = $this->me();
        if (!Ha12MrecService::canView($audit, $userId, $empUnitId)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูรายการนี้');
        }
        return $this->render('mrec/view', [
            'audit' => $audit,
            'canManage' => Ha12MrecService::canManage($audit, $userId, $empUnitId),
        ]);
    }

    public function actionSaveAudit($id)
    {
        $audit = Ha12MrecAudit::find()->with(['items'])->where(['id' => (int) $id])->one();
        if (!$audit) {
            throw new NotFoundHttpException('ไม่พบการตรวจ');
        }
        $this->assertManage($audit);
        $post = Yii::$app->request->post();

        $audit->load($post);
        $audit->review_date = AppHelper::normalizeDateToDb(Yii::$app->request->post('review_date_thai')) ?: $audit->review_date;

        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$audit->save()) {
                throw new \RuntimeException('การตรวจ: ' . implode(' ', $audit->getFirstErrors()));
            }
            foreach ($audit->items as $it) {
                $ip = $post['item'][$it->id] ?? null;
                if ($ip === null) {
                    continue;
                }
                $it->complete_count = isset($ip['complete_count']) && $ip['complete_count'] !== '' ? (int) $ip['complete_count'] : null;
                if ($it->is_other && isset($ip['item_name']) && trim($ip['item_name']) !== '') {
                    $it->item_name = trim($ip['item_name']);
                }
                $it->note = isset($ip['note']) && trim($ip['note']) !== '' ? trim($ip['note']) : null;
                if (!$it->save()) {
                    throw new \RuntimeException($it->item_name . ': ' . implode(' ', $it->getFirstErrors()));
                }
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกเรียบร้อย');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
        }
        return $this->redirect(['edit', 'id' => $audit->id]);
    }

    public function actionAddItem($id)
    {
        $audit = $this->findAudit((int) $id);
        $this->assertManage($audit);
        $name = trim((string) Yii::$app->request->post('item_name'));
        if ($name === '') {
            Yii::$app->session->setFlash('error', 'กรุณาระบุชื่อหัวข้อ');
            return $this->redirect(['edit', 'id' => $audit->id]);
        }
        $no = (int) Ha12MrecItem::find()->where(['audit_id' => $audit->id])->max('item_no');
        $sort = (int) Ha12MrecItem::find()->where(['audit_id' => $audit->id])->max('sort');
        (new Ha12MrecItem(['audit_id' => (int) $audit->id, 'item_no' => $no + 1, 'item_name' => $name, 'is_other' => 1, 'sort' => $sort + 1]))->save();
        Yii::$app->session->setFlash('success', 'เพิ่มหัวข้อแล้ว');
        return $this->redirect(['edit', 'id' => $audit->id]);
    }

    public function actionDeleteItem($id)
    {
        $it = Ha12MrecItem::findOne((int) $id);
        if (!$it) {
            throw new NotFoundHttpException('ไม่พบหัวข้อ');
        }
        $audit = $it->audit;
        $this->assertManage($audit);
        if (!$it->is_other) {
            Yii::$app->session->setFlash('error', 'ลบได้เฉพาะหัวข้อที่เพิ่มเอง');
            return $this->redirect(['edit', 'id' => $audit->id]);
        }
        $it->delete();
        Yii::$app->session->setFlash('success', 'ลบหัวข้อแล้ว');
        return $this->redirect(['edit', 'id' => $audit->id]);
    }

    public function actionDelete($id)
    {
        $audit = $this->findAudit((int) $id);
        $this->assertManage($audit);
        $audit->deleted = 1;
        $audit->save(false);
        Yii::$app->session->setFlash('success', 'ลบแล้ว (กู้คืนได้)');
        return $this->redirect(['index', 'fy' => $audit->fiscal_year]);
    }

    public function actionRestore($id)
    {
        $audit = $this->findAudit((int) $id);
        $this->assertManage($audit);
        $audit->deleted = 0;
        $audit->save(false);
        Yii::$app->session->setFlash('success', 'กู้คืนแล้ว');
        return $this->redirect(['index', 'fy' => $audit->fiscal_year, 'deleted' => 1]);
    }
}
