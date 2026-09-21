<?php

namespace app\modules\laundry\controllers;

use app\modules\hr\models\Organization;
use app\modules\laundry\services\PieceInventoryService;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class InventoryController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.approve']],
                    ['allow' => true, 'actions' => ['create-item', 'set-par', 'receive', 'issue', 'return-counted', 'qc', 'request-loss', 'request-dry-count', 'record-ironing', 'return-for-rewash', 'request-qc-disposal'], 'roles' => ['laundry.manage']],
                    ['allow' => true, 'actions' => ['opening', 'approve-loss', 'set-receiving-warehouse', 'approve-dry-count', 'complete-repair', 'approve-qc-disposal'], 'roles' => ['laundry.approve']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create-item' => ['POST'], 'set-par' => ['POST'], 'set-receiving-warehouse' => ['POST'], 'opening' => ['POST'],
                    'receive' => ['POST'], 'issue' => ['POST'], 'return-counted' => ['POST'],
                    'qc' => ['POST'], 'request-loss' => ['POST'], 'approve-loss' => ['POST'],
                    'request-dry-count' => ['POST'], 'approve-dry-count' => ['POST'], 'record-ironing' => ['POST'],
                    'return-for-rewash' => ['POST'], 'complete-repair' => ['POST'],
                    'request-qc-disposal' => ['POST'], 'approve-qc-disposal' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $items = (new Query())->from('laundry_item')->orderBy(['item_name' => SORT_ASC])->all();
        $inventory = new PieceInventoryService();
        foreach ($items as &$item) {
            $item['clean_qty'] = $inventory->balance((int) $item['id'], 'CLEAN');
            $item['dirty_qty'] = $inventory->balance((int) $item['id'], 'DIRTY');
            $item['rework_qty'] = $inventory->balance((int) $item['id'], 'REWORK');
            $item['repair_qty'] = $inventory->balance((int) $item['id'], 'REPAIR');
            $item['disposal_pending_qty'] = $inventory->balance((int) $item['id'], 'DISPOSAL_PENDING');
        }
        unset($item);
        $events = (new Query())->select(['e.*', 'item_name' => 'i.item_name'])
            ->from(['e' => 'laundry_piece_event'])->innerJoin(['i' => 'laundry_item'], 'i.id = e.item_id')
            ->orderBy(['e.id' => SORT_DESC])->limit(40)->all();
        $departments = Organization::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        $dryBatches = (new Query())->select(['batch_no', 'id'])->from('laundry_processing_batch')
            ->where(['stage' => 'DRY', 'status' => 'COMPLETED'])->orderBy(['id' => SORT_DESC])->limit(100)->all();
        $batchOptions = [];
        foreach ($dryBatches as $batch) {
            $batchOptions[$batch['id']] = $batch['batch_no'];
        }
        $par = (new Query())->select(['p.*', 'item_name' => 'i.item_name', 'department_name' => 'd.name'])
            ->from(['p' => 'laundry_par'])->innerJoin(['i' => 'laundry_item'], 'i.id = p.item_id')
            ->leftJoin(['d' => 'tree'], 'd.id = p.department_id')
            ->orderBy(['department_name' => SORT_ASC, 'item_name' => SORT_ASC])->all();
        $warehouses = (new Query())->select(['id', 'warehouse_name'])->from('warehouses')
            ->where(['warehouse_type' => ['SUB', 'BRANCH']])->orderBy(['warehouse_name' => SORT_ASC])->all();
        $warehouseOptions = [];
        foreach ($warehouses as $warehouse) {
            $warehouseOptions[$warehouse['id']] = $warehouse['warehouse_name'];
        }
        $receivingWarehouseId = (new Query())->select('receiving_warehouse_id')->from('laundry_config')->where(['id' => 1])->scalar();
        $dryCounts = (new Query())->select(['c.*', 'batch_no' => 'b.batch_no', 'item_name' => 'i.item_name'])
            ->from(['c' => 'laundry_batch_piece_count'])->innerJoin(['b' => 'laundry_processing_batch'], 'b.id = c.dry_batch_id')
            ->innerJoin(['i' => 'laundry_item'], 'i.id = c.item_id')
            ->orderBy(['c.id' => SORT_DESC])->limit(40)->all();
        $lots = (new Query())->select(['l.*', 'item_name' => 'i.item_name', 'item_code' => 'i.item_code', 'batch_no' => 'b.batch_no'])
            ->from(['l' => 'laundry_clean_lot'])->innerJoin(['i' => 'laundry_item'], 'i.id = l.item_id')
            ->leftJoin(['b' => 'laundry_processing_batch'], 'b.id = l.dry_batch_id')
            ->orderBy(['l.id' => SORT_DESC])->all();
        $lotBalances = $inventory->lotBalances();
        $availableLots = [];
        foreach ($lots as &$lot) {
            $lot['available_qty'] = $lotBalances[(int) $lot['id']] ?? 0;
            if ($lot['available_qty'] > 0) {
                $availableLots[$lot['id']] = $lot['lot_no'] . ' · ' . $lot['item_name'] . ' (' . $lot['available_qty'] . ' ชิ้น)';
            }
        }
        unset($lot);
        $ironing = (new Query())->select(['r.*', 'batch_no' => 'b.batch_no', 'item_name' => 'i.item_name'])
            ->from(['r' => 'laundry_ironing'])->innerJoin(['b' => 'laundry_processing_batch'], 'b.id = r.dry_batch_id')
            ->innerJoin(['i' => 'laundry_item'], 'i.id = r.item_id')
            ->orderBy(['r.id' => SORT_DESC])->limit(40)->all();
        return $this->render('index', compact('items', 'events', 'departments', 'batchOptions', 'par', 'warehouseOptions', 'receivingWarehouseId', 'dryCounts', 'lots', 'availableLots', 'ironing'));
    }

    public function actionCreateItem()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->createItem((string) Yii::$app->request->post('item_code'),
                (string) Yii::$app->request->post('item_name'),
                (string) Yii::$app->request->post('stock_item_code'), $this->userId());
        });
    }

    public function actionSetPar()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->setPar((int) Yii::$app->request->post('department_id'), (int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('target_qty'), (int) Yii::$app->request->post('min_qty'), $this->userId());
        });
    }

    public function actionSetReceivingWarehouse()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->setReceivingWarehouse((int) Yii::$app->request->post('warehouse_id'), $this->userId());
        });
    }

    public function actionOpening()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $location = (string) Yii::$app->request->post('location');
            $service->opening((int) Yii::$app->request->post('item_id'), (int) Yii::$app->request->post('qty'),
                $location, $location === 'WARD' ? (int) Yii::$app->request->post('department_id') : null,
                (string) Yii::$app->request->post('reason'), $this->userId());
        });
    }

    public function actionReceive()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->receiveFromStockDetail((int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('stock_detail_id'), $this->userId());
        });
    }

    public function actionIssue()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->issueFromLot((int) Yii::$app->request->post('lot_id'),
                (int) Yii::$app->request->post('department_id'),
                (int) Yii::$app->request->post('qty'), $this->userId());
        });
    }

    public function actionReturnCounted()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->returnCounted((int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('department_id'),
                (int) Yii::$app->request->post('qty'), $this->userId());
        });
    }

    public function actionQc()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->qc((int) Yii::$app->request->post('item_id'), (int) Yii::$app->request->post('qty'),
                (string) Yii::$app->request->post('destination'),
                (int) Yii::$app->request->post('dry_batch_id'), $this->userId());
        });
    }

    public function actionRequestDryCount()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->requestDryPieceCount((int) Yii::$app->request->post('dry_batch_id'),
                (int) Yii::$app->request->post('item_id'), (int) Yii::$app->request->post('qty'),
                (string) Yii::$app->request->post('evidence'), (int) $this->userId());
        });
    }

    public function actionRecordIroning()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->recordIroning((int) Yii::$app->request->post('dry_batch_id'),
                (int) Yii::$app->request->post('item_id'), (int) Yii::$app->request->post('qty'),
                (string) Yii::$app->request->post('started_at'), (string) Yii::$app->request->post('ended_at'),
                (string) Yii::$app->request->post('evidence'), (int) $this->userId());
        });
    }

    public function actionApproveDryCount(int $id)
    {
        return $this->runWrite(function (PieceInventoryService $service) use ($id) {
            $service->approveDryPieceCount($id, (int) $this->userId());
        });
    }

    public function actionRequestLoss()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->requestLoss((int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('department_id'), (int) Yii::$app->request->post('qty'),
                (string) Yii::$app->request->post('reason'), $this->userId());
        });
    }

    public function actionApproveLoss(int $id)
    {
        return $this->runWrite(function (PieceInventoryService $service) use ($id) {
            $service->approveLoss($id, (int) $this->userId());
        });
    }

    public function actionReturnForRewash()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->returnForRewash((int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('qty'), (string) Yii::$app->request->post('evidence'), $this->userId());
        });
    }

    public function actionCompleteRepair()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->completeRepair((int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('qty'), (string) Yii::$app->request->post('evidence'), $this->userId());
        });
    }

    public function actionRequestQcDisposal()
    {
        return $this->runWrite(function (PieceInventoryService $service) {
            $service->requestQcDisposal((int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('qty'), (string) Yii::$app->request->post('reason'), (int) $this->userId());
        });
    }

    public function actionApproveQcDisposal(int $id)
    {
        return $this->runWrite(function (PieceInventoryService $service) use ($id) {
            $service->approveQcDisposal($id, (int) $this->userId());
        });
    }

    private function runWrite(callable $callback)
    {
        try {
            $callback(new PieceInventoryService());
            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลผ้าเรียบร้อย');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'บันทึกไม่สำเร็จ กรุณาตรวจสอบข้อมูล');
            Yii::error($e, __METHOD__);
        }
        return $this->redirect(['index']);
    }

    private function userId(): ?int
    {
        return Yii::$app->user->id ? (int) Yii::$app->user->id : null;
    }
}
