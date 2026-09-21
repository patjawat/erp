<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\ha12\models\Ha12Indicator;
use app\modules\ha12\models\Ha12IndicatorValue;
use app\modules\ha12\services\Ha12IndicatorService;
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
 * ติดตามตัวชี้วัดสำคัญ (กิจกรรม 12) — เฟส 2c (standalone)
 */
class IndicatorController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'save-values' => ['POST'], 'delete' => ['POST'], 'restore' => ['POST'],
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

    private function findIndicator(int $id): Ha12Indicator
    {
        $i = Ha12Indicator::findOne($id);
        if (!$i) {
            throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
        }
        return $i;
    }

    private function assertManage(Ha12Indicator $i): void
    {
        [, $empUnitId, $userId] = $this->me();
        if (!Ha12IndicatorService::canManage($i, $userId, $empUnitId)) {
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

        $query = Ha12Indicator::find()->with(['ownerUnit', 'values'])
            ->where(['fiscal_year' => $fiscalYear, 'deleted' => $showDeleted ? 1 : 0]);
        if (!Ha12ReviewService::isManager()) {
            $scope = Ha12ReviewService::unitScopeIds($empUnitId) ?: [-1];
            $query->andWhere(['or', ['owner_unit_id' => $scope], ['created_by' => $userId ?? -1]]);
        }
        if ($unitId) {
            $query->andWhere(['owner_unit_id' => $unitId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20], 'sort' => false,
        ]);

        return $this->render('index', [
            'indicators' => $dataProvider->getModels(), 'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear, 'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'units' => Ha12ReviewService::orderedUnits(),
            'filters' => ['unit_id' => $unitId, 'deleted' => $showDeleted ? 1 : 0],
        ]);
    }

    /** สร้าง/แก้ไขข้อมูลตัวชี้วัด (AJAX modal) */
    public function actionCreate()
    {
        [$me, $empUnitId] = $this->me();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
        $model = new Ha12Indicator(['fiscal_year' => $this->fiscalYear(), 'owner_unit_id' => (int) $me->department ?: null]);
        return $this->saveMeta($model, 'เพิ่มตัวชี้วัด', $empUnitId);
    }

    public function actionUpdate($id)
    {
        $model = $this->findIndicator((int) $id);
        $this->assertManage($model);
        [, $empUnitId] = $this->me();
        return $this->saveMeta($model, 'แก้ไขตัวชี้วัด', $empUnitId);
    }

    private function saveMeta(Ha12Indicator $model, string $title, ?int $empUnitId)
    {
        $req = Yii::$app->request;
        if ($req->isPost) {
            $model->load($req->post());
            $allowed = array_map(static fn ($o) => (int) $o['id'], Ha12ReviewService::selectableUnits($empUnitId));
            if ($model->owner_unit_id && !in_array((int) $model->owner_unit_id, $allowed, true) && !Ha12ReviewService::isManager()) {
                $model->owner_unit_id = $empUnitId;
            }
            if ($model->save()) {
                if ($req->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => 'บันทึกแล้ว', 'container' => '#ha12-ind-list'];
                }
                return $this->redirect(['index']);
            }
        }
        $data = ['model' => $model, 'units' => Ha12ReviewService::selectableUnits($empUnitId)];
        if ($req->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => $title, 'content' => $this->renderAjax('_form', $data)];
        }
        return $this->render('_form', $data);
    }

    /** หน้ากรอกค่ารายเดือน */
    public function actionValues($id)
    {
        $model = Ha12Indicator::find()->with(['ownerUnit', 'values'])->where(['id' => (int) $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
        }
        [, $empUnitId, $userId] = $this->me();
        if (!Ha12IndicatorService::canView($model, $userId, $empUnitId)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูรายการนี้');
        }
        return $this->render('values', [
            'model' => $model,
            'canManage' => Ha12IndicatorService::canManage($model, $userId, $empUnitId),
        ]);
    }

    /** บันทึกค่ารายเดือน 12 เดือน (upsert) */
    public function actionSaveValues($id)
    {
        $model = $this->findIndicator((int) $id);
        $this->assertManage($model);
        $vals = (array) Yii::$app->request->post('value', []);

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach (range(1, 12) as $m) {
                $raw = $vals[$m] ?? '';
                $existing = Ha12IndicatorValue::find()->where(['indicator_id' => $model->id, 'month_no' => $m])->one();
                if ($raw === '' || $raw === null) {
                    if ($existing) {
                        $existing->delete();
                    }
                    continue;
                }
                $row = $existing ?: new Ha12IndicatorValue(['indicator_id' => (int) $model->id, 'month_no' => $m]);
                $row->value = $raw;
                if (!$row->save()) {
                    throw new \RuntimeException('เดือน ' . $m . ': ' . implode(' ', $row->getFirstErrors()));
                }
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกค่ารายเดือนแล้ว');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
        }
        return $this->redirect(['values', 'id' => $model->id]);
    }

    public function actionDelete($id)
    {
        $model = $this->findIndicator((int) $id);
        $this->assertManage($model);
        $model->deleted = 1;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'ลบแล้ว (กู้คืนได้)');
        return $this->redirect(['index', 'fy' => $model->fiscal_year]);
    }

    public function actionRestore($id)
    {
        $model = $this->findIndicator((int) $id);
        $this->assertManage($model);
        $model->deleted = 0;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'กู้คืนแล้ว');
        return $this->redirect(['index', 'fy' => $model->fiscal_year, 'deleted' => 1]);
    }
}
