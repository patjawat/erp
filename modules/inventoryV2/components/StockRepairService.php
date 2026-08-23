<?php

namespace app\modules\inventoryV2\components;

use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use Yii;
use yii\db\Query;

/** Controlled stock reconciliation. Every write must originate from a fresh plan. */
class StockRepairService
{
    private const BLOCKING_ISSUES = [
        'duplicate_balance', 'negative_fifo', 'negative_balance', 'fifo_over_received',
        'orphan_balance', 'orphan_allocation', 'missing_allocation', 'history_only_edit',
    ];

    public static function plan(int $warehouseId, string $itemCode, string $scope, string $lotNumber = '', ?float $physicalQty = null): array
    {
        $itemCode = trim($itemCode);
        $scope = $scope === 'item' ? 'item' : 'lot';
        $lotNumber = self::lot($lotNumber);
        $scan = StockHealthService::scan([$warehouseId], ['include_healthy' => true]);
        $itemRow = null;
        $lotRows = [];
        foreach ($scan['rows'] as $row) {
            if ($row['item_code'] !== $itemCode) continue;
            if ($row['scope'] === 'item') $itemRow = $row;
            if ($row['scope'] === 'lot') $lotRows[$row['lot_number']] = $row;
        }
        if (!$itemRow) return self::refused('ไม่พบข้อมูลสรุปของวัสดุในคลังนี้');
        if ($physicalQty !== null) {
            return self::physicalCountPlan($warehouseId, $itemCode, $scope, $physicalQty, $itemRow, $lotRows);
        }
        if ($itemRow['ledger_qty'] === null
            && abs((float) $itemRow['balance_qty'] - (float) $itemRow['fifo_qty']) <= StockHealthService::EPSILON
            && ($scope === 'item' || (isset($lotRows[$lotNumber])
                && abs((float) $lotRows[$lotNumber]['balance_qty'] - (float) $lotRows[$lotNumber]['fifo_qty']) <= StockHealthService::EPSILON))) {
            return self::refused('Balance และ FIFO ตรงกัน จึงไม่มียอดสต็อกที่ต้องซ่อม แต่ไม่พบประวัติที่เข้าเกณฑ์คำนวณ กรุณาตรวจเอกสารนำเข้าหรือข้อมูลที่ย้ายจากระบบเดิม', $itemRow);
        }
        if ($itemRow['ledger_qty'] !== null
            && abs((float) $itemRow['balance_qty'] - (float) $itemRow['fifo_qty']) <= StockHealthService::EPSILON
            && abs((float) $itemRow['ledger_qty'] - (float) $itemRow['balance_qty']) > StockHealthService::EPSILON
            && ($scope === 'item' || (isset($lotRows[$lotNumber])
                && abs((float) $lotRows[$lotNumber]['balance_qty'] - (float) $lotRows[$lotNumber]['fifo_qty']) <= StockHealthService::EPSILON))) {
            $variance = (float) $itemRow['balance_qty'] - (float) $itemRow['ledger_qty'];
            if ($scope === 'item') {
                $ledgerBlockers = array_values(array_diff(self::BLOCKING_ISSUES, ['history_only_edit']));
                $unsafe = array_values(array_intersect($ledgerBlockers, $itemRow['issues']));
                if ($unsafe) {
                    return self::refused('ยังปรับประวัติไม่ได้ เพราะพบ: ' . implode(', ', array_map([self::class, 'issueLabel'], $unsafe)), $itemRow);
                }
                $operation = self::operation('ledger', 'รวมทุก Lot', (float) $itemRow['ledger_qty'], (float) $itemRow['balance_qty']);
                $snapshot = ['warehouse_id' => $warehouseId, 'item_code' => $itemCode, 'scope' => $scope,
                    'lot_number' => null, 'mode' => 'sync_ledger', 'operations' => [$operation]];
                $snapshot['fingerprint'] = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
                return [
                    'allowed' => true,
                    'message' => sprintf('Balance และ FIFO ยืนยันตรงกันที่ %s: พร้อมปรับเฉพาะประวัติ %+.2f โดยไม่แตะ stock/FIFO',
                        number_format((float) $itemRow['balance_qty'], 2), $variance),
                    'plan' => $snapshot,
                    'evidence' => $itemRow,
                ];
            }
            return self::refused('การปรับประวัติทำได้เฉพาะระดับรวมทุก Lot กรุณาเลือกขอบเขตรายการสินค้า', $itemRow);
        }

        $operations = [];
        $mode = 'manual_count_required';
        if ($scope === 'lot') {
            $row = $lotRows[$lotNumber] ?? null;
            if (!$row) return self::refused('ไม่พบข้อมูล Lot ที่ระบุ');
            $blocked = array_values(array_intersect(self::BLOCKING_ISSUES, $row['issues']));
            if ($blocked) {
                return self::refused('พบสัญญาณที่ต้องตรวจเอกสารก่อน: ' . implode(', ', array_map([self::class, 'issueLabel'], $blocked)), $itemRow);
            }
            $ledgerMatchesBalance = $itemRow['ledger_qty'] !== null
                && abs((float) $itemRow['ledger_qty'] - (float) $itemRow['balance_qty']) <= StockHealthService::EPSILON;
            $ledgerMatchesFifo = $itemRow['ledger_qty'] !== null
                && abs((float) $itemRow['ledger_qty'] - (float) $itemRow['fifo_qty']) <= StockHealthService::EPSILON;
            $balanceDiffersFromFifo = abs((float) $itemRow['balance_qty'] - (float) $itemRow['fifo_qty']) > StockHealthService::EPSILON;
            if ($ledgerMatchesBalance && $balanceDiffersFromFifo && $row['source_rows'] > 0
                && $row['balance_rows'] === 1 && $row['balance_qty'] >= 0
                && $row['balance_qty'] <= $row['received_qty'] + StockHealthService::EPSILON
                && abs($row['balance_qty'] - $row['fifo_qty']) > StockHealthService::EPSILON) {
                $mode = 'sync_fifo';
                $operations[] = self::operation('fifo', $lotNumber, $row['fifo_qty'], $row['balance_qty']);
            } elseif ($ledgerMatchesFifo && $balanceDiffersFromFifo
                && $row['balance_rows'] <= 1 && $row['fifo_qty'] >= 0
                && $row['fifo_qty'] <= $row['received_qty'] + StockHealthService::EPSILON
                && abs($row['balance_qty'] - $row['fifo_qty']) > StockHealthService::EPSILON) {
                $mode = 'sync_balance';
                $operations[] = self::operation('balance', $lotNumber, $row['balance_qty'], $row['fifo_qty']);
            }
        } else {
            $itemEvidenceMatches = $itemRow['ledger_qty'] !== null
                && abs((float) $itemRow['ledger_qty'] - (float) $itemRow['fifo_qty']) <= StockHealthService::EPSILON;
            $safeLots = !empty($lotRows);
            foreach ($lotRows as $row) {
                if ($row['balance_rows'] > 1 || array_intersect(self::BLOCKING_ISSUES, $row['issues'])
                    || $row['fifo_qty'] < 0 || $row['fifo_qty'] > $row['received_qty'] + StockHealthService::EPSILON) {
                    $safeLots = false;
                    break;
                }
            }
            if ($itemEvidenceMatches && $safeLots
                && abs((float) $itemRow['ledger_qty'] - (float) $itemRow['balance_qty']) > StockHealthService::EPSILON) {
                $mode = 'sync_balance';
                foreach ($lotRows as $row) {
                    if (abs($row['balance_qty'] - $row['fifo_qty']) > StockHealthService::EPSILON) {
                        $operations[] = self::operation('balance', $row['lot_number'], $row['balance_qty'], $row['fifo_qty']);
                    }
                }
            }
        }

        if (empty($operations)) {
            return self::refused(sprintf(
                'หลักฐานยังไม่ยืนยันค่าเดียวกัน (ประวัติ %s, Balance %s, FIFO %s) ต้องตรวจเอกสารหรือตรวจนับจริง',
                number_format((float) $itemRow['ledger_qty'], 2),
                number_format((float) $itemRow['balance_qty'], 2),
                number_format((float) $itemRow['fifo_qty'], 2)
            ), $itemRow);
        }
        $snapshot = ['warehouse_id' => $warehouseId, 'item_code' => $itemCode, 'scope' => $scope, 'lot_number' => $scope === 'lot' ? $lotNumber : null, 'mode' => $mode, 'operations' => $operations];
        $snapshot['fingerprint'] = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
        return ['allowed' => true, 'message' => 'พร้อมดำเนินการภายใต้ transaction', 'plan' => $snapshot, 'evidence' => $itemRow];
    }

    public static function execute(int $warehouseId, string $itemCode, string $scope, string $lotNumber, string $fingerprint, string $reason, ?int $userId, ?float $physicalQty = null): array
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 10) throw new \InvalidArgumentException('กรุณาระบุเหตุผลอย่างน้อย 10 ตัวอักษร');
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            // Health repair must also support legacy history whose material
            // master has already been removed. Normal stock mutations remain
            // fail-closed when the registry row is missing.
            InventoryService::lockStockPool(
                $itemCode,
                $warehouseId,
                $scope === 'lot' ? self::lot($lotNumber) : null,
                true
            );
            $fresh = self::plan($warehouseId, $itemCode, $scope, $lotNumber, $physicalQty);
            if (!$fresh['allowed']) throw new \RuntimeException($fresh['message']);
            if (!hash_equals((string) $fresh['plan']['fingerprint'], $fingerprint)) {
                throw new \RuntimeException('ข้อมูลเปลี่ยนหลัง Dry-run กรุณาตรวจสอบและยืนยันรายการใหม่');
            }
            foreach ($fresh['plan']['operations'] as $operation) {
                if ($operation['target'] === 'fifo') self::applyFifo($warehouseId, $itemCode, $operation);
                if ($operation['target'] === 'balance') self::applyBalance($warehouseId, $itemCode, $operation);
                if ($operation['target'] === 'ledger') self::applyLedger($warehouseId, $itemCode, $operation, $reason, $userId);
            }
            $after = self::verifyAfter($warehouseId, $itemCode, $fresh['plan']['operations']);
            $db->createCommand()->insert('{{%stock_repair_audit}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                'warehouse_id' => $warehouseId, 'item_code' => $itemCode,
                'scope' => $scope, 'lot_number' => $scope === 'lot' ? self::lot($lotNumber) : null,
                'repair_mode' => $fresh['plan']['mode'], 'reason' => $reason,
                'before_json' => json_encode($fresh, JSON_UNESCAPED_UNICODE),
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE),
                'fingerprint' => $fingerprint, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId, 'updated_by' => $userId,
            ])->execute();
            $auditId = (int) $db->getLastInsertID();
            $tx->commit();
            return ['success' => true, 'audit_id' => $auditId, 'before' => $fresh, 'after' => $after];
        } catch (\Throwable $e) {
            if ($tx->isActive) $tx->rollBack();
            throw $e;
        }
    }

    private static function applyFifo(int $warehouseId, string $itemCode, array $operation): void
    {
        $sources = self::sourceQuery($warehouseId, $itemCode, $operation['lot_number'])
            ->orderBy(['so.order_date' => $operation['delta'] < 0 ? SORT_ASC : SORT_DESC, 'sd.id' => $operation['delta'] < 0 ? SORT_ASC : SORT_DESC])
            ->all();
        $remaining = abs((float) $operation['delta']);
        foreach ($sources as $source) {
            if ($remaining <= StockHealthService::EPSILON) break;
            $capacity = $operation['delta'] > 0
                ? max(0.0, abs((float) $source->qty) - (float) $source->remain_qty)
                : max(0.0, (float) $source->remain_qty);
            $take = min($remaining, $capacity);
            if ($take <= 0) continue;
            $source->remain_qty += $operation['delta'] > 0 ? $take : -$take;
            if (!$source->save(false, ['remain_qty'])) throw new \RuntimeException('ไม่สามารถปรับ FIFO ต้นทางได้');
            $remaining -= $take;
        }
        if ($remaining > StockHealthService::EPSILON) throw new \RuntimeException('ความจุของ Lot ต้นทางไม่เพียงพอสำหรับซ่อม FIFO');
    }

    private static function physicalCountPlan(int $warehouseId, string $itemCode, string $scope, float $physicalQty, array $itemRow, array $lotRows): array
    {
        if ($scope !== 'item') return self::refused('ยอดตรวจนับรวมต้องบันทึกที่ขอบเขต “รวมทุก Lot”', $itemRow);
        if ($physicalQty < -StockHealthService::EPSILON) return self::refused('ยอดตรวจนับจริงต้องไม่ติดลบ', $itemRow);
        if (abs($physicalQty - (float) $itemRow['balance_qty']) > StockHealthService::EPSILON) {
            return self::refused(sprintf(
                'ยอดตรวจนับ %.2f ไม่เท่ากับ Balance %.2f จึงต้องระบุยอดตรวจนับแยกแต่ละ Lot ก่อน เพื่อไม่ให้ระบบเดาว่าต้องเพิ่มหรือลด Lot ใด',
                $physicalQty, (float) $itemRow['balance_qty']
            ), $itemRow);
        }
        $unsafeCodes = ['duplicate_balance', 'negative_balance', 'fifo_over_received', 'orphan_allocation'];
        $unsafe = array_values(array_intersect($unsafeCodes, $itemRow['issues']));
        if ($unsafe) return self::refused('ยังซ่อมจากยอดตรวจนับไม่ได้ เพราะพบ: ' . implode(', ', array_map([self::class, 'issueLabel'], $unsafe)), $itemRow);

        $operations = [];
        $desiredBalances = [];
        foreach ($lotRows as $row) {
            if ($row['balance_rows'] > 1 || $row['balance_qty'] < -StockHealthService::EPSILON) {
                return self::refused('Lot ' . $row['lot_number'] . ' ไม่มีหลักฐานเพียงพอสำหรับจัดสรรยอดตรวจนับ', $itemRow);
            }
            $desiredBalances[$row['lot_number']] = (float) $row['balance_qty'];
        }

        // Balance ที่อยู่ใน Lot ลอย (ไม่มีเอกสารรับต้นทาง) ห้ามสร้าง FIFO ลอยตาม
        // ให้ย้ายไป Lot จริงที่มี capacity รองรับ โดยคงยอดรวมตามผลตรวจนับ
        foreach ($lotRows as $orphanRow) {
            if ($orphanRow['balance_qty'] <= StockHealthService::EPSILON || $orphanRow['source_rows'] > 0) continue;
            $remaining = (float) $orphanRow['balance_qty'];
            foreach ($lotRows as $sourceRow) {
                if ($remaining <= StockHealthService::EPSILON) break;
                if ($sourceRow['source_rows'] <= 0 || $sourceRow['lot_number'] === $orphanRow['lot_number']) continue;
                $capacity = max(0.0, (float) $sourceRow['received_qty'] - (float) $desiredBalances[$sourceRow['lot_number']]);
                $move = min($remaining, $capacity);
                if ($move <= StockHealthService::EPSILON) continue;
                $desiredBalances[$sourceRow['lot_number']] += $move;
                $desiredBalances[$orphanRow['lot_number']] -= $move;
                $remaining -= $move;
            }
            if ($remaining > StockHealthService::EPSILON) {
                return self::refused('Lot ลอย ' . $orphanRow['lot_number'] . ' จำนวน ' . number_format($remaining, 2)
                    . ' ไม่มี Lot ต้นทางที่มีความจุรองรับ ต้องระบุยอดตรวจนับแยก Lot', $itemRow);
            }
        }

        foreach ($lotRows as $row) {
            $desired = (float) $desiredBalances[$row['lot_number']];
            if ($row['source_rows'] > 0 && $desired > $row['received_qty'] + StockHealthService::EPSILON) {
                return self::refused('Lot ' . $row['lot_number'] . ' มียอดเป้าหมายเกินยอดรับเข้าสะสม', $itemRow);
            }
            if (abs((float) $row['balance_qty'] - $desired) > StockHealthService::EPSILON) {
                $operations[] = self::operation('balance', $row['lot_number'], (float) $row['balance_qty'], $desired);
            }
        }
        foreach ($lotRows as $row) {
            $desired = (float) $desiredBalances[$row['lot_number']];
            if (abs((float) $row['fifo_qty'] - $desired) > StockHealthService::EPSILON) {
                if ($desired > StockHealthService::EPSILON && $row['source_rows'] <= 0) {
                    return self::refused('Lot ' . $row['lot_number'] . ' ยังไม่มีเอกสารต้นทางสำหรับสร้าง FIFO', $itemRow);
                }
                $operations[] = self::operation('fifo', $row['lot_number'], (float) $row['fifo_qty'], $desired);
            }
        }
        if ($itemRow['ledger_qty'] === null) return self::refused('ไม่พบยอดประวัติที่ใช้คำนวณ จึงยังซ่อมจากยอดตรวจนับไม่ได้', $itemRow);
        if (abs((float) $itemRow['ledger_qty'] - $physicalQty) > StockHealthService::EPSILON) {
            $operations[] = self::operation('ledger', 'รวมทุก Lot', (float) $itemRow['ledger_qty'], $physicalQty);
        }
        if (!$operations) return self::refused('ทั้งประวัติ Balance และ FIFO ตรงกับยอดตรวจนับแล้ว ไม่ต้องดำเนินการ', $itemRow);
        $snapshot = [
            'warehouse_id' => $warehouseId, 'item_code' => $itemCode, 'scope' => 'item', 'lot_number' => null,
            'mode' => 'physical_count_to_balance', 'physical_qty' => round($physicalQty, 4), 'operations' => $operations,
        ];
        $snapshot['fingerprint'] = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
        return ['allowed' => true, 'message' => 'พร้อมปรับประวัติและ FIFO ให้ตรงยอดตรวจนับจริง โดยคง Balance ที่ตรวจนับยืนยันแล้ว', 'plan' => $snapshot, 'evidence' => $itemRow];
    }

    private static function applyBalance(int $warehouseId, string $itemCode, array $operation): void
    {
        $rows = StockBalance::find()->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode, 'lot_number' => $operation['lot_number']])->all();
        if (count($rows) > 1) throw new \RuntimeException('พบ Balance ซ้ำ จึงยกเลิกการซ่อม');
        $balance = $rows[0] ?? new StockBalance(['warehouse_id' => $warehouseId, 'item_code' => $itemCode, 'lot_number' => $operation['lot_number']]);
        $balance->balance_qty = (float) $operation['after'];
        if (!$balance->save()) throw new \RuntimeException('ไม่สามารถบันทึก Balance ที่ซ่อมแล้วได้');
    }

    /** บันทึกรายการกระทบเฉพาะประวัติ เมื่อ Balance และ FIFO เป็นหลักฐานร่วมที่ตรงกันแล้ว */
    private static function applyLedger(int $warehouseId, string $itemCode, array $operation, string $reason, ?int $userId): void
    {
        $now = date('Y-m-d H:i:s');
        $delta = (float) $operation['delta'];
        if (abs($delta) <= StockHealthService::EPSILON) return;
        $orderNo = 'RECON-LEDGER-' . date('YmdHis') . '-' . substr(Yii::$app->getSecurity()->generateRandomString(6), 0, 6);
        $order = new StockOrder([
            'order_no' => $orderNo,
            'order_type' => StockOrder::ORDER_TYPE_ADJUST,
            'source_type' => StockOrder::ORDER_TYPE_ADJUST,
            'order_date' => $now,
            'main_warehouse_id' => $warehouseId,
            'status' => StockOrder::STATUS_CONFIRMED,
            'ref' => $reason,
            'data_json' => [
                'adjust_source' => 'stock-health-controlled-repair',
                'adjust_mode' => 'history_reconcile',
                'history_only_reverse' => 1,
                'ledger_before' => (float) $operation['before'],
                'ledger_after' => (float) $operation['after'],
                'balance_fifo_evidence' => (float) $operation['after'],
            ],
            'created_at' => $now, 'updated_at' => $now, 'created_by' => $userId, 'updated_by' => $userId,
        ]);
        if (!$order->save(false)) throw new \RuntimeException('ไม่สามารถสร้างเอกสารปรับประวัติได้');
        $detail = new StockDetail([
            'stock_order_id' => $order->id, 'item_code' => $itemCode, 'qty' => $delta,
            'unit_price' => 0, 'lot_number' => $orderNo, 'remain_qty' => 0,
            'data_json' => ['history_only_reverse' => 1, 'history_reconcile' => 1, 'reason' => $reason],
            'created_at' => $now, 'updated_at' => $now, 'created_by' => $userId, 'updated_by' => $userId,
        ]);
        if (!$detail->save(false)) throw new \RuntimeException('ไม่สามารถบันทึกรายการปรับประวัติได้');
    }

    private static function verifyAfter(int $warehouseId, string $itemCode, array $operations): array
    {
        $measurements = [];
        foreach ($operations as $operation) {
            $actual = $operation['target'] === 'fifo'
                ? (float) self::sourceQuery($warehouseId, $itemCode, $operation['lot_number'])->sum('sd.remain_qty')
                : ($operation['target'] === 'ledger'
                    ? self::ledgerQty($warehouseId, $itemCode)
                    : (float) StockBalance::find()->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode, 'lot_number' => $operation['lot_number']])->sum('balance_qty'));
            if (abs($actual - (float) $operation['after']) > StockHealthService::EPSILON) {
                throw new \RuntimeException('ตรวจสอบหลังซ่อมไม่ผ่าน ระบบได้ยกเลิกการเปลี่ยนแปลงทั้งหมด');
            }
            $measurements[] = ['target' => $operation['target'], 'lot_number' => $operation['lot_number'], 'expected' => $operation['after'], 'actual' => round($actual, 4)];
        }
        return ['verified' => true, 'measurements' => $measurements, 'verified_at' => date('Y-m-d H:i:s')];
    }

    private static function ledgerQty(int $warehouseId, string $itemCode): float
    {
        $scan = StockHealthService::scan([$warehouseId], ['include_healthy' => true]);
        foreach ($scan['rows'] as $row) {
            if ($row['scope'] === 'item' && $row['item_code'] === $itemCode) {
                return (float) ($row['ledger_qty'] ?? 0);
            }
        }
        throw new \RuntimeException('ไม่พบยอดประวัติหลังซ่อม');
    }

    private static function sourceQuery(int $warehouseId, string $itemCode, string $lotNumber)
    {
        return StockDetail::find()->alias('sd')->joinWith(['stockOrder so'])->where([
            'sd.item_code' => $itemCode, 'sd.lot_number' => $lotNumber, 'so.status' => StockOrder::STATUS_CONFIRMED,
        ])->andWhere(['or',
            ['and', ['so.main_warehouse_id' => $warehouseId], ['or', ['so.order_type' => StockOrder::ORDER_TYPE_IN], ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]]]],
            ['and', ['so.sub_warehouse_id' => $warehouseId], ['so.order_type' => [StockOrder::ORDER_TYPE_TRANSFER, StockOrder::ORDER_TYPE_OUT]], ['>', 'sd.qty', 0]],
        ]);
    }

    private static function operation(string $target, string $lot, float $before, float $after): array
    {
        return ['target' => $target, 'lot_number' => $lot, 'before' => round($before, 4), 'after' => round($after, 4), 'delta' => round($after - $before, 4)];
    }

    private static function refused(string $message, ?array $evidence = null): array
    {
        return ['allowed' => false, 'message' => $message, 'plan' => null, 'evidence' => $evidence];
    }

    private static function lot(string $lot): string
    {
        $lot = trim($lot);
        return $lot === '' ? '-' : $lot;
    }

    private static function issueLabel(string $issue): string
    {
        return [
            'duplicate_balance' => 'พบ Balance ซ้ำ',
            'negative_fifo' => 'FIFO ติดลบ',
            'negative_balance' => 'Balance ติดลบ',
            'fifo_over_received' => 'FIFO มากกว่ายอดรับเข้า',
            'orphan_balance' => 'มี Balance แต่ไม่พบต้นทาง',
            'orphan_allocation' => 'Allocation ชี้ต้นทางที่หาย',
            'missing_allocation' => 'ใบจ่ายไม่มี Allocation',
            'history_only_edit' => 'เคยแก้เฉพาะประวัติ',
        ][$issue] ?? $issue;
    }
}
