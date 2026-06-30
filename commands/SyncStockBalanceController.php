<?php

namespace app\commands;

use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * คำนวณยอดคงเหลือที่ถูกต้องจาก stock_order + stock_detail (ระบบเดิมที่คำนวณผิด)
 * แล้วซิงก์เข้า stock_balance เป็นยอดยกมา/เปิดระบบใหม่
 *
 * วิธีใช้:
 *   php yii sync-stock-balance/recalc   คำนวณและอัปเดต stock_balance (dry-run แสดงผลก่อน)
 *   php yii sync-stock-balance/recalc --apply   คำนวณแล้วเขียนลง stock_balance จริง
 */
class SyncStockBalanceController extends Controller
{
    /** @var bool เมื่อใส่ --apply จะเขียนลง DB จริง */
    public $apply = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['apply']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['a' => 'apply']);
    }

    /**
     * Replay ทุกเอกสาร CONFIRMED ตามลำดับเวลา → ได้ยอดคงเหลือต่อ (item, warehouse, lot)
     * แล้วเทียบ/อัปเดต stock_balance
     */
    public function actionRecalc()
    {
        $orders = StockOrder::find()
            ->where(['status' => StockOrder::STATUS_CONFIRMED])
            ->orderBy(['order_date' => SORT_ASC, 'id' => SORT_ASC])
            ->with('stockDetails')
            ->all();

        // state: key = "item_code|warehouse_id" => [ [ 'lot' => x, 'qty' => y, 'order_date' => z ], ... ] (FIFO queue)
        $fifo = [];
        // balance: key = "item_code|warehouse_id|lot_number" => qty (ผลลัพธ์สุดท้าย)
        $balance = [];

        $this->stdout("Replaying " . count($orders) . " orders (CONFIRMED only)...\n");

        foreach ($orders as $order) {
            $date = $order->order_date;
            $mainWh = (int) $order->main_warehouse_id;
            $subWh = $order->sub_warehouse_id ? (int) $order->sub_warehouse_id : null;

            if ($mainWh <= 0 && in_array($order->order_type, [StockOrder::ORDER_TYPE_IN, StockOrder::ORDER_TYPE_OUT, StockOrder::ORDER_TYPE_ADJUST], true)) {
                continue;
            }
            if ($order->order_type === StockOrder::ORDER_TYPE_TRANSFER && ($mainWh <= 0 || $subWh <= 0)) {
                continue;
            }

            foreach ($order->stockDetails as $d) {
                $item = (string) $d->item_code;
                $qty = (float) $d->qty;
                $lot = $d->lot_number ?: '-';

                if ($order->order_type === StockOrder::ORDER_TYPE_IN) {
                    $this->fifoAdd($fifo, $balance, $item, $mainWh, $lot, $qty, $date);
                } elseif ($order->order_type === StockOrder::ORDER_TYPE_ADJUST) {
                    $this->balanceAdd($balance, $item, $mainWh, $lot, $qty);
                } elseif ($order->order_type === StockOrder::ORDER_TYPE_OUT) {
                    $this->fifoDeduct($fifo, $balance, $item, $mainWh, $qty);
                } elseif ($order->order_type === StockOrder::ORDER_TYPE_TRANSFER && $subWh !== null) {
                    $deducted = $this->fifoDeductReturnLots($fifo, $balance, $item, $mainWh, $qty);
                    foreach ($deducted as $lotQty) {
                        $this->fifoAdd($fifo, $balance, $item, $subWh, $lotQty['lot'], $lotQty['qty'], $date);
                    }
                }
            }
        }

        // เฉพาะ OUT อาจเหลือใน fifo queue ที่ยังไม่ถูก consume ครบ → รวมเข้า balance แล้ว
        $this->flushFifoToBalance($fifo, $balance);

        $this->stdout("Recalculated " . count($balance) . " (item, warehouse, lot) balances.\n");

        if (!$this->apply) {
            $this->stdout("DRY-RUN. To write to DB, run: php yii sync-stock-balance/recalc --apply\n");
            $this->printSample($balance, 20);
            return ExitCode::OK;
        }

        return $this->syncToStockBalance($balance);
    }

    private function fifoAdd(array &$fifo, array &$balance, string $item, int $wh, string $lot, float $qty, string $date): void
    {
        $key = $item . '|' . $wh;
        if (!isset($fifo[$key])) {
            $fifo[$key] = [];
        }
        $fifo[$key][] = ['lot' => $lot, 'qty' => $qty, 'order_date' => $date];
    }

    private function balanceAdd(array &$balance, string $item, int $wh, string $lot, float $delta): void
    {
        $bKey = $item . '|' . $wh . '|' . $lot;
        $balance[$bKey] = ($balance[$bKey] ?? 0) + $delta;
    }

    private function fifoDeduct(array &$fifo, array &$balance, string $item, int $wh, float $need): void
    {
        $this->fifoDeductReturnLots($fifo, $balance, $item, $wh, $need);
    }

    /** ลดจาก FIFO queue ของ (item, wh) จำนวน $need คืนรายการ (lot, qty) ที่หัก (สำหรับ TRANSFER ไปเพิ่มที่ sub_wh) */
    private function fifoDeductReturnLots(array &$fifo, array &$balance, string $item, int $wh, float $need): array
    {
        $key = $item . '|' . $wh;
        $list = $fifo[$key] ?? [];
        $deducted = [];
        $remaining = $need;

        $newList = [];
        foreach ($list as $row) {
            if ($remaining <= 0) {
                $newList[] = $row;
                continue;
            }
            $take = min($remaining, (float) $row['qty']);
            if ($take > 0) {
                $remaining -= $take;
                $deducted[] = ['lot' => $row['lot'], 'qty' => $take];
                $row['qty'] -= $take;
            }
            if ($row['qty'] > 0) {
                $newList[] = $row;
            }
        }
        $fifo[$key] = $newList;

        return $deducted;
    }

    private function flushFifoToBalance(array &$fifo, array &$balance): void
    {
        foreach ($fifo as $key => $list) {
            foreach ($list as $row) {
                $q = (float) $row['qty'];
                if ($q <= 0) {
                    continue;
                }
                $parts = explode('|', $key, 2);
                $item = $parts[0];
                $wh = (int) $parts[1];
                $lot = $row['lot'];
                $bKey = $item . '|' . $wh . '|' . $lot;
                $balance[$bKey] = ($balance[$bKey] ?? 0) + $q;
            }
        }
    }

    private function printSample(array $balance, int $limit): void
    {
        $i = 0;
        foreach ($balance as $key => $qty) {
            if ($i >= $limit) {
                break;
            }
            $parts = explode('|', $key, 3);
            $this->stdout(sprintf("  %s | wh=%s | lot=%s => %s\n", $parts[0], $parts[1] ?? '', $parts[2] ?? '', $qty));
            $i++;
        }
    }

    private function syncToStockBalance(array $balance): int
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $table = $db->getSchema()->getRawTableName(StockBalance::tableName());
            $db->createCommand("DELETE FROM " . $db->quoteTableName($table))->execute();

            $rows = [];
            $now = date('Y-m-d H:i:s');
            $userId = 1; // console ไม่มี user; ใช้ 1 หรือ 0

            foreach ($balance as $key => $qty) {
                $qty = (float) $qty;
                if ($qty == 0) {
                    continue;
                }
                $parts = explode('|', $key, 3);
                if (count($parts) < 3) {
                    continue;
                }
                $itemCode = $parts[0];
                $warehouseId = (int) $parts[1];
                $lotNumber = $parts[2];
                $rows[] = [
                    'item_code' => $itemCode,
                    'warehouse_id' => $warehouseId,
                    'lot_number' => $lotNumber,
                    'balance_qty' => $qty,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ];
            }

            foreach ($rows as $row) {
                $db->createCommand()->insert(StockBalance::tableName(), $row)->execute();
            }

            $transaction->commit();
            $this->stdout("Written " . count($rows) . " rows to stock_balance.\n");
            return ExitCode::OK;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
