<?php

namespace app\modules\inventory\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use app\modules\inventoryV2\models\StockItem;

/**
 * This is the model class for table "stock_monthly_report".
 * (โครงสร้างเดียวกับที่ inventoryV2 ใช้ เพื่อให้ V1/V2 อ่าน opening/closing ต่อเนื่องกัน)
 *
 * @property int         $id
 * @property int         $report_year
 * @property int         $report_month
 * @property int         $warehouse_id
 * @property string      $item_code
 * @property string|null $unit_name
 * @property float       $opening_qty
 * @property float       $opening_value
 * @property float       $in_qty
 * @property float       $in_value
 * @property float       $out_sub_qty     จ่ายส่วนของ รพ.สต. (BRANCH) - qty
 * @property float       $out_sub_value   จ่ายส่วนของ รพ.สต. (BRANCH) - value
 * @property float       $out_hosp_qty    จ่ายส่วนของโรงพยาบาล (SUB) - qty
 * @property float       $out_hosp_value  จ่ายส่วนของโรงพยาบาล (SUB) - value
 * @property float       $total_out_qty
 * @property float       $total_out_value
 * @property float       $closing_qty
 * @property float       $closing_value
 * @property int|null    $created_at
 * @property int|null    $created_by
 * @property int|null    $adjusted_at           เวลาปรับยอดล่าสุด (unix ts)
 * @property int|null    $adjusted_by
 * @property string|null $adjustment_note
 * @property float|null  $original_closing_qty  ยอด qty ก่อนถูกปรับ
 * @property float|null  $original_closing_value ยอดมูลค่าก่อนถูกปรับ
 */
class StockMonthlyReport extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'stock_monthly_report';
    }

    public function rules()
    {
        return [
            [['report_year', 'report_month', 'warehouse_id', 'item_code'], 'required'],
            [['report_year', 'report_month', 'warehouse_id', 'created_at', 'created_by',
              'adjusted_at', 'adjusted_by'], 'integer'],
            [['opening_qty', 'opening_value', 'in_qty', 'in_value',
              'out_sub_qty', 'out_sub_value', 'out_hosp_qty', 'out_hosp_value',
              'total_out_qty', 'total_out_value',
              'closing_qty', 'closing_value',
              'original_closing_qty', 'original_closing_value'], 'number'],
            [['item_code'], 'string', 'max' => 50],
            [['unit_name'], 'string', 'max' => 100],
            [['adjustment_note'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_year' => 'ปี',
            'report_month' => 'เดือน',
            'warehouse_id' => 'คลังสินค้า',
            'item_code' => 'รหัสพัสดุ',
            'unit_name' => 'หน่วยนับ',
            'opening_qty' => 'ยอดยกมา (จำนวน)',
            'opening_value' => 'ยอดยกมา (มูลค่า)',
            'in_qty' => 'รับเข้า (จำนวน)',
            'in_value' => 'รับเข้า (มูลค่า)',
            'out_sub_qty' => 'จ่าย รพ.สต. (จำนวน)',
            'out_sub_value' => 'จ่าย รพ.สต. (มูลค่า)',
            'out_hosp_qty' => 'จ่ายโรงพยาบาล (จำนวน)',
            'out_hosp_value' => 'จ่ายโรงพยาบาล (มูลค่า)',
            'total_out_qty' => 'รวมจ่าย (จำนวน)',
            'total_out_value' => 'รวมจ่าย (มูลค่า)',
            'closing_qty' => 'คงเหลือ (จำนวน)',
            'closing_value' => 'คงเหลือ (มูลค่า)',
            'created_at' => 'สร้างเมื่อ',
            'created_by' => 'ผู้สร้าง',
            'adjusted_at' => 'ปรับยอดเมื่อ',
            'adjusted_by' => 'ผู้ปรับยอด',
            'adjustment_note' => 'เหตุผลการปรับยอด',
            'original_closing_qty' => 'ยอดคงเหลือเดิม (จำนวน)',
            'original_closing_value' => 'มูลค่าคงเหลือเดิม',
        ];
    }

    /**
     * แถวนี้ถูกปรับยอดแล้วหรือไม่ — ถ้า true แสดงว่า closing_* มาจากการปรับมือ
     * (จะถูกคุ้มครองให้ไม่ถูกทับเวลา re-generate)
     */
    public function isAdjusted()
    {
        return !empty($this->adjusted_at);
    }

    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    public function getItem()
    {
        return $this->hasOne(StockItem::class, ['item_code' => 'item_code']);
    }

    public function getAsset()
    {
        return $this->hasOne(Categorise::class, ['code' => 'item_code'])
            ->andOnCondition(['name' => 'asset_item']);
    }

    public static function thaiMonthName($month)
    {
        $names = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        return $names[(int) $month] ?? '';
    }

    public function getMonthLabel()
    {
        return self::thaiMonthName($this->report_month) . ' ' . ($this->report_year + 543);
    }

    /**
     * คำนวณและบันทึกสรุปคงคลังของเดือน (per item_code × warehouse) — Auto-bootstrap mode
     *
     * วิธีคำนวณ:
     *   - opening = closing ของเดือนก่อนใน stock_monthly_report (warehouse + item เดียวกัน)
     *               ถ้าไม่มี (เริ่มใช้ระบบครั้งแรก / item ใหม่) → คำนวณจาก stock_events
     *               ที่ movement_date < month_start (auto-bootstrap)
     *   - in/out = aggregate จาก stock_events ระหว่าง month_start..month_end
     *               (วิธีเดียวกับ /inventory/export-stock — buildStockAssetItemSql)
     *   - closing = opening + in - total_out (คำนวณ ไม่ scan stock_events ใหม่)
     *               เพื่อให้ chain ต่อเนื่องกับเดือนหน้า
     *
     * รายการที่ "ไม่มี activity ในเดือนนี้ แต่มี prev closing > 0" จะถูก carry over
     * เพื่อให้ V2 อ่าน opening เดือนถัดไปได้ครบ
     *
     * NOTE: FK fk_report_item_code ผูกกับ stock_item.item_code ดังนั้นจะ insert
     *       เฉพาะ asset_item ที่มีอยู่ใน stock_item เท่านั้น (รายการที่ไม่ตรงจะถูก skip)
     *
     * @param int        $reportYear
     * @param int        $reportMonth 1-12
     * @param int|null   $warehouseId กรองเฉพาะคลังหลัก (null = ทุกคลังหลัก MAIN)
     * @param string|null $assetTypeId กรองตามรหัสประเภทวัสดุ (categorise.code ของ asset_type)
     * @return array ['inserted' => int, 'skipped' => int]
     */
    public static function generateMonth($reportYear, $reportMonth, $warehouseId = null, $assetTypeId = null)
    {
        $reportYear  = (int) $reportYear;
        $reportMonth = (int) $reportMonth;
        if ($reportMonth < 1 || $reportMonth > 12) {
            throw new \InvalidArgumentException('report_month must be between 1 and 12');
        }

        if ($warehouseId !== null && $warehouseId !== '') {
            $warehouseIds = [(int) $warehouseId];
        } else {
            $warehouseIds = Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->select('id')
                ->column();
        }

        if (empty($warehouseIds)) {
            return ['inserted' => 0, 'skipped' => 0];
        }

        $totalInserted = 0;
        $totalSkipped  = 0;
        foreach ($warehouseIds as $whId) {
            $r = self::generateMonthForWarehouse($reportYear, $reportMonth, (int) $whId, $assetTypeId);
            $totalInserted += $r['inserted'];
            $totalSkipped  += $r['skipped'];
        }

        return ['inserted' => $totalInserted, 'skipped' => $totalSkipped];
    }

    protected static function generateMonthForWarehouse($reportYear, $reportMonth, $warehouseId, $assetTypeId = null)
    {
        $monthStart = sprintf('%04d-%02d-01 00:00:00', $reportYear, $reportMonth);
        $monthEnd   = date('Y-m-t 23:59:59', strtotime($monthStart));

        // ----- เดือนก่อนหน้า -----
        $prevMonth = $reportMonth - 1;
        $prevYear  = $reportYear;
        if ($prevMonth < 1) {
            $prevMonth += 12;
            $prevYear--;
        }

        // ----- Step 0: เก็บแถวที่ "ปรับยอด" ของเดือนนี้ไว้ (ก่อนจะ delete + insert ใหม่) -----
        $adjustedQuery = (new \yii\db\Query())
            ->from(['r' => self::tableName()])
            ->where([
                'r.report_year'  => $reportYear,
                'r.report_month' => $reportMonth,
                'r.warehouse_id' => $warehouseId,
            ])
            ->andWhere(['IS NOT', 'r.adjusted_at', null]);
        $adjustedRows = $adjustedQuery->indexBy('item_code')->all();

        // ----- Step 1: ดึง closing ของเดือนก่อนจาก stock_monthly_report -----
        $prevQuery = (new \yii\db\Query())
            ->select(['r.item_code', 'r.unit_name', 'r.closing_qty', 'r.closing_value'])
            ->from(['r' => self::tableName()])
            ->where([
                'r.report_year'  => $prevYear,
                'r.report_month' => $prevMonth,
                'r.warehouse_id' => $warehouseId,
            ]);

        if ($assetTypeId !== null && $assetTypeId !== '') {
            $prevQuery->innerJoin('stock_item si', 'si.item_code = r.item_code')
                ->andWhere(['si.category_id' => $assetTypeId]);
        }

        $prevClosings = $prevQuery->indexBy('item_code')->all();

        // ----- Step 2: คำนวณ in/out ระหว่างเดือน + opening จาก stock_events (สำหรับ bootstrap) -----
        $params = [
            ':warehouse_id' => $warehouseId,
            ':date_start'   => $monthStart,
            ':date_end'     => $monthEnd,
        ];
        $assetTypeCondition = '';
        if ($assetTypeId !== null && $assetTypeId !== '') {
            $assetTypeCondition = ' AND a.category_id = :asset_type_id ';
            $params[':asset_type_id'] = $assetTypeId;
        }

        // วิธีคำนวณยึดตาม StockEvent::buildStockAssetItemSql ของ /inventory/export-stock
        $sql = "
            SELECT
                a.code AS item_code,
                a.title AS asset_name,
                a.data_json->>'$.unit' AS unit_name,

                -- Bootstrap opening: ใช้เมื่อไม่มี prev_closing
                SUM(CASE
                    WHEN e.movement_date < :date_start
                         AND i.transaction_type = 'IN'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                    THEN CAST(i.qty AS DECIMAL(20,5))
                    WHEN e.movement_date < :date_start
                         AND i.transaction_type = 'OUT'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                    THEN -CAST(i.qty AS DECIMAL(20,5))
                    ELSE 0
                END) AS bootstrap_opening_qty,

                SUM(CASE
                    WHEN e.movement_date < :date_start
                         AND i.transaction_type = 'IN'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                    THEN CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                    WHEN e.movement_date < :date_start
                         AND i.transaction_type = 'OUT'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                    THEN -CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                    ELSE 0
                END) AS bootstrap_opening_value,

                SUM(CASE
                    WHEN e.movement_date BETWEEN :date_start AND :date_end
                         AND i.transaction_type = 'IN'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                    THEN CAST(i.qty AS DECIMAL(20,5))
                    ELSE 0
                END) AS in_qty,

                SUM(CASE
                    WHEN e.movement_date BETWEEN :date_start AND :date_end
                         AND i.transaction_type = 'IN'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                    THEN CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                    ELSE 0
                END) AS in_value,

                -- OUT ไปคลัง SUB = จ่ายส่วนของโรงพยาบาล
                SUM(CASE
                    WHEN e.movement_date BETWEEN :date_start AND :date_end
                         AND i.transaction_type = 'OUT'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'SUB'
                    THEN CAST(i.qty AS DECIMAL(20,5))
                    ELSE 0
                END) AS out_hosp_qty,

                SUM(CASE
                    WHEN e.movement_date BETWEEN :date_start AND :date_end
                         AND i.transaction_type = 'OUT'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'SUB'
                    THEN CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                    ELSE 0
                END) AS out_hosp_value,

                -- OUT ไปคลัง BRANCH = จ่ายส่วนของ รพ.สต.
                SUM(CASE
                    WHEN e.movement_date BETWEEN :date_start AND :date_end
                         AND i.transaction_type = 'OUT'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'BRANCH'
                    THEN CAST(i.qty AS DECIMAL(20,5))
                    ELSE 0
                END) AS out_sub_qty,

                SUM(CASE
                    WHEN e.movement_date BETWEEN :date_start AND :date_end
                         AND i.transaction_type = 'OUT'
                         AND i.order_status = 'success'
                         AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'BRANCH'
                    THEN CAST(i.qty AS DECIMAL(20,5)) * CAST(i.unit_price AS DECIMAL(20,5))
                    ELSE 0
                END) AS out_sub_value
            FROM categorise a
            LEFT JOIN stock_events i ON i.asset_item = a.code
            LEFT JOIN stock_events e ON e.id = i.category_id AND e.name = 'order'
            LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
            LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
            WHERE a.name = 'asset_item'
              AND e.warehouse_id = :warehouse_id
              {$assetTypeCondition}
            GROUP BY a.code
            HAVING (bootstrap_opening_qty <> 0 OR in_qty <> 0
                    OR out_hosp_qty <> 0 OR out_sub_qty <> 0)
        ";

        $monthRows = Yii::$app->db->createCommand($sql, $params)->queryAll();
        $monthMap = [];
        foreach ($monthRows as $r) {
            $monthMap[$r['item_code']] = $r;
        }

        // ----- Step 3: รวม prev_closings + monthMap + adjustedRows -----
        $allItemCodes = array_unique(array_merge(
            array_keys($prevClosings),
            array_keys($monthMap),
            array_keys($adjustedRows)
        ));

        if (empty($allItemCodes)) {
            $db = Yii::$app->db;
            $db->createCommand()->delete('stock_monthly_report', [
                'report_year'  => $reportYear,
                'report_month' => $reportMonth,
                'warehouse_id' => $warehouseId,
            ])->execute();
            return ['inserted' => 0, 'skipped' => 0];
        }

        // ----- Step 4: ตรวจสอบว่า item_code มีอยู่ใน stock_item (FK) -----
        $validItems = (new \yii\db\Query())
            ->select(['item_code'])
            ->from('stock_item')
            ->where(['item_code' => $allItemCodes])
            ->indexBy('item_code')
            ->all();

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete('stock_monthly_report', [
                    'report_year'  => $reportYear,
                    'report_month' => $reportMonth,
                    'warehouse_id' => $warehouseId,
                ])
                ->execute();

            $createdAt = time();
            $createdBy = isset(Yii::$app->user) && !Yii::$app->user->isGuest
                ? Yii::$app->user->id
                : null;

            $batch = [];
            $skipped = 0;
            foreach ($allItemCodes as $code) {
                if (!isset($validItems[$code])) {
                    $skipped++;
                    continue;
                }

                $month = $monthMap[$code] ?? null;
                $prev  = $prevClosings[$code] ?? null;

                // Opening: prev_closing ถ้ามี, ไม่งั้น bootstrap จาก stock_events
                if ($prev !== null) {
                    $openingQty   = (float) $prev['closing_qty'];
                    $openingValue = (float) $prev['closing_value'];
                } else {
                    $openingQty   = $month ? (float) $month['bootstrap_opening_qty'] : 0;
                    $openingValue = $month ? (float) $month['bootstrap_opening_value'] : 0;
                }

                $inQty       = $month ? (float) $month['in_qty']        : 0;
                $inValue     = $month ? (float) $month['in_value']      : 0;
                $outSubQty   = $month ? (float) $month['out_sub_qty']   : 0;
                $outSubValue = $month ? (float) $month['out_sub_value'] : 0;
                $outHospQty  = $month ? (float) $month['out_hosp_qty']  : 0;
                $outHospValue= $month ? (float) $month['out_hosp_value']: 0;
                $totalOutQty   = $outSubQty + $outHospQty;
                $totalOutValue = $outSubValue + $outHospValue;

                // closing = opening + in - out (chain ต่อเนื่อง)
                $calcClosingQty   = $openingQty + $inQty - $totalOutQty;
                $calcClosingValue = $openingValue + $inValue - $totalOutValue;

                // ถ้ารายการนี้เคย "ปรับยอด" มาก่อน → ใช้ค่าที่ผู้ใช้ปรับเป็น closing
                $adj = $adjustedRows[$code] ?? null;
                if ($adj !== null) {
                    $closingQty           = (float) $adj['closing_qty'];
                    $closingValue         = (float) $adj['closing_value'];
                    $adjustedAt           = (int) $adj['adjusted_at'];
                    $adjustedBy           = $adj['adjusted_by'] !== null ? (int) $adj['adjusted_by'] : null;
                    $adjustmentNote       = $adj['adjustment_note'];
                    $originalClosingQty   = $adj['original_closing_qty'] !== null
                        ? (float) $adj['original_closing_qty'] : $calcClosingQty;
                    $originalClosingValue = $adj['original_closing_value'] !== null
                        ? (float) $adj['original_closing_value'] : $calcClosingValue;
                } else {
                    $closingQty           = $calcClosingQty;
                    $closingValue         = $calcClosingValue;
                    $adjustedAt           = null;
                    $adjustedBy           = null;
                    $adjustmentNote       = null;
                    $originalClosingQty   = null;
                    $originalClosingValue = null;
                }

                // ข้ามรายการที่ไม่มีค่าใด ๆ เลย (และไม่ได้ถูกปรับยอด)
                if ($adj === null
                    && $openingQty == 0 && $inQty == 0 && $totalOutQty == 0 && $closingQty == 0) {
                    continue;
                }

                $unitName = ($month['unit_name'] ?? null) ?: ($prev['unit_name'] ?? ($adj['unit_name'] ?? null));

                $batch[] = [
                    $reportYear,
                    $reportMonth,
                    $warehouseId,
                    $code,
                    $unitName ?: null,
                    $openingQty,
                    $openingValue,
                    $inQty,
                    $inValue,
                    $outSubQty,
                    $outSubValue,
                    $outHospQty,
                    $outHospValue,
                    $totalOutQty,
                    $totalOutValue,
                    $closingQty,
                    $closingValue,
                    $createdAt,
                    $createdBy,
                    $adjustedAt,
                    $adjustedBy,
                    $adjustmentNote,
                    $originalClosingQty,
                    $originalClosingValue,
                ];
            }

            $inserted = 0;
            if (!empty($batch)) {
                $inserted = $db->createCommand()->batchInsert(
                    'stock_monthly_report',
                    [
                        'report_year', 'report_month', 'warehouse_id',
                        'item_code', 'unit_name',
                        'opening_qty', 'opening_value',
                        'in_qty', 'in_value',
                        'out_sub_qty', 'out_sub_value',
                        'out_hosp_qty', 'out_hosp_value',
                        'total_out_qty', 'total_out_value',
                        'closing_qty', 'closing_value',
                        'created_at', 'created_by',
                        'adjusted_at', 'adjusted_by', 'adjustment_note',
                        'original_closing_qty', 'original_closing_value',
                    ],
                    $batch
                )->execute();
            }

            $transaction->commit();
            return ['inserted' => $inserted, 'skipped' => $skipped];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
