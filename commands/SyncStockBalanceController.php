<?php

namespace app\commands;

use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * คำนวณยอดคงเหลือที่ถูกต้องจาก stock_order + stock_detail
 * แล้วซิงก์เข้า stock_balance และ stock_detail.remain_qty ให้ FIFO ใช้จ่ายได้จริง
 *
 * วิธีใช้:
 *   php yii sync-stock-balance/recalc   คำนวณแบบ dry-run แสดงผลก่อน
 *   php yii sync-stock-balance/recalc --apply   คำนวณแล้วเขียนลง DB จริง
 */
class SyncStockBalanceController extends Controller
{
    private const EPSILON = 0.000001;
    private const SAMPLE_LIMIT = 20;

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
     * พร้อมยอดคงเหลือราย stock_detail ที่ FIFO ใช้เป็น source lot
     */
    public function actionRecalc()
    {
        $orders = StockOrder::find()
            ->where(['status' => StockOrder::STATUS_CONFIRMED])
            ->orderBy(['order_date' => SORT_ASC, 'id' => SORT_ASC])
            ->with('stockDetails')
            ->all();

        $this->stdout("Replaying " . count($orders) . " orders (CONFIRMED only)...\n");

        [$balance, $detailRemain, $warnings] = $this->replayOrders($orders);
        $detailChanges = $this->findDetailRemainChanges($detailRemain);

        $this->stdout("Recalculated " . count($balance) . " (item, warehouse, lot) balances.\n");
        $this->stdout("Prepared " . count($detailRemain) . " FIFO source stock_detail rows; " . count($detailChanges) . " remain_qty changes.\n");
        $this->printWarnings($warnings, self::SAMPLE_LIMIT);

        if (!$this->apply) {
            $this->stdout("DRY-RUN. To write to DB, run: php yii sync-stock-balance/recalc --apply\n");
            $this->printSample($balance, self::SAMPLE_LIMIT);
            $this->printDetailChangeSample($detailChanges, self::SAMPLE_LIMIT);
            return ExitCode::OK;
        }

        return $this->syncToStockTables($balance, $detailChanges);
    }

    private function replayOrders(array $orders): array
    {
        // key = "item_code|warehouse_id" => FIFO rows with lot, qty, and optional source stock_detail id
        $fifo = [];
        // key = stock_detail.id => target remain_qty and metadata for reporting
        $detailRemain = [];
        $warnings = [];

        foreach ($orders as $order) {
            $date = (string) $order->order_date;
            $mainWh = (int) $order->main_warehouse_id;
            $subWh = $order->sub_warehouse_id ? (int) $order->sub_warehouse_id : null;

            if ($mainWh <= 0 && in_array($order->order_type, [StockOrder::ORDER_TYPE_IN, StockOrder::ORDER_TYPE_OUT, StockOrder::ORDER_TYPE_ADJUST], true)) {
                $warnings[] = $this->describeOrder($order) . ': skipped because main_warehouse_id is empty';
                continue;
            }
            if ($order->order_type === StockOrder::ORDER_TYPE_TRANSFER && ($mainWh <= 0 || $subWh <= 0)) {
                $warnings[] = $this->describeOrder($order) . ': skipped because transfer warehouse is incomplete';
                continue;
            }

            foreach ($order->stockDetails as $detail) {
                $item = trim((string) $detail->item_code);
                $qty = (float) $detail->qty;
                $lot = $this->normalizeLot($detail->lot_number);

                if ($item === '' || abs($qty) <= self::EPSILON) {
                    continue;
                }

                if ($order->order_type === StockOrder::ORDER_TYPE_IN) {
                    $this->fifoAdd($fifo, $detailRemain, $detail, $item, $mainWh, $lot, $qty, $date, StockOrder::ORDER_TYPE_IN);
                } elseif ($order->order_type === StockOrder::ORDER_TYPE_ADJUST) {
                    if ($qty > 0) {
                        $this->fifoAdd($fifo, $detailRemain, $detail, $item, $mainWh, $lot, $qty, $date, StockOrder::ORDER_TYPE_ADJUST);
                    } else {
                        $lotFilter = $lot !== '-' ? $lot : null;
                        $this->fifoDeductReturnLots($fifo, $detailRemain, $warnings, $item, $mainWh, abs($qty), $lotFilter, $this->describeMovement($order, $detail));
                        $this->setDetailRemain($detailRemain, $detail, 0.0, $item, $mainWh, $lot, StockOrder::ORDER_TYPE_ADJUST);
                    }
                } elseif ($order->order_type === StockOrder::ORDER_TYPE_OUT) {
                    $this->fifoDeductReturnLots($fifo, $detailRemain, $warnings, $item, $mainWh, $qty, null, $this->describeMovement($order, $detail));
                } elseif ($order->order_type === StockOrder::ORDER_TYPE_TRANSFER && $subWh !== null) {
                    $deducted = $this->fifoDeductReturnLots($fifo, $detailRemain, $warnings, $item, $mainWh, $qty, null, $this->describeMovement($order, $detail));
                    $this->setDetailRemain($detailRemain, $detail, 0.0, $item, $subWh, $lot, StockOrder::ORDER_TYPE_TRANSFER);

                    $transferDetail = $this->transferDetailCanRepresentLots($detail, $deducted) ? $detail : null;
                    if ($transferDetail === null && $this->sumDeductedQty($deducted) > self::EPSILON) {
                        $warnings[] = $this->describeMovement($order, $detail)
                            . ': transfer destination lot(s) '
                            . $this->formatDeductedLots($deducted)
                            . ' cannot be represented by stock_detail lot '
                            . $lot
                            . '; destination remain_qty kept at 0';
                    }

                    foreach ($deducted as $lotQty) {
                        $this->fifoAdd($fifo, $detailRemain, $transferDetail, $item, $subWh, $lotQty['lot'], $lotQty['qty'], $date, StockOrder::ORDER_TYPE_TRANSFER);
                    }
                }
            }
        }

        $balance = [];
        $this->flushFifoToBalance($fifo, $balance);

        return [$balance, $detailRemain, $warnings];
    }

    private function fifoAdd(
        array &$fifo,
        array &$detailRemain,
        ?StockDetail $detail,
        string $item,
        int $wh,
        string $lot,
        float $qty,
        string $date,
        string $orderType
    ): void {
        if ($qty <= self::EPSILON) {
            return;
        }

        $detailId = null;
        if ($detail !== null) {
            $detailId = (int) $detail->id;
            $this->addDetailRemain($detailRemain, $detail, $qty, $item, $wh, $lot, $orderType);
        }

        $key = $item . '|' . $wh;
        if (!isset($fifo[$key])) {
            $fifo[$key] = [];
        }
        $fifo[$key][] = [
            'detail_id' => $detailId,
            'lot' => $lot,
            'qty' => $qty,
            'order_date' => $date,
        ];
    }

    private function addDetailRemain(array &$detailRemain, StockDetail $detail, float $qty, string $item, int $wh, string $lot, string $orderType): void
    {
        $id = (int) $detail->id;
        if ($id <= 0) {
            return;
        }

        if (!isset($detailRemain[$id])) {
            $detailRemain[$id] = [
                'qty' => 0.0,
                'item_code' => $item,
                'warehouse_id' => $wh,
                'lot_number' => $lot,
                'order_type' => $orderType,
            ];
        }

        $detailRemain[$id]['qty'] += $qty;
        $detailRemain[$id]['qty'] = $this->normalizeQty($detailRemain[$id]['qty']);
    }

    private function setDetailRemain(array &$detailRemain, StockDetail $detail, float $qty, string $item, int $wh, string $lot, string $orderType): void
    {
        $id = (int) $detail->id;
        if ($id <= 0) {
            return;
        }

        $detailRemain[$id] = [
            'qty' => $this->normalizeQty(max(0.0, $qty)),
            'item_code' => $item,
            'warehouse_id' => $wh,
            'lot_number' => $lot,
            'order_type' => $orderType,
        ];
    }

    /**
     * ลดจาก FIFO queue ของ (item, wh) จำนวน $need คืนรายการ (lot, qty) ที่หัก
     */
    private function fifoDeductReturnLots(
        array &$fifo,
        array &$detailRemain,
        array &$warnings,
        string $item,
        int $wh,
        float $need,
        ?string $lotFilter,
        string $movement
    ): array {
        if ($need <= self::EPSILON) {
            return [];
        }

        $key = $item . '|' . $wh;
        $list = $fifo[$key] ?? [];
        $deducted = [];
        $remaining = $need;
        $newList = [];

        foreach ($list as $row) {
            $rowQty = (float) $row['qty'];
            $matchesLot = $lotFilter === null || $row['lot'] === $lotFilter;

            if ($remaining <= self::EPSILON || !$matchesLot) {
                if ($rowQty > self::EPSILON) {
                    $newList[] = $row;
                }
                continue;
            }

            $take = min($remaining, $rowQty);
            if ($take > self::EPSILON) {
                $remaining -= $take;
                $deducted[] = [
                    'detail_id' => $row['detail_id'] ?? null,
                    'lot' => $row['lot'],
                    'qty' => $take,
                ];
                $rowQty -= $take;

                $detailId = (int) ($row['detail_id'] ?? 0);
                if ($detailId > 0 && isset($detailRemain[$detailId])) {
                    $detailRemain[$detailId]['qty'] = $this->normalizeQty(max(0.0, (float) $detailRemain[$detailId]['qty'] - $take));
                }
            }

            if ($rowQty > self::EPSILON) {
                $row['qty'] = $rowQty;
                $newList[] = $row;
            }
        }

        $fifo[$key] = $newList;

        if ($remaining > self::EPSILON) {
            $lotText = $lotFilter === null ? 'any lot' : 'lot=' . $lotFilter;
            $warnings[] = sprintf('%s: FIFO short item=%s wh=%d %s by %.2f', $movement, $item, $wh, $lotText, $remaining);
        }

        return $deducted;
    }

    private function flushFifoToBalance(array &$fifo, array &$balance): void
    {
        foreach ($fifo as $key => $list) {
            foreach ($list as $row) {
                $q = (float) $row['qty'];
                if ($q <= self::EPSILON) {
                    continue;
                }
                $parts = explode('|', $key, 2);
                $item = $parts[0];
                $wh = (int) $parts[1];
                $lot = $row['lot'];
                $bKey = $item . '|' . $wh . '|' . $lot;
                $balance[$bKey] = $this->normalizeQty(($balance[$bKey] ?? 0) + $q);
            }
        }
    }

    private function transferDetailCanRepresentLots(StockDetail $detail, array $deducted): bool
    {
        $lots = [];
        foreach ($deducted as $row) {
            if ((float) $row['qty'] > self::EPSILON) {
                $lots[$row['lot']] = true;
            }
        }

        if (count($lots) !== 1) {
            return false;
        }

        $onlyLot = null;
        foreach ($lots as $lot => $_) {
            $onlyLot = $lot;
            break;
        }

        return $this->normalizeLot($detail->lot_number) === $onlyLot;
    }

    private function findDetailRemainChanges(array $detailRemain): array
    {
        if (empty($detailRemain)) {
            return [];
        }

        $currentRows = [];
        foreach (array_chunk(array_keys($detailRemain), 1000) as $ids) {
            $currentRows += StockDetail::find()
                ->select(['id', 'remain_qty'])
                ->where(['id' => $ids])
                ->indexBy('id')
                ->asArray()
                ->all();
        }

        $changes = [];
        foreach ($detailRemain as $id => $row) {
            if (!isset($currentRows[$id])) {
                continue;
            }

            $before = (float) $currentRows[$id]['remain_qty'];
            $after = $this->normalizeQty(max(0.0, (float) $row['qty']));
            if (abs($before - $after) <= 0.0049) {
                continue;
            }

            $changes[] = [
                'id' => (int) $id,
                'before' => $before,
                'after' => $after,
                'item_code' => $row['item_code'],
                'warehouse_id' => $row['warehouse_id'],
                'lot_number' => $row['lot_number'],
                'order_type' => $row['order_type'],
            ];
        }

        return $changes;
    }

    private function printSample(array $balance, int $limit): void
    {
        $this->stdout("stock_balance sample:\n");
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

    private function printDetailChangeSample(array $changes, int $limit): void
    {
        if (empty($changes)) {
            $this->stdout("stock_detail.remain_qty changes: none\n");
            return;
        }

        $this->stdout("stock_detail.remain_qty change sample:\n");
        foreach (array_slice($changes, 0, $limit) as $change) {
            $this->stdout(sprintf(
                "  #%d %s | wh=%s | lot=%s | %s: %s -> %s\n",
                $change['id'],
                $change['item_code'],
                $change['warehouse_id'],
                $change['lot_number'],
                $change['order_type'],
                $change['before'],
                $change['after']
            ));
        }

        if (count($changes) > $limit) {
            $this->stdout("  ... and " . (count($changes) - $limit) . " more stock_detail rows\n");
        }
    }

    private function printWarnings(array $warnings, int $limit): void
    {
        if (empty($warnings)) {
            return;
        }

        $this->stdout("Warnings:\n");
        foreach (array_slice($warnings, 0, $limit) as $warning) {
            $this->stdout("  - " . $warning . "\n");
        }
        if (count($warnings) > $limit) {
            $this->stdout("  ... and " . (count($warnings) - $limit) . " more warnings\n");
        }
    }

    private function syncToStockTables(array $balance, array $detailChanges): int
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $table = $db->getSchema()->getRawTableName(StockBalance::tableName());
            $db->createCommand("DELETE FROM " . $db->quoteTableName($table))->execute();

            $rows = $this->prepareStockBalanceRows($balance);
            foreach ($rows as $row) {
                $db->createCommand()->insert(StockBalance::tableName(), $row)->execute();
            }

            foreach ($detailChanges as $change) {
                $db->createCommand()->update(
                    StockDetail::tableName(),
                    ['remain_qty' => $change['after']],
                    ['id' => $change['id']]
                )->execute();
            }

            $transaction->commit();
            $this->stdout("Written " . count($rows) . " rows to stock_balance.\n");
            $this->stdout("Updated " . count($detailChanges) . " rows in stock_detail.remain_qty.\n");
            return ExitCode::OK;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    private function prepareStockBalanceRows(array $balance): array
    {
        $rows = [];
        $now = date('Y-m-d H:i:s');
        $userId = 1; // console ไม่มี user; ใช้ 1 หรือ 0

        foreach ($balance as $key => $qty) {
            $qty = $this->normalizeQty((float) $qty);
            if (abs($qty) <= self::EPSILON) {
                continue;
            }
            $parts = explode('|', $key, 3);
            if (count($parts) < 3) {
                continue;
            }

            $rows[] = [
                'item_code' => $parts[0],
                'warehouse_id' => (int) $parts[1],
                'lot_number' => $parts[2],
                'balance_qty' => $qty,
                'updated_at' => $now,
                'updated_by' => $userId,
            ];
        }

        return $rows;
    }

    private function normalizeLot($lot): string
    {
        $lot = trim((string) $lot);
        return $lot === '' ? '-' : $lot;
    }

    private function normalizeQty(float $qty): float
    {
        $qty = round($qty, 2);
        return abs($qty) <= self::EPSILON ? 0.0 : $qty;
    }

    private function sumDeductedQty(array $deducted): float
    {
        $sum = 0.0;
        foreach ($deducted as $row) {
            $sum += (float) $row['qty'];
        }
        return $this->normalizeQty($sum);
    }

    private function formatDeductedLots(array $deducted): string
    {
        $lots = [];
        foreach ($deducted as $row) {
            $lot = $row['lot'];
            $lots[$lot] = ($lots[$lot] ?? 0) + (float) $row['qty'];
        }

        $parts = [];
        foreach ($lots as $lot => $qty) {
            $parts[] = $lot . '=' . $this->normalizeQty($qty);
        }

        return implode(', ', $parts);
    }

    private function describeMovement(StockOrder $order, StockDetail $detail): string
    {
        return sprintf(
            'order#%d %s detail#%d',
            (int) $order->id,
            (string) $order->order_type,
            (int) $detail->id
        );
    }

    private function describeOrder(StockOrder $order): string
    {
        return sprintf('order#%d %s', (int) $order->id, (string) $order->order_type);
    }
}
