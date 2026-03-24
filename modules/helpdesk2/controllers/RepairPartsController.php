<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Response;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\inventoryV2\models\Warehouse;

class RepairPartsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate($helpdesk_id)
    {
        $model = new HelpdeskDetail([
            'helpdesk_id' => $helpdesk_id
        ]);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $rowsJson = (string) $this->request->post('part_rows_json', '');
            if ($rowsJson === '') {
                return ['status' => 'error', 'message' => 'ไม่พบรายการอะไหล่'];
            }
            $rows = json_decode($rowsJson, true);
            if (!is_array($rows)) {
                return ['status' => 'error', 'message' => 'รูปแบบข้อมูลไม่ถูกต้อง'];
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                HelpdeskDetail::deleteAll([
                    'helpdesk_id' => (int) $helpdesk_id,
                    'name' => 'part_record',
                ]);

                foreach ($rows as $row) {
                    $itemCode = trim((string) ($row['item_code'] ?? ''));
                    $itemName = trim((string) ($row['item_name'] ?? ''));
                    $qty = (float) ($row['qty'] ?? 0);
                    $warehouseId = (int) ($row['warehouse_id'] ?? 0);
                    if ($itemCode === '' || $itemName === '' || $qty <= 0) {
                        continue;
                    }

                    $unit = trim((string) ($row['unit'] ?? ''));
                    $balance = (float) ($row['balance_qty'] ?? 0);

                    $part = new HelpdeskDetail();
                    $part->helpdesk_id = (int) $helpdesk_id;
                    $part->name = 'part_record';
                    $part->status = 'เบิกอะไหล่';
                    $part->code = $itemCode;
                    $part->title = $itemName;
                    $part->data_json = [
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'qty' => $qty,
                        'unit' => $unit,
                        'balance_qty' => $balance,
                        'warehouse_id' => $warehouseId,
                    ];
                    if (!$part->save()) {
                        throw new \RuntimeException('บันทึกรายการอะไหล่ไม่สำเร็จ');
                    }
                }

                try {
                    $sumQty = 0.0;
                    foreach ($rows as $r) {
                        $sumQty += (float) ($r['qty'] ?? 0);
                    }
                    $log = new HelpdeskDetail();
                    $log->helpdesk_id = (int) $helpdesk_id;
                    $log->name = 'service_record';
                    $log->status = 'บันทึกการเบิกอะไหล่';
                    $log->title = 'เบิกอะไหล่ ' . count($rows) . ' รายการ';
                    $log->data_json = [
                        'part_count' => count($rows),
                        'part_total_qty' => $sumQty,
                    ];
                    $log->save(false);
                } catch (\Throwable $e) {
                    // ไม่ให้กระทบการบันทึกหลัก
                }

                $tx->commit();
                return ['status' => 'success'];
            } catch (\Throwable $e) {
                $tx->rollBack();
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        $partRows = HelpdeskDetail::find()
            ->where(['helpdesk_id' => (int) $helpdesk_id, 'name' => 'part_record'])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $subWarehouses = Warehouse::findSubWarehousesForUser();

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'เบิกอะไหล่งานซ่อม'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'partRows' => $partRows,
                    'subWarehouses' => $subWarehouses,
                ]),
            ];
        }

        return $this->render('create', [
            'model' => $model,
            'partRows' => $partRows,
            'subWarehouses' => $subWarehouses,
        ]);
    }

    public function actionInventoryLookup($q = '', $warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string) $q);
        $warehouseId = (int) $warehouse_id;

        $allowedSubWarehouses = Warehouse::findSubWarehousesForUser();
        $allowedWarehouseIds = array_map(static fn($w) => (int) $w->id, $allowedSubWarehouses);
        if ($warehouseId <= 0 || !in_array($warehouseId, $allowedWarehouseIds, true)) {
            return ['results' => []];
        }

        $where = '';
        $params = [];
        if ($q !== '') {
            $where = "WHERE si.is_active = 1 AND sb.warehouse_id = :warehouse_id AND (si.item_code LIKE :q OR si.item_name LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        } else {
            $where = "WHERE si.is_active = 1 AND sb.warehouse_id = :warehouse_id";
        }
        $params[':warehouse_id'] = $warehouseId;

        $sql = "
            SELECT 
                si.item_code,
                si.item_name,
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(si.data_json, '$.unit_name')), '') AS unit_name,
                COALESCE(SUM(sb.balance_qty), 0) AS balance_qty
            FROM stock_item si
            INNER JOIN stock_balance sb ON sb.item_code = si.item_code
            {$where}
            GROUP BY si.item_code, si.item_name, si.data_json
            ORDER BY si.item_name ASC
            LIMIT 30
        ";
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'item_code' => (string) ($r['item_code'] ?? ''),
                'item_name' => (string) ($r['item_name'] ?? ''),
                'unit_name' => (string) ($r['unit_name'] ?? ''),
                'balance_qty' => (float) ($r['balance_qty'] ?? 0),
            ];
        }

        return ['results' => $results];
    }
}
