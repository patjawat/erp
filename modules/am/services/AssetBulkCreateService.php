<?php

namespace app\modules\am\services;

use Yii;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use yii\helpers\ArrayHelper;

/**
 * Production-grade bulk asset receiving: generate preview rows and save multiple assets in a transaction.
 * Reuses Asset model and AssetNumberGenerator; supports 100+ assets per run.
 */
class AssetBulkCreateService
{
    public const BATCH_SIZE = 100;

    /**
     * Build preview rows: generate asset numbers and optionally merge serial list or CSV data.
     *
     * @param int $quantity
     * @param array $template ['asset_item_id' => string, 'brand' => ?, 'model' => ?, 'specification' => ?, 'purchase_price' => float, 'useful_life' => int, 'residual_value' => float]
     * @param array|null $serialList List of serial numbers (or [serial, name, remark] per row). If shorter than quantity, rest get empty serial.
     * @param int|null $yearBe Buddhist year that asset numbers should be issued under (defaults to current budget year).
     * @return array [['code' => string, 'serial_number' => string, 'asset_name' => string, 'remark' => string], ...]
     */
    public function buildPreviewRows(int $quantity, array $template, ?array $serialList = null, ?int $yearBe = null): array
    {
        $categoryId = $template['fsn_number'] ?? $template['asset_item_id'] ?? '';
        if ($categoryId === '') {
            return [];
        }
        $rows = [];
        $baseName = $template['asset_name'] ?? '';
        for ($i = 0; $i < $quantity; $i++) {
            $code = AssetNumberGenerator::generate($categoryId, $yearBe);
            $serial = '';
            $name = $baseName;
            $remark = '';
            if (is_array($serialList) && isset($serialList[$i])) {
                $cell = $serialList[$i];
                if (is_string($cell)) {
                    $serial = trim($cell);
                } else {
                    $serial = trim((string) ($cell['serial_number'] ?? $cell[0] ?? ''));
                    $name = trim((string) ($cell['asset_name'] ?? $cell[1] ?? $baseName));
                    $remark = trim((string) ($cell['remark'] ?? $cell[2] ?? ''));
                }
            }
            $rows[] = [
                'code' => $code,
                'serial_number' => $serial,
                'asset_name' => $name ?: $baseName,
                'remark' => $remark,
            ];
        }
        return $rows;
    }

    /**
     * Parse pasted serial list (one per line) or CSV lines into array of [serial_number, asset_name?, remark?].
     *
     * @param string $input Pasted text: one serial per line, or CSV with columns serial_number, asset_name, remark
     * @return array
     */
    public function parseSerialList(string $input): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($input), -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (strpos($line, ',') !== false) {
                $parts = str_getcsv($line);
                $out[] = [
                    'serial_number' => isset($parts[0]) ? trim($parts[0]) : '',
                    'asset_name' => isset($parts[1]) ? trim($parts[1]) : '',
                    'remark' => isset($parts[2]) ? trim($parts[2]) : '',
                ];
            } else {
                $out[] = ['serial_number' => $line, 'asset_name' => '', 'remark' => ''];
            }
        }
        return $out;
    }

    /**
     * Parse CSV file content (header: serial_number, asset_name, remark) into rows.
     *
     * @param string $filePath
     * @return array [['serial_number' => ..., 'asset_name' => ..., 'remark' => ...], ...]
     */
    public function parseCsvFile(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }
        $header = fgetcsv($handle, 0, ',');
        $header = array_map('trim', $header ?: []);
        $idxSerial = array_search('serial_number', $header);
        if ($idxSerial === false) {
            $idxSerial = 0;
        }
        $idxName = array_search('asset_name', $header);
        if ($idxName === false) {
            $idxName = 1;
        }
        $idxRemark = array_search('remark', $header);
        if ($idxRemark === false) {
            $idxRemark = 2;
        }
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = [
                'serial_number' => isset($data[$idxSerial]) ? trim((string) $data[$idxSerial]) : '',
                'asset_name' => isset($data[$idxName]) ? trim((string) $data[$idxName]) : '',
                'remark' => isset($data[$idxRemark]) ? trim((string) $data[$idxRemark]) : '',
            ];
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Save bulk assets in a single transaction. Each element of $rows must have 'code', 'serial_number', 'asset_name', 'remark'.
     * Purchase and template arrays provide common fields.
     *
     * @param array $purchase ['purchase_date' => Y-m-d, 'invoice_number' => ?, 'supplier' => vendor_id/code, 'budget_year' => ?, 'category' => asset_item_id?, 'warehouse_location' => ?, 'department' => id]
     * @param array $template ['asset_item_id' => ?, 'brand' => ?, 'model' => ?, 'specification' => ?, 'purchase_price' => float, 'useful_life' => int, 'residual_value' => float, 'asset_name' => ?]
     * @param array $rows [['code' => string, 'serial_number' => string, 'asset_name' => string, 'remark' => string], ...]
     * @return array ['success' => bool, 'imported' => int, 'errors' => string[]]
     */
    public function saveBatch(array $purchase, array $template, array $rows): array
    {
        $imported = 0;
        $errors = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $receiveDate = $purchase['purchase_date'] ?? date('Y-m-d');
            $onYear = (int) ($purchase['budget_year'] ?? substr(AppHelper::YearBudget($receiveDate), 0, 4));
            $department = isset($purchase['department']) ? (int) $purchase['department'] : null;
            $vendorId = $purchase['supplier'] ?? null;
            $location = $purchase['warehouse_location'] ?? '';

            foreach ($rows as $index => $row) {
                $code = $row['code'] ?? '';
                if ($code === '') {
                    $errors[] = "แถว " . ($index + 1) . ": ไม่มีหมายเลขครุภัณฑ์";
                    continue;
                }
                $exists = Asset::find()->where(['code' => $code])->exists();
                if ($exists) {
                    $errors[] = "แถว " . ($index + 1) . ": หมายเลขซ้ำ ({$code})";
                    continue;
                }

                $model = new Asset();
                $model->asset_group_id = 3;
                $model->asset_status = 'active'; // FK varchar → asset_status.id (เดิม =1 ชน constraint)
                $model->ref = substr(Yii::$app->security->generateRandomString(), 10);
                $model->code = $code;
                $model->asset_item_id = $template['asset_item_id'] ?? null;
                $model->fsn_number = $template['asset_item_id'] ?? $template['fsn_number'] ?? null;
                $model->receive_date = $receiveDate;
                $model->on_year = $onYear;
                $model->department = $department;
                $model->price = (float) ($template['purchase_price'] ?? 0);
                $model->useful_life = isset($template['useful_life']) ? (int) $template['useful_life'] : null;
                $model->residual_value = isset($template['residual_value']) ? (float) $template['residual_value'] : null;
                $model->depreciation_method = $template['depreciation_method'] ?? 'straight_line';
                $model->purchase = $purchase['purchase'] ?? null;

                $dataJson = [
                    'serial_number' => $row['serial_number'] ?? '',
                    'asset_name' => $row['asset_name'] ?? ($template['asset_name'] ?? ''),
                    'location' => $location,
                    'vendor_id' => $vendorId,
                    'invoice_number' => $purchase['invoice_number'] ?? '',
                    'budget_year_text' => $purchase['budget_year'] ?? '',
                    'brand' => $template['brand'] ?? '',
                    'asset_model' => $template['model'] ?? '',
                    'specification' => $template['specification'] ?? '',
                ];
                if (!empty($row['remark'])) {
                    $dataJson['remark'] = $row['remark'];
                }
                $model->data_json = $dataJson;

                if (!$model->save(false)) {
                    $errors[] = "แถว " . ($index + 1) . ": " . implode(', ', $model->getFirstErrors());
                    continue;
                }
                $imported++;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $errors[] = $e->getMessage();
        }

        return [
            'success' => empty($errors),
            'imported' => $imported,
            'errors' => $errors,
        ];
    }
}
