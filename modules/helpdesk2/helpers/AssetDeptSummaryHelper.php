<?php

namespace app\modules\helpdesk2\helpers;

use yii\db\Query;
use yii\db\Expression;

/**
 * สรุปทะเบียนครุภัณฑ์ "รายหน่วยงาน" สำหรับหน้าทรัพย์สินของศูนย์งานซ่อม (helpdesk2)
 *
 * ใช้ scope เดียวกับหน้าทะเบียน (asset_group_id=4 + ชนิดครุภัณฑ์ของศูนย์)
 * คืนสรุปต่อหน่วยงาน: จำนวน/สภาพ/ส่งซ่อม/รอจำหน่าย/มูลค่า พร้อมก้อน "ไม่ระบุหน่วยงาน"
 * เพื่อให้ตรวจสอบได้ง่ายว่าแต่ละหน่วยงานถือครุภัณฑ์อะไร/สภาพเป็นอย่างไร
 */
class AssetDeptSummaryHelper
{
    /**
     * @param string[] $assetTypes ชนิดครุภัณฑ์ (asset_type_id) ที่อยู่ในขอบเขตของศูนย์
     *                             เช่น ['MED','SCI'] / ['COM'] / รายการของศูนย์ทั่วไป
     * @param string|null $q คำค้น (รหัส/ชื่อครุภัณฑ์) ให้ตรงกับลิสต์
     * @return array{rows: array<int,array<string,mixed>>, totals: array<string,mixed>}
     */
    public static function byDepartment(array $assetTypes, ?string $q = null): array
    {
        $q = ($q !== null && trim($q) !== '') ? trim($q) : null;

        $base = (new Query())
            ->from('{{%asset}} a')
            ->where(['a.asset_group_id' => 4, 'a.deleted_at' => null]);
        if (!empty($assetTypes)) {
            $base->andWhere(['a.asset_type_id' => $assetTypes]);
        }
        if ($q !== null) {
            $base->andWhere(['or',
                ['like', 'a.code', $q],
                ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(a.data_json, '$.asset_name'))"), $q],
            ]);
        }

        $rows = $base
            ->select([
                'dept_id' => 'a.department',
                'dept_name' => new Expression("COALESCE(t.name, '')"),
                'total' => 'COUNT(*)',
                'good' => "SUM(a.asset_condition = 'good')",
                'fair' => "SUM(a.asset_condition = 'fair')",
                'damaged' => "SUM(a.asset_condition IN ('damaged','worn'))",
                'repairing' => "SUM(a.asset_status = 'repair')",
                'wait_dispose' => "SUM(a.asset_status = 'wait_dispose')",
                'value' => 'COALESCE(SUM(a.price), 0)',
            ])
            ->leftJoin('{{%tree}} t', 't.id = a.department')
            ->groupBy('a.department')
            ->orderBy(['total' => SORT_DESC])
            ->all();

        $out = [];
        $noDept = null;
        $totals = ['dept' => 0, 'total' => 0, 'good' => 0, 'damaged' => 0, 'repairing' => 0, 'wait_dispose' => 0, 'value' => 0.0];

        foreach ($rows as $r) {
            $hasDept = !empty($r['dept_id']) && (string) $r['dept_name'] !== '';
            $row = [
                'dept_id' => (int) $r['dept_id'],
                'dept_name' => $hasDept ? (string) $r['dept_name'] : 'ไม่ระบุหน่วยงาน',
                'has_dept' => $hasDept,
                'total' => (int) $r['total'],
                'good' => (int) $r['good'],
                'fair' => (int) $r['fair'],
                'damaged' => (int) $r['damaged'],
                'repairing' => (int) $r['repairing'],
                'wait_dispose' => (int) $r['wait_dispose'],
                'value' => (float) $r['value'],
            ];

            if ($hasDept) {
                $out[] = $row;
                $totals['dept']++;
            } elseif ($noDept === null) {
                $noDept = $row;
            } else {
                // รวมทุกกรณีไม่มีหน่วยงาน (NULL/0/หน่วยที่ถูกลบ) เป็นก้อนเดียว
                foreach (['total', 'good', 'fair', 'damaged', 'repairing', 'wait_dispose'] as $k) {
                    $noDept[$k] += $row[$k];
                }
                $noDept['value'] += $row['value'];
            }

            $totals['total'] += $row['total'];
            $totals['good'] += $row['good'];
            $totals['damaged'] += $row['damaged'];
            $totals['repairing'] += $row['repairing'];
            $totals['wait_dispose'] += $row['wait_dispose'];
            $totals['value'] += $row['value'];
        }

        if ($noDept !== null) {
            $noDept['dept_id'] = 0;
            $noDept['has_dept'] = false;
            $out[] = $noDept; // ไว้ท้ายสุดเสมอ
        }

        return ['rows' => $out, 'totals' => $totals];
    }
}
