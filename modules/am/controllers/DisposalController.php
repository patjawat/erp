<?php

namespace app\modules\am\controllers;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use app\components\AppHelper;
use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetCondition;
use app\modules\am\models\AssetDisposal;
use app\modules\am\models\AssetDisposalItem;
use app\modules\am\models\AssetDisposalSearch;
use app\modules\am\models\AssetType;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/**
 * Disposal request controller for assets pending disposal.
 */
class DisposalController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new AssetDisposalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new AssetDisposal();
        $model->fiscal_year = (int) AppHelper::YearBudget();
        $model->status = AssetDisposal::STATUS_PENDING_APPROVAL;
        $model->disposal_date = AppHelper::convertToThai(date('Y-m-d'));

        $items = [new AssetDisposalItem()];

        if ($model->load(Yii::$app->request->post())) {
            $postItems = Yii::$app->request->post('AssetDisposalItem', []);
            $items = $this->buildItemModels($postItems);
            if ($this->saveWithItems($model, $items, true)) {
                Yii::$app->session->setFlash('success', 'บันทึกใบขอจำหน่ายเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            $model->disposal_date = $model->disposal_date ? AppHelper::convertToThai($model->disposal_date) : '';
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
            'conditionOptions' => $this->getConditionOptions(),
            'departmentOptions' => $this->getDepartmentOptions(),
            'assetTypeOptions' => $this->getAssetTypeOptions(),
            'statusOptions' => AssetDisposal::statusList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->disposal_date = $model->disposal_date ? AppHelper::convertToThai($model->disposal_date) : '';
        $items = $model->disposalItems ?: [new AssetDisposalItem()];

        if ($model->load(Yii::$app->request->post())) {
            $postItems = Yii::$app->request->post('AssetDisposalItem', []);
            $items = $this->buildItemModels($postItems);
            if ($this->saveWithItems($model, $items, false)) {
                Yii::$app->session->setFlash('success', 'บันทึกใบขอจำหน่ายเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            $model->disposal_date = $model->disposal_date ? AppHelper::convertToThai($model->disposal_date) : '';
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items,
            'conditionOptions' => $this->getConditionOptions(),
            'departmentOptions' => $this->getDepartmentOptions(),
            'assetTypeOptions' => $this->getAssetTypeOptions(),
            'statusOptions' => AssetDisposal::statusList(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบใบขอจำหน่ายเรียบร้อย');
        return $this->redirect(['index']);
    }

    public function actionLookupAsset($code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $code = trim((string) $code);
        if ($code === '') {
            return ['success' => false, 'message' => 'กรุณาระบุรหัสทรัพย์สิน'];
        }

        $asset = Asset::find()
            ->where(['or', ['code' => $code], ['fsn_number' => $code]])
            ->with(['assetCondition', 'assetType'])
            ->one();

        if ($asset === null) {
            return ['success' => false, 'message' => 'ไม่พบทรัพย์สินตามรหัสที่ระบุ'];
        }

        return [
            'success' => true,
            'data' => [
                'asset_id' => $asset->id,
                'asset_code' => $this->resolveAssetCode($asset, $code),
                'asset_name' => $asset->asset_name ?: $asset->AssetitemName() ?: $asset->name ?: $code,
                'asset_condition' => $asset->asset_condition ?: '',
                'asset_condition_name' => $asset->assetCondition->name ?? '',
                'asset_type_id' => $asset->asset_type_id ?: '',
                'asset_type_name' => $asset->assetType->title ?? '',
            ],
        ];
    }

    public function actionLoadPendingAssets($department_id = null, $asset_type_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $departmentId = (int) $department_id;
        $assetTypeId = trim((string) $asset_type_id);

        $query = Asset::find()
            ->alias('a')
            ->with(['assetCondition', 'assetType'])
            ->where(['a.asset_status' => 'wait_dispose'])
            ->andWhere(['a.deleted_at' => null])
            ->orderBy(['a.code' => SORT_ASC, 'a.id' => SORT_ASC]);

        if ($departmentId > 0) {
            $query->andWhere(['a.department' => $departmentId]);
        }
        if ($assetTypeId !== '') {
            $query->andWhere(['a.asset_type_id' => $assetTypeId]);
        }

        $assets = $query->all();
        $rows = [];
        foreach ($assets as $asset) {
            $rows[] = [
                'asset_id' => $asset->id,
                'asset_code' => $this->resolveAssetCode($asset),
                'asset_name' => $asset->asset_name ?: $asset->AssetitemName() ?: $asset->name ?: '',
                'asset_condition' => $asset->asset_condition ?: '',
                'asset_condition_name' => $asset->assetCondition->name ?? '',
                'reason' => '',
            ];
        }

        $department = $departmentId > 0 ? Organization::findOne($departmentId) : null;
        $assetType = $assetTypeId !== '' ? AssetType::findOne(['name' => 'asset_type', 'code' => $assetTypeId]) : null;

        return [
            'success' => true,
            'department' => $department ? ['id' => $department->id, 'name' => $department->name] : null,
            'asset_type' => $assetType ? ['id' => $assetType->code, 'name' => $assetType->title] : null,
            'count' => count($rows),
            'items' => $rows,
        ];
    }

    protected function findModel($id)
    {
        $model = AssetDisposal::find()
            ->with([
                'departmentRef',
                'responsibleEmp',
                'disposalItems' => function ($query) {
                    $query->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
                },
            ])
            ->where(['id' => (int) $id])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบใบขอจำหน่าย');
        }
        return $model;
    }

    protected function getConditionOptions()
    {
        return (new Asset())->ListAssetCondition();
    }

    protected function getDepartmentOptions()
    {
        return ArrayHelper::map(
            Organization::find()->addOrderBy('root, lft')->all(),
            'id',
            'name'
        );
    }

    protected function getAssetTypeOptions()
    {
        return ArrayHelper::map(
            AssetType::find()->where(['name' => 'asset_type'])->orderBy(['title' => SORT_ASC])->all(),
            'code',
            'title'
        );
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return AssetDisposalItem[]
     */
    protected function buildItemModels(array $rows)
    {
        $items = [];
        foreach ($rows as $index => $row) {
            $code = trim((string) ($row['asset_code'] ?? ''));
            $name = trim((string) ($row['asset_name'] ?? ''));
            $reason = trim((string) ($row['reason'] ?? ''));
            $condition = trim((string) ($row['asset_condition'] ?? ''));
            $assetId = isset($row['asset_id']) && $row['asset_id'] !== '' ? (int) $row['asset_id'] : null;

            if ($code === '' && $name === '' && $reason === '' && $condition === '') {
                continue;
            }

            $item = new AssetDisposalItem();
            $item->load($row, '');
            $item->sort_order = (int) $index;

            $asset = null;
            if ($assetId !== null) {
                $asset = Asset::find()->where(['id' => $assetId])->with(['assetCondition', 'assetType'])->one();
            }
            if ($asset === null && $code !== '') {
                $asset = Asset::find()
                    ->where(['or', ['code' => $code], ['fsn_number' => $code]])
                    ->with(['assetCondition', 'assetType'])
                    ->one();
            }

            if ($asset !== null) {
                $item->asset_id = $asset->id;
                $item->asset_code = $this->resolveAssetCode($asset, $code);
                $item->asset_name = $asset->asset_name ?: $asset->AssetitemName() ?: $asset->name ?: ($name !== '' ? $name : $code);
                $item->asset_condition = $condition !== '' ? $condition : ($asset->asset_condition ?: '');
            } else {
                $item->asset_code = $code;
                $item->asset_name = $name !== '' ? $name : $code;
                $item->asset_condition = $condition;
            }

            if ($assetId !== null && $item->asset_id === null) {
                $item->asset_id = $assetId;
            }

            $item->reason = $reason;
            $items[] = $item;
        }

        return $items;
    }

    protected function resolveAssetCode(Asset $asset, $fallback = '')
    {
        $fallback = trim((string) $fallback);
        $code = trim((string) ($asset->code ?? ''));
        if ($code !== '') {
            return $code;
        }
        $fsn = trim((string) ($asset->fsn_number ?? ''));
        if ($fsn !== '') {
            return $fsn;
        }
        $assetItem = trim((string) ($asset->asset_item_id ?? ''));
        if ($assetItem !== '') {
            return $assetItem;
        }
        if ($fallback !== '') {
            return $fallback;
        }
        return (string) $asset->id;
    }

    protected function saveWithItems(AssetDisposal $model, array $items, bool $insert)
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $model->disposal_date = $model->disposal_date ? AppHelper::convertToGregorian($model->disposal_date) : null;
            if ($model->fiscal_year === null || $model->fiscal_year === '') {
                $model->fiscal_year = (int) AppHelper::YearBudget($model->disposal_date ?: null);
            }

            if ($insert) {
                $seqNo = (int) $db->createCommand(
                    'SELECT COALESCE(MAX([[seq_no]]), 0) + 1 FROM {{%asset_disposals}} WHERE [[fiscal_year]] = :year',
                    [':year' => $model->fiscal_year]
                )->queryScalar();
                $model->seq_no = $seqNo;
                $model->disposal_no = sprintf('จน.%03d/%d', $seqNo, $model->fiscal_year);
            }

            if (empty($items)) {
                $model->addError('disposal_no', 'กรุณาเพิ่มรายการพัสดุอย่างน้อย 1 รายการ');
                $transaction->rollBack();
                return false;
            }

            if (!$model->validate()) {
                $transaction->rollBack();
                return false;
            }

            if (!\yii\base\Model::validateMultiple($items)) {
                $messages = [];
                foreach ($items as $item) {
                    $firstErrors = $item->getFirstErrors();
                    if (!empty($firstErrors)) {
                        $messages[] = reset($firstErrors);
                    }
                    if (count($messages) >= 3) {
                        break;
                    }
                }
                $model->addError('disposal_no', 'ตรวจสอบรายการที่ขอจำหน่าย: ' . implode(' / ', $messages ?: ['ข้อมูลไม่ถูกต้อง']));
                $transaction->rollBack();
                return false;
            }

            if (!$model->save(false)) {
                $transaction->rollBack();
                return false;
            }

            if (!$insert) {
                AssetDisposalItem::deleteAll(['disposal_id' => $model->id]);
            }

            foreach ($items as $item) {
                $item->disposal_id = $model->id;
                $item->save(false);
            }

            if ($model->status === AssetDisposal::STATUS_DONE) {
                $this->markAssetsAsDisposed($items);
            }

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            $model->addError('disposal_no', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param AssetDisposalItem[] $items
     */
    protected function markAssetsAsDisposed(array $items): void
    {
        foreach ($items as $item) {
            $asset = null;
            if (!empty($item->asset_id)) {
                $asset = Asset::findOne((int) $item->asset_id);
            }
            if ($asset === null && trim((string) $item->asset_code) !== '') {
                $asset = Asset::find()
                    ->where(['or', ['code' => $item->asset_code], ['fsn_number' => $item->asset_code]])
                    ->one();
            }
            if ($asset === null) {
                continue;
            }

            $asset->asset_status = 'disposed';
            if ($asset->hasAttribute('lifecycle_status')) {
                $asset->lifecycle_status = Asset::LIFECYCLE_DISPOSED;
            }
            $asset->save(false, ['asset_status', 'lifecycle_status']);
        }
    }
}
