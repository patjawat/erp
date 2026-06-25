<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use app\components\AppHelper;
use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\am\services\AssetBulkCreateService;
use app\modules\hr\models\Organization;
use yii\helpers\ArrayHelper;

/**
 * Bulk asset receiving: step wizard to create multiple assets in one transaction.
 * Route: /am/asset-bulk/bulk-create (or /am/asset/bulk-create via URL rule).
 */
class AssetBulkController extends Controller
{
    const SESSION_KEY = 'am_bulk_create';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    /**
     * Wizard entry: GET shows current step; POST advances or saves.
     */
    public function actionBulkCreate()
    {
        $session = Yii::$app->session;
        $step = (int) (Yii::$app->request->get('step') ?: Yii::$app->request->post('step', 1));

        if (Yii::$app->request->isPost) {
            if ($step === 1) {
                $purchase = $this->capturePurchaseFromPost();
                $session->set(self::SESSION_KEY . '_purchase', $purchase);
                return $this->redirect(['bulk-create', 'step' => 2]);
            }
            if ($step === 2) {
                $template = $this->captureTemplateFromPost();
                $fsnOrItem = trim((string) ($template['fsn_number'] ?? '')) !== '' || trim((string) ($template['asset_item_id'] ?? '')) !== '';
                if (!$fsnOrItem) {
                    Yii::$app->session->setFlash('error', 'กรุณาเลือกรายการครุภัณฑ์จากปุ่มค้นหา (ทะเบียนรหัสทรัพย์สิน)');
                    return $this->redirect(['bulk-create', 'step' => 2]);
                }
                $session->set(self::SESSION_KEY . '_template', $template);
                return $this->redirect(['bulk-create', 'step' => 3]);
            }
            if ($step === 3) {
                $quantity = (int) Yii::$app->request->post('quantity', 0);
                $serialInput = trim((string) Yii::$app->request->post('serial_list', ''));
                $csvFile = UploadedFile::getInstanceByName('csv_file');
                $template = $session->get(self::SESSION_KEY . '_template', []);
                $service = new AssetBulkCreateService();
                $serialList = null;
                if ($csvFile && in_array(strtolower($csvFile->extension), ['csv'], true)) {
                    $path = Yii::getAlias('@runtime') . '/bulk_serial_' . time() . '.csv';
                    $csvFile->saveAs($path);
                    $serialList = $service->parseCsvFile($path);
                    @unlink($path);
                } elseif ($serialInput !== '') {
                    $serialList = $service->parseSerialList($serialInput);
                }
                if ($quantity < 1 || $quantity > 500) {
                    Yii::$app->session->setFlash('error', 'จำนวนต้องอยู่ระหว่าง 1–500');
                    return $this->redirect(['bulk-create', 'step' => 3]);
                }
                $purchaseForRows = $session->get(self::SESSION_KEY . '_purchase', []);
                $budgetYear = isset($purchaseForRows['budget_year']) && $purchaseForRows['budget_year']
                    ? (int) $purchaseForRows['budget_year']
                    : null;
                if ($budgetYear === null) {
                    Yii::$app->session->setFlash('error', 'กรุณาระบุปีงบประมาณในขั้นที่ 1 ก่อนสร้างหมายเลข');
                    return $this->redirect(['bulk-create', 'step' => 1]);
                }
                $rows = $service->buildPreviewRows($quantity, $template, $serialList, $budgetYear);
                $session->set(self::SESSION_KEY . '_rows', $rows);
                $session->set(self::SESSION_KEY . '_quantity', $quantity);
                return $this->redirect(['bulk-create', 'step' => 4]);
            }
            if ($step === 4) {
                $purchase = $session->get(self::SESSION_KEY . '_purchase', []);
                $template = $session->get(self::SESSION_KEY . '_template', []);
                $savedRows = $session->get(self::SESSION_KEY . '_rows', []);
                $rows = $this->mergeRowsFromPost($savedRows);
                if (empty($rows)) {
                    Yii::$app->session->setFlash('error', 'ไม่พบรายการที่จะบันทึก');
                    return $this->redirect(['bulk-create', 'step' => 4]);
                }
                $service = new AssetBulkCreateService();
                $result = $service->saveBatch($purchase, $template, $rows);
                $session->remove(self::SESSION_KEY . '_purchase');
                $session->remove(self::SESSION_KEY . '_template');
                $session->remove(self::SESSION_KEY . '_rows');
                $session->remove(self::SESSION_KEY . '_quantity');
                if ($result['success']) {
                    Yii::$app->session->setFlash('success', 'รับครุภัณฑ์จำนวน ' . $result['imported'] . ' รายการเรียบร้อย');
                    return $this->redirect(['/am/equip/index']);
                }
                Yii::$app->session->setFlash('error', implode(' ', array_slice($result['errors'], 0, 5)));
                $session->set(self::SESSION_KEY . '_purchase', $purchase);
                $session->set(self::SESSION_KEY . '_template', $template);
                $session->set(self::SESSION_KEY . '_rows', $rows);
                return $this->redirect(['bulk-create', 'step' => 4]);
            }
        }

        $purchase = $session->get(self::SESSION_KEY . '_purchase', []);
        $template = $session->get(self::SESSION_KEY . '_template', []);
        $rows = $session->get(self::SESSION_KEY . '_rows', []);
        $quantity = (int) $session->get(self::SESSION_KEY . '_quantity', 0);

        $lists = $this->getDropdownLists($purchase);
        return $this->render('bulk-create', [
            'step' => $step,
            'purchase' => $purchase,
            'template' => $template,
            'rows' => $rows,
            'quantity' => $quantity,
            'lists' => $lists,
        ]);
    }

    private function capturePurchaseFromPost(): array
    {
        $r = Yii::$app->request;
        return [
            'purchase_date' => AppHelper::DateToDb($r->post('purchase_date')) ?: date('Y-m-d'),
            'invoice_number' => trim((string) $r->post('invoice_number', '')),
            'supplier' => $r->post('supplier') ?: null,
            'budget_year' => $r->post('budget_year') ? (int) $r->post('budget_year') : null,
            'asset_type' => $r->post('asset_type') ?: null,
            'category' => $r->post('category') ?: null,
            'warehouse_location' => trim((string) $r->post('warehouse_location', '')),
            'department' => $r->post('department') !== '' && $r->post('department') !== null ? (int) $r->post('department') : null,
            'purchase' => $r->post('purchase') !== '' && $r->post('purchase') !== null ? (int) $r->post('purchase') : null,
        ];
    }

    private function captureTemplateFromPost(): array
    {
        $r = Yii::$app->request;
        return [
            'asset_item_id' => trim((string) ($r->post('asset_item_id') ?: $r->post('category'))),
            'fsn_number' => trim((string) $r->post('fsn_number', '')),
            'asset_name' => trim((string) $r->post('asset_name', '')),
            'brand' => trim((string) $r->post('brand', '')),
            'model' => trim((string) $r->post('model', '')),
            'specification' => trim((string) $r->post('specification', '')),
            'purchase_price' => $r->post('purchase_price') !== '' ? (float) $r->post('purchase_price') : 0,
            'useful_life' => $r->post('useful_life') !== '' ? (int) $r->post('useful_life') : null,
            'residual_value' => $r->post('residual_value') !== '' ? (float) $r->post('residual_value') : null,
            'depreciation_method' => $r->post('depreciation_method', 'straight_line'),
        ];
    }

    private function mergeRowsFromPost(array $savedRows): array
    {
        $codes = Yii::$app->request->post('row_code', []);
        $serials = Yii::$app->request->post('row_serial', []);
        $names = Yii::$app->request->post('row_name', []);
        $remarks = Yii::$app->request->post('row_remark', []);
        $rows = [];
        foreach ($savedRows as $i => $row) {
            $code = isset($codes[$i]) ? trim((string) $codes[$i]) : ($row['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $rows[] = [
                'code' => $code,
                'serial_number' => isset($serials[$i]) ? trim((string) $serials[$i]) : ($row['serial_number'] ?? ''),
                'asset_name' => isset($names[$i]) ? trim((string) $names[$i]) : ($row['asset_name'] ?? ''),
                'remark' => isset($remarks[$i]) ? trim((string) $remarks[$i]) : ($row['remark'] ?? ''),
            ];
        }
        return $rows;
    }

    /**
     * ดึงรายการสำหรับ dropdown เหมือน /am/equip/create
     * - asset_types: ประเภทครุภัณฑ์ จาก Categorise name=asset_type, group_id=EQUIP
     * - categories: รายการครุภัณฑ์ (สำหรับ step 2) กรองตาม asset_type ที่เลือกใน step 1
     */
    private function getDropdownLists(array $purchase = []): array
    {
        $year = (int) AppHelper::YearBudget();
        $years = array_combine(range($year, $year - 5), range($year, $year - 5));
        $assetTypes = ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'EQUIP'])->orderBy('title')->all(),
            'code',
            'title'
        );
        $assetType = $purchase['asset_type'] ?? null;
        $categories = [];
        if ($assetType !== null && $assetType !== '') {
            $categories = ArrayHelper::map(
                Categorise::find()->where(['name' => 'asset_item_id', 'category_id' => $assetType])->orderBy('title')->all(),
                'code',
                'title'
            );
        }
        if (empty($categories) && ($assetType === null || $assetType === '')) {
            $categories = ArrayHelper::map(
                Categorise::find()->where(['name' => 'asset_item_id', 'group_id' => 3])->orderBy('title')->all(),
                'code',
                'title'
            );
        }
        $vendors = ArrayHelper::map(Categorise::find()->where(['name' => 'vendor'])->orderBy('title')->all(), 'code', 'title');
        $purchases = ArrayHelper::map(Categorise::find()->where(['name' => 'purchase'])->orderBy('title')->all(), 'code', 'title');
        $departments = Organization::find()->orderBy('lft')->all();
        $deptList = [];
        foreach ($departments as $d) {
            $deptList[$d->id] = str_repeat('— ', (int) $d->lvl) . $d->name;
        }
        return [
            'years' => $years,
            'asset_types' => $assetTypes,
            'categories' => $categories,
            'vendors' => $vendors,
            'purchases' => $purchases,
            'departments' => $deptList,
        ];
    }
}
