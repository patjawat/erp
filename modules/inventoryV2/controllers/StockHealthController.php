<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\components\StockHealthService;
use app\modules\inventoryV2\components\StockRepairService;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/** Read-only stock reconciliation dashboard. */
class StockHealthController extends Controller
{
    public const PERMISSION_REPAIR = 'inventoryStockRepair';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['actions' => ['repair'], 'allow' => true, 'roles' => [self::PERMISSION_REPAIR]],
                ['actions' => ['repair'], 'allow' => false],
                ['allow' => true, 'roles' => ['@']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['dry-run' => ['post'], 'repair' => ['post']]],
        ]);
    }

    public function actionIndex()
    {
        $warehouses = $this->accessibleWarehouses();
        $filters = [
            'warehouse_id' => (int) $this->request->get('warehouse_id', 0),
            'status' => trim((string) $this->request->get('status', '')),
            'search' => trim((string) $this->request->get('search', '')),
            'include_healthy' => (bool) $this->request->get('include_healthy', false),
        ];
        if ($filters['status'] === 'healthy') {
            $filters['include_healthy'] = true;
        }
        $result = StockHealthService::scan(ArrayHelper::getColumn($warehouses, 'id'), $filters);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $result['rows'],
            'pagination' => ['pageSize' => 50],
            'sort' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'summary' => $result['summary'],
            'generatedAt' => $result['generated_at'],
            'warehouses' => ['' => 'ทุกคลังที่รับผิดชอบ'] + ArrayHelper::map($warehouses, 'id', 'warehouse_name'),
            'filters' => $filters,
            'canRepair' => Yii::$app->user->can(self::PERMISSION_REPAIR),
        ]);
    }

    public function actionDryRun()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        [$warehouseId, $itemCode, $scope, $lotNumber, $physicalQty] = $this->repairInput();
        $this->assertWarehouseAccess($warehouseId);
        return ['success' => true, 'read_only' => true, 'can_repair' => Yii::$app->user->can(self::PERMISSION_REPAIR),
            'result' => StockRepairService::plan($warehouseId, $itemCode, $scope, $lotNumber, $physicalQty)];
    }

    public function actionRepair()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        [$warehouseId, $itemCode, $scope, $lotNumber, $physicalQty] = $this->repairInput();
        $this->assertWarehouseAccess($warehouseId);
        try {
            $result = StockRepairService::execute(
                $warehouseId, $itemCode, $scope, $lotNumber,
                trim((string) $this->request->post('fingerprint', '')),
                trim((string) $this->request->post('reason', '')),
                Yii::$app->user->id === null ? null : (int) Yii::$app->user->id,
                $physicalQty
            );
            return $result;
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->response->statusCode = 422;
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionExport()
    {
        $warehouses = $this->accessibleWarehouses();
        $filters = [
            'warehouse_id' => (int) $this->request->get('warehouse_id', 0),
            'status' => trim((string) $this->request->get('status', '')),
            'search' => trim((string) $this->request->get('search', '')),
            'include_healthy' => (bool) $this->request->get('include_healthy', false),
        ];
        if ($filters['status'] === 'healthy') {
            $filters['include_healthy'] = true;
        }
        $result = StockHealthService::scan(ArrayHelper::getColumn($warehouses, 'id'), $filters);
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="stock-health-' . date('Ymd-His') . '.csv"');
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['คลัง', 'รหัสวัสดุ', 'รายการ', 'Lot', 'Ledger', 'Balance', 'FIFO', 'สถานะ', 'ปัญหา', 'แนวทาง']);
        foreach ($result['rows'] as $row) {
            fputcsv($stream, [
                $row['warehouse_name'], $row['item_code'], $row['item_name'], $row['lot_number'],
                $row['ledger_qty'], $row['balance_qty'], $row['fifo_qty'], $row['status'],
                implode('|', $row['issues']), $row['repair_mode'],
            ]);
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    private function accessibleWarehouses(): array
    {
        $warehouses = Warehouse::findMainWarehousesForReceive();
        if (empty($warehouses)) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์ตรวจสอบคลังหลัก');
        }
        return $warehouses;
    }

    private function repairInput(): array
    {
        $scope = trim((string) $this->request->post('scope', 'lot'));
        if (!in_array($scope, ['lot', 'item'], true)) $scope = 'lot';
        $physicalRaw = $this->request->post('physical_qty', null);
        $physicalQty = $physicalRaw === null || $physicalRaw === '' || !is_numeric($physicalRaw) ? null : (float) $physicalRaw;
        return [(int) $this->request->post('warehouse_id', 0), trim((string) $this->request->post('item_code', '')), $scope, trim((string) $this->request->post('lot_number', '')), $physicalQty];
    }

    private function assertWarehouseAccess(int $warehouseId): void
    {
        $allowedIds = array_map('intval', ArrayHelper::getColumn($this->accessibleWarehouses(), 'id'));
        if (!in_array($warehouseId, $allowedIds, true)) throw new ForbiddenHttpException('ไม่มีสิทธิ์ตรวจสอบคลังนี้');
    }
}
