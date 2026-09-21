<?php

namespace app\modules\laundry\services;

use Yii;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;

/**
 * ยอดคงเหลือผ้าแบบ perpetual (laundry_stock_balance) — อ่านเร็วคงที่ กันข้อมูลโต
 * ทุก write ต้องผ่าน applyEvent เพื่ออัปเดตยอดคู่กับ piece_event ในทรานแซกชันเดียว
 * department_id = 0 = ไม่ระบุ (เช่น คลังหลัก CLEAN)
 */
class LaundryBalance
{
    /** ปรับยอดคงเหลือ (upsert item×location×dept += delta) */
    public static function adjust(Connection $db, int $itemId, string $location, int $deptId, int $delta): void
    {
        if ($delta === 0 || $location === '') {
            return;
        }
        $db->createCommand()->upsert('{{%laundry_stock_balance}}', [
            'item_id' => $itemId, 'location' => $location, 'department_id' => $deptId,
            'qty' => $delta, 'updated_at' => date('Y-m-d H:i:s'),
        ], [
            'qty' => new Expression('[[qty]] + (' . (int) $delta . ')'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    /**
     * insert piece_event + อัปเดตยอดคงเหลือ (เรียกภายในทรานแซกชันของผู้เรียก)
     * @return int event id
     */
    public static function applyEvent(Connection $db, array $e): int
    {
        $db->createCommand()->insert('{{%laundry_piece_event}}', $e)->execute();
        $id = (int) $db->getLastInsertID();
        if (($e['status'] ?? '') === 'CONFIRMED') {
            $qty = (int) $e['qty'];
            if (!empty($e['to_location'])) {
                self::adjust($db, (int) $e['item_id'], (string) $e['to_location'], (int) ($e['to_department_id'] ?? 0), $qty);
            }
            if (!empty($e['from_location'])) {
                self::adjust($db, (int) $e['item_id'], (string) $e['from_location'], (int) ($e['from_department_id'] ?? 0), -$qty);
            }
        }
        return $id;
    }

    /** ยอดคงเหลือของ item ใน location (เช่น CLEAN=คลังหลัก) */
    public static function balance(int $itemId, string $location, int $deptId = 0): int
    {
        return (int) (new Query())->select('qty')->from('{{%laundry_stock_balance}}')
            ->where(['item_id' => $itemId, 'location' => $location, 'department_id' => $deptId])->scalar();
    }

    /** ยอดคลังหลักรวม (ชิ้น) */
    public static function cleanTotal(): int
    {
        return (int) (new Query())->from('{{%laundry_stock_balance}}')
            ->where(['location' => 'CLEAN'])->sum('qty');
    }

    /** ยอดคลังหลักรายประเภท: item_id => qty (เฉพาะที่ > 0) */
    public static function cleanByItem(): array
    {
        $rows = (new Query())->select(['item_id', 'qty'])->from('{{%laundry_stock_balance}}')
            ->where(['location' => 'CLEAN'])->andWhere(['>', 'qty', 0])->all();
        return array_column($rows, 'qty', 'item_id');
    }

    /** ยอดรวมของ location หนึ่ง (ชิ้น) — สำหรับ backlog dashboard */
    public static function locationTotal(string $location): int
    {
        return (int) (new Query())->from('{{%laundry_stock_balance}}')
            ->where(['location' => $location])->sum('qty');
    }
}
