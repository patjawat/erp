<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\inventoryV2\components\TransferV2HistoryMigrator;
use app\modules\inventoryV2\models\Warehouse;

/**
 * ย้ายประวัติ stock_events (V1) → stock_order/stock_detail (V2) แบบทั้งระบบ
 * history-only — ไม่กระทบ stock_balance / FIFO
 *
 * วิธีใช้:
 *   php yii transfer-history-all-v2/run                        # ย้ายทุก warehouse_id ทั้งระบบ
 *   php yii transfer-history-all-v2/run --warehouse-id=12      # ย้ายเฉพาะคลังเดียว (debug)
 *   php yii transfer-history-all-v2/run --dry-run              # แสดง warehouse list อย่างเดียว ไม่เขียน
 *
 * Idempotent: รันซ้ำได้ — ใบที่ order_no มีอยู่ใน V2 แล้วจะถูก skip อัตโนมัติ
 */
class TransferHistoryAllV2Controller extends Controller
{
    /** @var int|null filter เฉพาะ warehouse นี้ (debug); null = ทุกคลัง */
    public $warehouseId = null;

    /** @var bool ถ้า true → ไม่เขียน DB, แสดง summary ของ warehouse list อย่างเดียว */
    public $dryRun = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['warehouseId', 'dryRun']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'w' => 'warehouseId',
            'd' => 'dryRun',
        ]);
    }

    public function actionRun()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $migrator = new TransferV2HistoryMigrator(null);

        $warehouseIds = $this->warehouseId !== null
            ? [(int) $this->warehouseId]
            : $migrator->getAllV1WarehouseIds();

        if (empty($warehouseIds)) {
            $this->stderr("ไม่พบ warehouse_id ที่ตรงกันระหว่าง V1 stock_events และ V2 warehouses\n");
            return ExitCode::DATAERR;
        }

        $whNames = Warehouse::find()
            ->select(['id', 'warehouse_name'])
            ->where(['id' => $warehouseIds])
            ->indexBy('id')
            ->asArray()->all();

        $this->stdout(sprintf(
            "พบ %d คลังที่จะย้าย%s\n",
            count($warehouseIds),
            $this->dryRun ? ' (DRY-RUN)' : ''
        ));
        foreach ($warehouseIds as $wid) {
            $name = $whNames[$wid]['warehouse_name'] ?? '(ไม่พบชื่อ)';
            $this->stdout(sprintf("  - id=%d : %s\n", $wid, $name));
        }
        $this->stdout("\n");

        if ($this->dryRun) {
            $this->stdout("DRY-RUN: ไม่มีการเขียน DB\n");
            return ExitCode::OK;
        }

        $startedAt = microtime(true);
        $grand = [
            'warehouses' => 0,
            'created' => 0,
            'linesMigrated' => 0,
            'linesSkipped' => 0,
            'duplicate' => 0,
            'noItems' => 0,
            'noWarehouse' => 0,
            'errors' => 0,
            'missingItemCodes' => [],
        ];

        foreach ($warehouseIds as $wid) {
            $name = $whNames[$wid]['warehouse_name'] ?? '(ไม่พบชื่อ)';
            $this->stdout(sprintf("[%s] กำลังย้ายคลัง id=%d : %s\n", date('H:i:s'), $wid, $name));

            list($parents, $childrenByParent, $existingV2Items, $existingOrderNos) =
                $migrator->fetchHistoryDataByWarehouse($wid);

            if (empty($parents)) {
                $this->stdout("    → ไม่มีใบให้ย้าย\n");
                continue;
            }
            $existingWarehouses = [$wid => true];
            $selectedIds = array_map('intval', array_column($parents, 'id'));

            $summary = $migrator->migrateOrders(
                $parents, $childrenByParent, $existingV2Items, $existingOrderNos,
                $existingWarehouses, $selectedIds
            );

            $this->stdout(sprintf(
                "    → created %d ใบ · %d รายการ · duplicate %d · noItems %d · noWarehouse %d · errors %d\n",
                $summary['createdCount'],
                $summary['totalLinesMigrated'],
                count($summary['skippedDuplicate']),
                count($summary['skippedNoItems']),
                count($summary['skippedNoWarehouse']),
                count($summary['errors'])
            ));

            $grand['warehouses']++;
            $grand['created']        += $summary['createdCount'];
            $grand['linesMigrated']  += $summary['totalLinesMigrated'];
            $grand['linesSkipped']   += $summary['totalLinesSkipped'];
            $grand['duplicate']      += count($summary['skippedDuplicate']);
            $grand['noItems']        += count($summary['skippedNoItems']);
            $grand['noWarehouse']    += count($summary['skippedNoWarehouse']);
            $grand['errors']         += count($summary['errors']);
            foreach (array_keys($summary['missingItemCodes']) as $code) {
                $grand['missingItemCodes'][$code] = true;
            }

            unset($parents, $childrenByParent, $existingV2Items, $existingOrderNos, $summary);
            gc_collect_cycles();
        }

        $elapsed = number_format(microtime(true) - $startedAt, 2);
        $this->stdout("\n========== สรุป ==========\n");
        $this->stdout(sprintf("คลังที่ประมวลผล : %d\n", $grand['warehouses']));
        $this->stdout(sprintf("ใบที่สร้าง       : %d\n", $grand['created']));
        $this->stdout(sprintf("Line ที่ย้าย     : %d (skip %d)\n", $grand['linesMigrated'], $grand['linesSkipped']));
        $this->stdout(sprintf("ใบ duplicate    : %d\n", $grand['duplicate']));
        $this->stdout(sprintf("ใบ noItems      : %d\n", $grand['noItems']));
        $this->stdout(sprintf("ใบ noWarehouse  : %d\n", $grand['noWarehouse']));
        $this->stdout(sprintf("ใบ errors       : %d\n", $grand['errors']));
        $this->stdout(sprintf("item_code ขาดใน V2 master : %d code\n", count($grand['missingItemCodes'])));
        if (!empty($grand['missingItemCodes'])) {
            $sample = array_slice(array_keys($grand['missingItemCodes']), 0, 30);
            $more = count($grand['missingItemCodes']) > 30 ? ' (+อีก ' . (count($grand['missingItemCodes']) - 30) . ')' : '';
            $this->stdout('    ตัวอย่าง: ' . implode(', ', $sample) . $more . "\n");
        }
        $this->stdout(sprintf("เวลา           : %s วินาที\n", $elapsed));

        return $grand['errors'] > 0 ? ExitCode::SOFTWARE : ExitCode::OK;
    }
}
