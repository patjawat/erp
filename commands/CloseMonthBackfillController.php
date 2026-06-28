<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;
use app\modules\inventoryV2\controllers\ReportController;
use app\modules\inventoryV2\models\StockMonthlyReport;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;

/**
 * Backfill snapshot รายเดือน (stock_monthly_report) ย้อนหลังทุกคลัง × ทุกเดือน
 * ตั้งแต่ stock_order แรก จนถึงเดือนก่อนปัจจุบัน
 *
 * ใช้เมื่อ:
 *   - เปิดใช้งานระบบปิดเดือนครั้งแรก (ยังไม่เคยปิดเดือนใด)
 *   - หลัง schema เปลี่ยน (เช่นเพิ่มคอลัมน์ adjust_*) ต้อง re-generate snapshot ใหม่
 *   - dashboard เดือนเก่าควรอ่านจาก snapshot แต่ยังไม่มี
 *
 * วิธีใช้:
 *   php yii close-month-backfill/run                          # ทุกคลัง MAIN, ทุกเดือนที่ขาด
 *   php yii close-month-backfill/run --warehouse-id=12        # คลังเดียว
 *   php yii close-month-backfill/run --force                  # re-close แม้มี snapshot อยู่แล้ว
 *   php yii close-month-backfill/run --from-year=2024 --from-month=10  # เริ่มจากเดือนที่กำหนด
 */
class CloseMonthBackfillController extends Controller
{
    /** @var int|null คลังที่จะ backfill (null = ทุกคลัง MAIN) */
    public $warehouseId = null;

    /** @var bool re-close เดือนที่มี snapshot อยู่แล้วด้วย */
    public $force = false;

    /** @var int|null ปี ค.ศ. เริ่มต้น (null = หาเดือนแรกที่มี stock_order) */
    public $fromYear = null;

    /** @var int|null เดือนเริ่มต้น 1-12 (ใช้ร่วมกับ from-year) */
    public $fromMonth = null;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['warehouseId', 'force', 'fromYear', 'fromMonth']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['w' => 'warehouseId', 'f' => 'force']);
    }

    public function actionRun()
    {
        $warehouseIds = $this->resolveWarehouseIds();
        if (empty($warehouseIds)) {
            $this->stderr("ไม่พบคลัง MAIN ในระบบ\n");
            return ExitCode::DATAERR;
        }

        $now = time();
        $cutoffYear = (int) date('Y', $now);
        $cutoffMonth = (int) date('n', $now);
        // ปิดเฉพาะเดือนที่ผ่านมาแล้ว (ไม่ปิดเดือนปัจจุบัน)
        $cutoffMonth--;
        if ($cutoffMonth < 1) {
            $cutoffMonth = 12;
            $cutoffYear--;
        }

        $totalWarehouses = 0;
        $totalMonths = 0;
        $totalItems = 0;

        foreach ($warehouseIds as $warehouseId) {
            $w = Warehouse::findOne($warehouseId);
            $name = $w ? $w->warehouse_name : ('#' . $warehouseId);
            $this->stdout("\n=== คลัง: {$name} (id={$warehouseId}) ===\n");

            [$startYear, $startMonth] = $this->resolveStartMonth($warehouseId);
            if ($startYear === null) {
                $this->stdout("  [SKIP] ไม่มี stock_order ในคลังนี้\n");
                continue;
            }
            $this->stdout("  ช่วง: {$startYear}-" . str_pad($startMonth, 2, '0', STR_PAD_LEFT)
                . " → {$cutoffYear}-" . str_pad($cutoffMonth, 2, '0', STR_PAD_LEFT) . "\n");

            $existingKeys = $this->loadExistingSnapshotKeys($warehouseId);

            $year = $startYear;
            $month = $startMonth;
            $warehouseMonthCount = 0;
            $warehouseItemCount = 0;

            while ($year < $cutoffYear || ($year === $cutoffYear && $month <= $cutoffMonth)) {
                $key = $year . '-' . $month;
                $alreadyClosed = isset($existingKeys[$key]);
                if ($alreadyClosed && !$this->force) {
                    $this->stdout("  [SKIP] {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . " (มี snapshot แล้ว)\n");
                } else {
                    $result = ReportController::closeMonthForWarehouse($warehouseId, $year, $month);
                    $count = (int) ($result['count'] ?? 0);
                    $tag = $alreadyClosed ? '[RECLOSE]' : '[CLOSE]';
                    $this->stdout("  {$tag} {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . " → {$count} items\n");
                    $warehouseMonthCount++;
                    $warehouseItemCount += $count;
                }

                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }
            }

            $totalWarehouses++;
            $totalMonths += $warehouseMonthCount;
            $totalItems += $warehouseItemCount;
            $this->stdout("  → ปิด {$warehouseMonthCount} เดือน, {$warehouseItemCount} items\n");
        }

        $this->stdout("\n=== สรุป ===\n");
        $this->stdout("คลังที่ประมวลผล: {$totalWarehouses}\n");
        $this->stdout("เดือนที่ปิดใหม่: {$totalMonths}\n");
        $this->stdout("Items ทั้งหมด:   {$totalItems}\n");
        return ExitCode::OK;
    }

    protected function resolveWarehouseIds()
    {
        if ($this->warehouseId !== null) {
            return [(int) $this->warehouseId];
        }
        return Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['id' => SORT_ASC])
            ->select('id')
            ->column();
    }

    /**
     * หาปี/เดือนแรกที่ต้อง backfill — ใช้ --from-year/--from-month ถ้ามี
     * ไม่งั้นหาจาก stock_order แรกของคลังนั้น
     * @return array [year|null, month|null]
     */
    protected function resolveStartMonth($warehouseId)
    {
        if ($this->fromYear !== null) {
            return [(int) $this->fromYear, (int) ($this->fromMonth ?? 1)];
        }
        $firstDate = (new Query())
            ->select('MIN(order_date)')
            ->from(StockOrder::tableName())
            ->where(['main_warehouse_id' => $warehouseId])
            ->scalar();
        if (!$firstDate) {
            return [null, null];
        }
        $ts = strtotime($firstDate);
        return [(int) date('Y', $ts), (int) date('n', $ts)];
    }

    /**
     * คืน map [year-month => true] ของเดือนที่มี snapshot อยู่แล้วในคลังนี้
     */
    protected function loadExistingSnapshotKeys($warehouseId)
    {
        $rows = (new Query())
            ->select(['report_year', 'report_month'])
            ->from(StockMonthlyReport::tableName())
            ->where(['warehouse_id' => $warehouseId])
            ->distinct()
            ->all();
        $keys = [];
        foreach ($rows as $r) {
            $keys[(int) $r['report_year'] . '-' . (int) $r['report_month']] = true;
        }
        return $keys;
    }
}
