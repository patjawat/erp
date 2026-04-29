<?php

namespace app\modules\am\controllers;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetAudit;
use app\modules\am\models\AssetAuditItem;
use app\modules\am\models\AssetAuditSearch;
use app\modules\am\models\AssetCondition;
use app\modules\hr\models\Organization;

/**
 * Annual asset inventory.
 */
class AuditController extends Controller
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
        $searchModel = new AssetAuditSearch();
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
        $model = new AssetAudit();
        $model->fiscal_year = (int) AppHelper::YearBudget();
        $model->status = AssetAudit::STATUS_DRAFT;
        $model->audit_date = AppHelper::convertToThai(date('Y-m-d'));

        $items = [new AssetAuditItem()];

        if ($model->load(Yii::$app->request->post())) {
            $postItems = Yii::$app->request->post('AssetAuditItem', []);
            $items = $this->buildItemModels($postItems);

            if ($this->saveWithItems($model, $items, true)) {
                Yii::$app->session->setFlash('success', 'บันทึกใบตรวจนับเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            $model->audit_date = $model->audit_date ? AppHelper::convertToThai($model->audit_date) : '';
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
            'conditionOptions' => $this->getConditionOptions(),
            'departmentOptions' => $this->getDepartmentOptions(),
            'statusOptions' => AssetAudit::statusList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->audit_date = $model->audit_date ? AppHelper::convertToThai($model->audit_date) : '';

        $items = $model->auditItems ?: [new AssetAuditItem()];

        if ($model->load(Yii::$app->request->post())) {
            $postItems = Yii::$app->request->post('AssetAuditItem', []);
            $items = $this->buildItemModels($postItems);

            if ($this->saveWithItems($model, $items, false)) {
                Yii::$app->session->setFlash('success', 'บันทึกใบตรวจนับเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            $model->audit_date = $model->audit_date ? AppHelper::convertToThai($model->audit_date) : '';
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items,
            'conditionOptions' => $this->getConditionOptions(),
            'departmentOptions' => $this->getDepartmentOptions(),
            'statusOptions' => AssetAudit::statusList(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบใบตรวจนับเรียบร้อย');
        return $this->redirect(['index']);
    }

    public function actionLookupAsset($code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $code = trim((string) $code);
        if ($code === '') {
            return ['success' => false, 'message' => 'กรุณาระบุรหัสครุภัณฑ์'];
        }

        $asset = Asset::find()->where(['code' => $code])->with(['assetCondition'])->one();
        if ($asset === null) {
            return ['success' => false, 'message' => 'ไม่พบครุภัณฑ์ตามรหัสที่ระบุ'];
        }

        return [
            'success' => true,
            'data' => [
                'asset_id' => $asset->id,
                'asset_code' => $asset->code ?: $code,
                'asset_name' => $asset->asset_name ?: $asset->AssetitemName() ?: $asset->name ?: $code,
                'asset_condition' => $asset->asset_condition,
                'asset_condition_name' => $asset->assetCondition->name ?? '',
            ],
        ];
    }

    public function actionAssetsByDepartment($department_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $departmentId = (int) $department_id;
        if ($departmentId <= 0) {
            return ['success' => false, 'message' => 'กรุณาเลือกหน่วยงานก่อน'];
        }

        $department = Organization::findOne($departmentId);
        if ($department === null) {
            return ['success' => false, 'message' => 'ไม่พบหน่วยงานที่เลือก'];
        }

        $assets = Asset::find()
            ->where(['department' => $departmentId])
            ->andWhere(['or', ['deleted_at' => null], ['deleted_at' => '']])
            ->with(['assetCondition'])
            ->orderBy(['code' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $rows = [];
        foreach ($assets as $asset) {
            $rows[] = [
                'asset_id' => $asset->id,
                'asset_code' => $asset->code ?: '',
                'asset_name' => $asset->asset_name ?: $asset->AssetitemName() ?: $asset->name ?: '',
                'asset_condition' => $asset->asset_condition ?: '',
                'asset_condition_name' => $asset->assetCondition->name ?? '',
                'note' => '',
            ];
        }

        return [
            'success' => true,
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
            ],
            'count' => count($rows),
            'items' => $rows,
        ];
    }

    protected function findModel($id)
    {
        $model = AssetAudit::find()->with(['departmentRef', 'auditItems' => function ($query) {
            $query->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        }])->where(['id' => (int) $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบใบตรวจนับ');
        }
        return $model;
    }

    protected function getConditionOptions()
    {
        return ArrayHelper::map(
            AssetCondition::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
            'id',
            'name'
        );
    }

    protected function getDepartmentOptions()
    {
        return ArrayHelper::map(
            Organization::find()->addOrderBy('root, lft')->all(),
            'id',
            'name'
        );
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return AssetAuditItem[]
     */
    protected function buildItemModels(array $rows)
    {
        $items = [];
        foreach ($rows as $index => $row) {
            $code = trim((string) ($row['asset_code'] ?? ''));
            $name = trim((string) ($row['asset_name'] ?? ''));
            $note = trim((string) ($row['note'] ?? ''));
            $condition = trim((string) ($row['asset_condition'] ?? ''));
            $assetId = isset($row['asset_id']) && $row['asset_id'] !== '' ? (int) $row['asset_id'] : null;

            if ($code === '' && $name === '' && $note === '' && $condition === '') {
                continue;
            }

            $item = new AssetAuditItem();
            $item->load($row, '');
            $item->sort_order = (int) $index;

            if ($code !== '') {
                $asset = Asset::find()->where(['code' => $code])->with(['assetCondition'])->one();
                if ($asset !== null) {
                    $item->asset_id = $asset->id;
                    $item->asset_code = $asset->code ?: $code;
                    $item->asset_name = $asset->asset_name ?: $asset->AssetitemName() ?: $asset->name ?: $code;
                    $item->asset_condition = $condition !== '' ? $condition : $asset->asset_condition;
                } else {
                    $item->asset_code = $code;
                    $item->asset_name = $name !== '' ? $name : $code;
                    $item->asset_condition = $condition;
                }
            } else {
                $item->asset_code = $code;
                $item->asset_name = $name;
                $item->asset_condition = $condition;
            }

            if ($assetId !== null && $item->asset_id === null) {
                $item->asset_id = $assetId;
            }

            $items[] = $item;
        }

        return $items;
    }

    protected function saveWithItems(AssetAudit $model, array $items, bool $insert)
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $model->audit_date = $model->audit_date ? AppHelper::convertToGregorian($model->audit_date) : null;
            if ($model->fiscal_year === null || $model->fiscal_year === '') {
                $model->fiscal_year = (int) AppHelper::YearBudget($model->audit_date ?: null);
            }

            if ($insert) {
                $seqNo = (int) $db->createCommand(
                    'SELECT COALESCE(MAX([[seq_no]]), 0) + 1 FROM {{%asset_audits}} WHERE [[fiscal_year]] = :year',
                    [':year' => $model->fiscal_year]
                )->queryScalar();
                $model->seq_no = $seqNo;
                $model->audit_no = sprintf('ตน.%03d/%d', $seqNo, $model->fiscal_year);
            }

            if (empty($items)) {
                $model->addError('audit_no', 'กรุณาเพิ่มรายการตรวจนับอย่างน้อย 1 รายการ');
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
                $model->addError('audit_no', 'ตรวจสอบรายการที่ตรวจนับ: ' . implode(' / ', $messages ?: ['ข้อมูลไม่ถูกต้อง']));
                $transaction->rollBack();
                return false;
            }

            if (!$model->save(false)) {
                $transaction->rollBack();
                return false;
            }

            if (!$insert) {
                AssetAuditItem::deleteAll(['audit_id' => $model->id]);
            }

            foreach ($items as $item) {
                $item->audit_id = $model->id;
                $item->save(false);
            }

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            $model->addError('audit_no', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }
}
