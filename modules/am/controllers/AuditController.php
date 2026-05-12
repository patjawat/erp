<?php

namespace app\modules\am\controllers;

use Yii;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
                    'bulk-delete' => ['POST'],
                    'sync-audit' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new AssetAuditSearch();
        $queryParams = Yii::$app->request->queryParams;
        if (empty($queryParams['AssetAuditSearch'])) {
            $searchModel->thai_year = (int) AppHelper::YearBudget();
        }
        $dataProvider = $searchModel->search($queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDashboard()
    {
        $searchModel = new AssetAuditSearch();
        $queryParams = Yii::$app->request->queryParams;
        if (empty($queryParams['AssetAuditSearch'])) {
            $searchModel->thai_year = (int) AppHelper::YearBudget();
        }
        $searchModel->load(Yii::$app->request->queryParams);

        $kpiFiscalYear = null;
        if ($searchModel->thai_year !== null && $searchModel->thai_year !== '') {
            $kpiFiscalYear = (int) $searchModel->thai_year;
        }

        $kpiData = $this->buildAuditKpiData($kpiFiscalYear);
        $noDepartmentCount = $this->countAssetsWithoutDepartment();

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'overallKpi' => $kpiData['overall'],
            'typeTotals' => $kpiData['typeTotals'],
            'kpiFiscalYear' => $kpiFiscalYear,
            'noDepartmentCount' => $noDepartmentCount,
        ]);
    }

    /**
     * @return array{overall: array<string, int|float>, typeTotals: array<int, array<string, int|float|string>>}
     */
    private function buildAuditKpiData(?int $fiscalYear): array
    {
        $overall = [
            'total' => 0,
            'checked' => 0,
            'remaining' => 0,
            'percent' => 0.0,
        ];
        $typeTotals = [];

        $allAssets = Asset::find()
            ->alias('a')
            ->with(['assetType'])
            ->where(['a.deleted_at' => null])
            ->orderBy(['a.asset_type_id' => SORT_ASC, 'a.code' => SORT_ASC, 'a.id' => SORT_ASC])
            ->all();

        $checkedAssetIds = $this->getCheckedAssetIdsByFiscalYear($fiscalYear);
        $checkedAssetIdMap = array_fill_keys(array_map('intval', $checkedAssetIds), true);

        foreach ($allAssets as $asset) {
            $typeLabel = $this->resolveAssetTypeLabel($asset);
            if (!isset($typeTotals[$typeLabel])) {
                $typeTotals[$typeLabel] = [
                    'type' => $typeLabel,
                    'total' => 0,
                    'checked' => 0,
                    'remaining' => 0,
                ];
            }

            $overall['total']++;
            $typeTotals[$typeLabel]['total']++;

            if (isset($checkedAssetIdMap[(int) $asset->id])) {
                $overall['checked']++;
                $typeTotals[$typeLabel]['checked']++;
            } else {
                $overall['remaining']++;
                $typeTotals[$typeLabel]['remaining']++;
            }
        }

        $overall['percent'] = $overall['total'] > 0 ? round(($overall['checked'] / $overall['total']) * 100, 1) : 0.0;
        foreach ($typeTotals as &$row) {
            $row['percent'] = $row['total'] > 0 ? round(($row['checked'] / $row['total']) * 100, 1) : 0.0;
        }
        unset($row);
        uasort($typeTotals, static function (array $a, array $b): int {
            return $b['total'] <=> $a['total'] ?: strcmp($a['type'], $b['type']);
        });

        return [
            'overall' => $overall,
            'typeTotals' => array_values($typeTotals),
        ];
    }

    /**
     * @return int[]
     */
    private function getCheckedAssetIdsByFiscalYear(?int $fiscalYear): array
    {
        $query = AssetAuditItem::find()
            ->alias('ai')
            ->select(['ai.asset_id'])
            ->distinct()
            ->innerJoin(['aa' => AssetAudit::tableName()], 'aa.id = ai.audit_id')
            ->where(['not', ['ai.asset_id' => null]]);

        $yearCandidates = AssetAudit::fiscalYearCandidates($fiscalYear);
        if (!empty($yearCandidates)) {
            $query->andWhere(['aa.thai_year' => $yearCandidates]);
        }

        $ids = $query->column();
        return array_values(array_filter(array_map('intval', $ids)));
    }

    private function resolveAssetTypeLabel(Asset $asset): string
    {
        $typeLabel = trim((string) ($asset->assetType->title ?? $asset->AssetTypeName() ?? $asset->asset_kind ?? ''));
        return $typeLabel !== '' ? $typeLabel : 'ไม่ระบุประเภท';
    }

    private function countAssetsWithoutDepartment(): int
    {
        return (int) Asset::find()
            ->alias('a')
            ->where(['a.deleted_at' => null])
            ->andWhere([
                'or',
                ['a.department' => null],
                ['a.department' => 0],
                ['a.department' => ''],
            ])
            ->count('DISTINCT a.id');
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
        $model->thai_year = (int) AppHelper::YearBudget();
        $model->status = AssetAudit::STATUS_DRAFT;
        $model->audit_date = AppHelper::convertToThai(date('Y-m-d'));

        $currentEmployee = Yii::$app->user->identity->employee ?? null;
        if ($currentEmployee !== null) {
            if (empty($model->department) && !empty($currentEmployee->department)) {
                $model->department = (int) $currentEmployee->department;
            }
            if (empty($model->emp_id) && !empty($currentEmployee->id)) {
                $model->emp_id = (string) $currentEmployee->id;
            }
        }

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

    public function actionBulkDelete()
    {
        $ids = Yii::$app->request->post('ids', []);
        $ids = array_values(array_filter(array_map('intval', (array) $ids)));

        if (empty($ids)) {
            Yii::$app->session->setFlash('warning', 'กรุณาเลือกรายการที่ต้องการลบ');
            return $this->redirect(['index']);
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $audits = AssetAudit::find()->where(['id' => $ids])->all();
            if (empty($audits)) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', 'ไม่พบรายการที่เลือก');
                return $this->redirect(['index']);
            }

            foreach ($audits as $audit) {
                AssetAuditItem::deleteAll(['audit_id' => $audit->id]);
                $audit->delete();
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบใบตรวจนับที่เลือกเรียบร้อย');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'ลบไม่สำเร็จ: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    public function actionSyncAudit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = (int) Yii::$app->request->post('id', 0);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ไม่พบใบตรวจนับที่เลือก'];
        }

        $model = $this->findModel($id);
        if ($model->status !== AssetAudit::STATUS_ACTIVE) {
            return [
                'success' => false,
                'message' => 'บันทึกผลได้เฉพาะใบตรวจนับที่สถานะเสร็จสิ้น',
                'status_label' => $model->getStatusLabel(),
            ];
        }

        if (empty($model->department)) {
            return [
                'success' => false,
                'message' => 'ใบตรวจนับนี้ยังไม่ได้ระบุหน่วยงาน',
                'status_label' => $model->getStatusLabel(),
            ];
        }

        $updated = 0;
        $assetIds = [];
        foreach ($model->auditItems as $item) {
            if (empty($item->asset_id)) {
                continue;
            }

            $asset = Asset::findOne((int) $item->asset_id);
            if ($asset === null) {
                continue;
            }

            $updateFields = [];

            if (!empty($item->asset_condition)) {
                $asset->asset_condition = $item->asset_condition;
                $updateFields[] = 'asset_condition';
            }

            $asset->department = (int) $model->department;
            $updateFields[] = 'department';

            if (!empty($updateFields)) {
                $asset->save(false, array_unique($updateFields));
                $updated++;
                $assetIds[] = (int) $asset->id;
            }
        }

        return [
            'success' => true,
            'message' => 'บันทึกผลเข้าทะเบียนทรัพย์สินเรียบร้อย',
            'status_label' => $model->getStatusLabel(),
            'updated' => $updated,
            'asset_ids' => $assetIds,
        ];
    }

    public function actionExportExcel($id)
    {
        $model = $this->findModel($id);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ตรวจนับ');

        $departmentName = $model->departmentRef->name ?? '-';
        $auditorName = $model->auditorLabel;
        $auditDate = $model->audit_date ? AppHelper::convertToThai($model->audit_date) : '-';
        $statusLabel = $model->getStatusLabel();
        $itemCount = count($model->auditItems);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'ใบตรวจนับครุภัณฑ์ประจำปี');
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'เลขที่ ' . $model->audit_no);

        $sheet->setCellValue('A4', 'ปีงบประมาณ');
        $sheet->setCellValue('B4', $model->thai_year);
        $sheet->setCellValue('C4', 'หน่วยงาน');
        $sheet->setCellValue('D4', $departmentName);

        $sheet->setCellValue('A5', 'วันที่ตรวจนับ');
        $sheet->setCellValue('B5', $auditDate);
        $sheet->setCellValue('C5', 'ผู้ตรวจนับ');
        $sheet->setCellValue('D5', $auditorName);

        $sheet->setCellValue('A6', 'สถานะ');
        $sheet->setCellValue('B6', $statusLabel);
        $sheet->setCellValue('C6', 'จำนวนรายการ');
        $sheet->setCellValue('D6', $itemCount);

        $sheet->setCellValue('A8', 'หมายเหตุรวม');
        $sheet->mergeCells('B8:D8');
        $sheet->setCellValue('B8', $model->summary_note ?: '-');

        $startRow = 10;
        $sheet->setCellValue('A' . $startRow, 'รหัส');
        $sheet->setCellValue('B' . $startRow, 'ชื่อครุภัณฑ์');
        $sheet->setCellValue('C' . $startRow, 'สภาพ');
        $sheet->setCellValue('D' . $startRow, 'หมายเหตุ');

        $headerRange = 'A' . $startRow . ':D' . $startRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row = $startRow + 1;
        foreach ($model->auditItems as $item) {
            $sheet->setCellValue('A' . $row, $item->asset_code);
            $sheet->setCellValue('B' . $row, $item->asset_name);
            $sheet->setCellValue('C' . $row, $item->condition->name ?? $item->asset_condition ?? '-');
            $sheet->setCellValue('D' . $row, $item->note ?? '');
            $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        foreach (['A' => 18, 'B' => 36, 'C' => 16, 'D' => 36] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getStyle('A1:D2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4:D8')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('B8:D8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:D' . max($row - 1, 10))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $fileName = 'asset-audit-' . str_replace('/', '-', $model->audit_no) . '.xlsx';
        $filePath = Yii::getAlias('@webroot') . '/downloads/' . $fileName;
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        return Yii::$app->response->sendFile($filePath, $fileName);
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
                'asset_code' => $this->resolveAssetCode($asset, $code),
                'asset_name' => trim((string) ($asset->asset_name ?? '')) ?: trim((string) ($asset->AssetitemName() ?? '')) ?: $code,
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
            ->andWhere(['or', ['deleted_at' => null]])
            ->with(['assetCondition'])
            ->orderBy(['code' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $rows = [];
        foreach ($assets as $asset) {
            $assetName = trim((string) ($asset->asset_name ?? ''));
            if ($assetName === '') {
                $assetName = trim((string) ($asset->AssetitemName() ?? ''));
            }
            $rows[] = [
                'asset_id' => $asset->id,
                'asset_code' => $this->resolveAssetCode($asset),
                'asset_name' => $assetName,
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
        $model = AssetAudit::find()->with([
            'departmentRef',
            'auditorEmp',
            'auditItems.asset.assetType',
        ])->where(['id' => (int) $id])->one();
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
                    $item->asset_code = $this->resolveAssetCode($asset, $code);
                    $item->asset_name = trim((string) ($asset->asset_name ?? '')) ?: trim((string) ($asset->AssetitemName() ?? '')) ?: $code;
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

    protected function saveWithItems(AssetAudit $model, array $items, bool $insert)
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $model->audit_date = $model->audit_date ? AppHelper::convertToGregorian($model->audit_date) : null;
            if ($model->thai_year === null || $model->thai_year === '') {
                $model->thai_year = (int) AppHelper::YearBudget($model->audit_date ?: null);
            }

            if ($insert) {
                $seqNo = (int) $db->createCommand(
                    'SELECT COALESCE(MAX([[seq_no]]), 0) + 1 FROM {{%asset_audits}} WHERE [[thai_year]] = :year',
                    [':year' => $model->thai_year]
                )->queryScalar();
                $model->seq_no = $seqNo;
                $model->audit_no = sprintf('ตน.%03d/%d', $seqNo, $model->thai_year);
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
