<?php

namespace app\modules\inventoryV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\modules\inventoryV2\components\InventorySyncService;
use app\modules\inventoryV2\models\Warehouse;

/**
 * Sync ข้อมูลจาก inventory (V1) → inventoryV2
 * - เลือกช่วงวัน/เดือน + คลัง
 * - Preview รายการที่จะ sync
 * - Run sync (Idempotent — รันซ้ำได้)
 * - Verify เทียบยอด V1 vs V2
 */
class SyncFromV1Controller extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'run' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * หน้าหลัก — แสดง form + summary stats
     */
    public function actionIndex()
    {
        $dateFrom = Yii::$app->request->get('date_from') ?: date('Y-m-01');
        $dateTo   = Yii::$app->request->get('date_to')   ?: date('Y-m-t');
        $whId     = Yii::$app->request->get('warehouse_id');
        $whId     = ($whId === '' || $whId === null) ? null : (int) $whId;

        $warehouseOptions = ArrayHelper::map(
            Warehouse::find()->orderBy(['warehouse_name' => SORT_ASC])->all(),
            'id', 'warehouse_name'
        );

        // นับ stats เบื้องต้น
        $statSql = "
            SELECT
                COUNT(DISTINCT e.id) AS total_orders,
                SUM(CASE WHEN e.transaction_type = 'IN'  THEN 1 ELSE 0 END) AS in_orders,
                SUM(CASE WHEN e.transaction_type = 'OUT' THEN 1 ELSE 0 END) AS out_orders,
                COUNT(DISTINCT so.id) AS synced_count
            FROM stock_events e
            LEFT JOIN stock_order so ON so.ref = CONCAT('" . InventorySyncService::REF_PREFIX . "', e.id)
            WHERE e.name = 'order'
              AND e.order_status = 'success'
              AND e.movement_date BETWEEN :from AND :to
              " . ($whId ? "AND e.warehouse_id = :wh" : "") . "
        ";
        $params = [':from' => $dateFrom.' 00:00:00', ':to' => $dateTo.' 23:59:59'];
        if ($whId) $params[':wh'] = $whId;
        $stats = Yii::$app->db->createCommand($statSql, $params)->queryOne() ?: [
            'total_orders' => 0, 'in_orders' => 0, 'out_orders' => 0, 'synced_count' => 0,
        ];

        return $this->render('index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'whId' => $whId,
            'warehouseOptions' => $warehouseOptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Preview — แสดงรายการ stock_events ที่จะ sync
     */
    public function actionPreview()
    {
        $dateFrom = Yii::$app->request->get('date_from') ?: date('Y-m-01');
        $dateTo   = Yii::$app->request->get('date_to')   ?: date('Y-m-t');
        $whId     = Yii::$app->request->get('warehouse_id');
        $whId     = ($whId === '' || $whId === null) ? null : (int) $whId;

        $service = new InventorySyncService();
        $rows = $service->preview($dateFrom, $dateTo, $whId);

        $warehouseOptions = ArrayHelper::map(
            Warehouse::find()->orderBy(['warehouse_name' => SORT_ASC])->all(),
            'id', 'warehouse_name'
        );

        return $this->render('preview', [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'whId' => $whId,
            'warehouseOptions' => $warehouseOptions,
        ]);
    }

    /**
     * Run sync จริง (POST)
     */
    public function actionRun()
    {
        $dateFrom = Yii::$app->request->post('date_from') ?: date('Y-m-01');
        $dateTo   = Yii::$app->request->post('date_to')   ?: date('Y-m-t');
        $whId     = Yii::$app->request->post('warehouse_id');
        $whId     = ($whId === '' || $whId === null) ? null : (int) $whId;

        $service = new InventorySyncService();
        try {
            $result = $service->syncRange($dateFrom, $dateTo, $whId);
            $msg = sprintf(
                'Sync เรียบร้อย — orders: +%d, ~%d | details: +%d, ~%d | skipped items: %d',
                $result['stats']['orders_inserted'],
                $result['stats']['orders_updated'],
                $result['stats']['details_inserted'],
                $result['stats']['details_updated'],
                $result['stats']['items_skipped']
            );
            Yii::$app->session->setFlash('success', $msg);
            if (!empty($result['errors'])) {
                Yii::$app->session->setFlash('warning',
                    'มี error บางรายการ:<br>• ' . implode('<br>• ', array_slice($result['errors'], 0, 10)));
            }
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        return $this->redirect(['verify',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'warehouse_id' => $whId,
        ]);
    }

    /**
     * Verify — เทียบยอด V1 vs V2 รายสินค้า + highlight diff
     */
    public function actionVerify()
    {
        $dateFrom = Yii::$app->request->get('date_from') ?: date('Y-m-01');
        $dateTo   = Yii::$app->request->get('date_to')   ?: date('Y-m-t');
        $whId     = Yii::$app->request->get('warehouse_id');
        $whId     = ($whId === '' || $whId === null) ? null : (int) $whId;
        $onlyDiff = (bool) Yii::$app->request->get('only_diff');

        $service = new InventorySyncService();
        $rows = $service->verify($dateFrom, $dateTo, $whId);

        if ($onlyDiff) {
            $rows = array_filter($rows, function ($r) { return $r['has_diff']; });
        }

        $warehouseOptions = ArrayHelper::map(
            Warehouse::find()->orderBy(['warehouse_name' => SORT_ASC])->all(),
            'id', 'warehouse_name'
        );

        return $this->render('verify', [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'whId' => $whId,
            'onlyDiff' => $onlyDiff,
            'warehouseOptions' => $warehouseOptions,
        ]);
    }
}
