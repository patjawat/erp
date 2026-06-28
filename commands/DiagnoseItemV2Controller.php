<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\Warehouse;

/**
 * Diagnose ความคลาดเคลื่อนระหว่าง stock_balance (truth) vs stock card history (replay)
 * ของ item เฉพาะตัว — รวมจำลอง perspective() ทั้งแบบเดิมและแบบใหม่ เพื่อชี้บั๊ก
 *
 * วิธีใช้:
 *   php yii diagnose-item-v2/item M1-1
 *   php yii diagnose-item-v2/item M1-1 --warehouse-id=12
 *
 * Output ต่อคลัง:
 *   - balance ที่อยู่ใน stock_balance (truth)
 *   - balance ที่ replay จาก stock_order/stock_detail (perspective เดิม vs ใหม่)
 *   - list ใบทั้งหมด (เรียงตามวันที่) พร้อม perspective ทั้งสองแบบ → ชี้จุดที่ต่าง
 */
class DiagnoseItemV2Controller extends Controller
{
    /** @var int|null filter เฉพาะคลังนี้ (null = ทุกคลังที่ item มี movement) */
    public $warehouseId = null;

    /** @var bool แสดงทุก line เลย ไม่ truncate */
    public $verbose = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['warehouseId', 'verbose']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['w' => 'warehouseId', 'v' => 'verbose']);
    }

    public function actionItem($itemCode)
    {
        $itemCode = trim((string) $itemCode);
        if ($itemCode === '') {
            $this->stderr("ต้องระบุ item_code\n");
            return ExitCode::USAGE;
        }

        // หาคลังทั้งหมดที่มี movement ของ item นี้
        $warehouseIds = $this->warehouseId !== null
            ? [(int) $this->warehouseId]
            : (new Query())
                ->select('warehouse_id')
                ->distinct()
                ->from('stock_balance')
                ->where(['item_code' => $itemCode])
                ->column();

        // เผื่อ stock_balance ว่าง — ใช้ stock_order ดูแทน
        if (empty($warehouseIds)) {
            $warehouseIds = $this->collectWarehousesFromOrders($itemCode);
        }

        if (empty($warehouseIds)) {
            $this->stderr("ไม่พบ movement ของ item_code='" . $itemCode . "' ในระบบ\n");
            return ExitCode::DATAERR;
        }

        $whNames = Warehouse::find()
            ->select(['id', 'warehouse_name', 'warehouse_type'])
            ->where(['id' => $warehouseIds])
            ->indexBy('id')
            ->asArray()->all();

        $this->stdout(sprintf(
            "\n=== Diagnose item '%s' across %d warehouse(s) ===\n\n",
            $itemCode, count($warehouseIds)
        ));

        foreach ($warehouseIds as $wid) {
            $wid = (int) $wid;
            $name = $whNames[$wid]['warehouse_name'] ?? '(?)';
            $type = $whNames[$wid]['warehouse_type'] ?? '?';
            $this->stdout(sprintf("─── คลัง id=%d [%s] %s ───\n", $wid, $type, $name));

            $this->reportWarehouse($itemCode, $wid);
            $this->stdout("\n");
        }

        return ExitCode::OK;
    }

    private function collectWarehousesFromOrders($itemCode)
    {
        return (new Query())
            ->select(['warehouse_id' => 'so.main_warehouse_id'])
            ->distinct()
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $itemCode, 'so.status' => StockOrder::STATUS_CONFIRMED])
            ->column();
    }

    private function reportWarehouse($itemCode, $warehouseId)
    {
        // 1) Truth = stock_balance รวมทุก lot
        $balanceRows = (new Query())
            ->select(['lot_number', 'balance_qty'])
            ->from(StockBalance::tableName())
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->orderBy(['lot_number' => SORT_ASC])
            ->all();
        $balanceTotal = 0.0;
        foreach ($balanceRows as $b) {
            $balanceTotal += (float) $b['balance_qty'];
        }

        $this->stdout(sprintf("  stock_balance (truth):  %s units · %d lot(s)\n",
            number_format($balanceTotal, 2), count($balanceRows)));
        foreach ($balanceRows as $b) {
            $this->stdout(sprintf("      · %s = %s\n",
                str_pad($b['lot_number'], 20), number_format((float) $b['balance_qty'], 2)));
        }

        // 2) Replay จาก stock_order/stock_detail
        $rows = (new Query())
            ->select([
                'so.order_no', 'so.order_type', 'so.source_type', 'so.order_date',
                'so.main_warehouse_id', 'so.sub_warehouse_id',
                'sd.qty', 'sd.unit_price', 'sd.lot_number',
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $itemCode])
            ->andWhere(['or',
                ['so.main_warehouse_id' => $warehouseId],
                ['so.sub_warehouse_id' => $warehouseId],
            ])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->orderBy(['so.order_date' => SORT_ASC, 'so.order_no' => SORT_ASC])
            ->all();

        $replayOld = 0.0;
        $replayNew = 0.0;
        $diffRows = [];

        foreach ($rows as $r) {
            $oldDir = $this->perspectiveOld($r, $warehouseId);
            $newDir = $this->perspectiveNew($r, $warehouseId);
            $qSigned = (float) $r['qty'];
            $qAbs = abs($qSigned);

            // old: ใช้ qty signed
            if ($oldDir === 'in') $replayOld += $qSigned;
            else                  $replayOld -= $qSigned;

            // new: ใช้ qty abs + perspective ที่ handle ADJUST/TRANSFER
            if ($newDir === 'in') $replayNew += $qAbs;
            else                  $replayNew -= $qAbs;

            if ($oldDir !== $newDir || $r['order_type'] === 'ADJUST' || $r['order_type'] === 'TRANSFER') {
                $diffRows[] = compact('r', 'oldDir', 'newDir', 'qSigned', 'qAbs');
            }
        }

        $deltaOld = $replayOld - $balanceTotal;
        $deltaNew = $replayNew - $balanceTotal;

        $this->stdout(sprintf("\n  Replay (perspective เดิม): %s units    Δ vs truth = %s %s\n",
            number_format($replayOld, 2),
            number_format($deltaOld, 2),
            abs($deltaOld) < 0.01 ? '✓' : '✗'));
        $this->stdout(sprintf("  Replay (perspective ใหม่): %s units    Δ vs truth = %s %s\n",
            number_format($replayNew, 2),
            number_format($deltaNew, 2),
            abs($deltaNew) < 0.01 ? '✓' : '✗'));

        // 3) ใบที่ logic เก่า/ใหม่ คิดต่าง หรือเป็น ADJUST/TRANSFER (จุดสนใจ)
        if (!empty($diffRows)) {
            $this->stdout("\n  ⚠️  ใบที่ perspective ต่าง หรือเป็น ADJUST/TRANSFER:\n");
            $this->stdout(sprintf("      %-20s %-9s %-9s %-10s %10s %5s %5s\n",
                'order_no', 'type', 'source', 'date', 'qty', 'old', 'new'));
            foreach ($diffRows as $d) {
                $r = $d['r'];
                $this->stdout(sprintf("      %-20s %-9s %-9s %-10s %10s %5s %5s%s\n",
                    substr($r['order_no'], 0, 20),
                    $r['order_type'],
                    substr((string) ($r['source_type'] ?? '-'), 0, 9),
                    substr((string) $r['order_date'], 0, 10),
                    number_format((float) $r['qty'], 2),
                    $d['oldDir'],
                    $d['newDir'],
                    $d['oldDir'] !== $d['newDir'] ? '  ←ต่าง' : ''
                ));
            }
        }

        if ($this->verbose) {
            $this->stdout("\n  ทุกใบ:\n");
            foreach ($rows as $r) {
                $this->stdout(sprintf("    %-20s %-9s %-10s qty=%s lot=%s main=%d sub=%d\n",
                    $r['order_no'], $r['order_type'], substr((string) $r['order_date'], 0, 10),
                    number_format((float) $r['qty'], 2),
                    $r['lot_number'],
                    (int) $r['main_warehouse_id'],
                    (int) ($r['sub_warehouse_id'] ?? 0)
                ));
            }
        }
    }

    /** Logic เดิม (มีบั๊ก) ที่อยู่ใน ReportController ก่อนแก้ */
    private function perspectiveOld($row, $warehouseId)
    {
        $isMain = (int) $row['main_warehouse_id'] === $warehouseId;
        $isSub = (int) ($row['sub_warehouse_id'] ?? 0) === $warehouseId;
        if ($row['order_type'] === 'IN' && $isMain) return 'in';
        if ($row['order_type'] === 'OUT' && $isSub && !$isMain) return 'in';
        if ($row['order_type'] === 'OUT' && $isMain) return 'out';
        if ($row['order_type'] === 'IN' && $isSub && !$isMain) return 'out';
        return 'out';
    }

    /** Logic ใหม่ที่แก้ใน ReportController */
    private function perspectiveNew($row, $warehouseId)
    {
        $type = $row['order_type'];
        $isMain = (int) $row['main_warehouse_id'] === $warehouseId;
        $isSub = (int) ($row['sub_warehouse_id'] ?? 0) === $warehouseId;
        if ($type === 'ADJUST') {
            return ((float) $row['qty']) >= 0 ? 'in' : 'out';
        }
        if ($type === 'TRANSFER') {
            if ($isMain && !$isSub) return 'out';
            if ($isSub && !$isMain) return 'in';
        }
        if ($type === 'IN' && $isMain) return 'in';
        if ($type === 'OUT' && $isMain) return 'out';
        if ($type === 'OUT' && $isSub && !$isMain) return 'in';
        if ($type === 'IN' && $isSub && !$isMain) return 'out';
        return 'out';
    }
}
