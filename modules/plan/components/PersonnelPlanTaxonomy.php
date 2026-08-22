<?php

namespace app\modules\plan\components;

use Yii;
use yii\db\Query;

/**
 * PersonnelPlanTaxonomy — ตัวกลางระหว่าง "ประเภทค่าใช้จ่ายบุคลากร" (categorise name=plan_item)
 * กับ "ประเภทบุคลากร" (employee_type) ที่ใช้ดึงรายชื่อเข้าแผนบุคลากร
 *
 * เดิมผูกด้วยรหัสตายตัวในโค้ด (P1, P2, ... , PER_02_04) ทำให้เครื่องที่รหัส plan_item
 * ไม่ตรงกับฐานทดสอบใช้งานไม่ได้ ตอนนี้อ่านจาก categorise.data_json ของ plan_item แทน
 *   { "employee_type_ids": [3], "all_employee_types": false }
 * ตั้งค่าได้ที่หน้า /plan/plan-item ; ถ้ายังไม่ตั้งค่าจะถอยไปใช้ค่าเดิม (LEGACY_TYPE_MAP)
 *
 * ฐานการจ่าย (รายเดือน/รายวัน) อ่านจาก employee_type.data_json
 *   { "pay_basis": "daily", "work_days_per_month": 22 }
 */
final class PersonnelPlanTaxonomy
{
    /** จำนวนวันทำงานต่อเดือนที่ใช้ตั้งต้นวงเงินของลูกจ้างรายวัน */
    const DEFAULT_WORK_DAYS = 22;

    /** ค่าเดิมที่เคย hardcode ไว้ ใช้เมื่อ plan_item ยังไม่ได้ตั้งค่าประเภทบุคลากร */
    const LEGACY_TYPE_MAP = [
        'P1' => [3],  'PER_01_01' => [3],
        'P2' => [4],  'PER_01_02' => [4],
        'P3' => [5],  'PER_01_03' => [5],
        'P8' => [1],  'PER_02_03' => [1],
        'P10' => [1, 2], 'PER_02_05' => [1, 2],
    ];

    /** ค่าตอบแทน ฉ.11 จ่ายให้บุคลากรทุกประเภทที่ยังปฏิบัติงานในหน่วยงาน */
    const LEGACY_ALL_TYPES = ['P9', 'PER_02_04'];

    /** ประเภทบุคลากรที่จ่ายเป็นรายวันตามค่าเดิม (ลูกจ้างชั่วคราวรายวัน) */
    const LEGACY_DAILY_TYPE_ID = 5;

    /** @var array<string,array{employee_type_ids:int[],all_employee_types:bool}> */
    private static $itemCache = [];

    /** @var array<int,array{basis:string,work_days:int}>|null */
    private static $payBasisCache = null;

    /**
     * การตั้งค่าประเภทบุคลากรของประเภทค่าใช้จ่าย 1 รายการ
     * @return array{employee_type_ids:int[],all_employee_types:bool}
     */
    public static function config(string $planItemId): array
    {
        $planItemId = trim($planItemId);
        if ($planItemId === '') {
            return ['employee_type_ids' => [], 'all_employee_types' => false];
        }
        if (array_key_exists($planItemId, self::$itemCache)) {
            return self::$itemCache[$planItemId];
        }

        $config = self::fromDatabase($planItemId) ?? self::legacyConfig($planItemId);

        return self::$itemCache[$planItemId] = $config;
    }

    /** ประเภทบุคลากรที่ผูกกับประเภทค่าใช้จ่ายนี้ */
    public static function employeeTypeIds(string $planItemId): array
    {
        return self::config($planItemId)['employee_type_ids'];
    }

    /** ประเภทค่าใช้จ่ายนี้จ่ายให้บุคลากรทุกประเภทหรือไม่ */
    public static function appliesToAllEmployees(string $planItemId): bool
    {
        return self::config($planItemId)['all_employee_types'];
    }

    /**
     * ฐานการจ่ายของประเภทบุคลากร
     * @return array{basis:string,work_days:int}
     */
    public static function payBasis(int $employeeTypeId): array
    {
        if (self::$payBasisCache === null) {
            self::$payBasisCache = self::loadPayBasis();
        }

        return self::$payBasisCache[$employeeTypeId] ?? self::defaultPayBasis($employeeTypeId);
    }

    /** วงเงินประมาณการทั้งปีจากอัตราใน employees.salary (รายเดือน = อัตรา x 12, รายวัน = อัตรา x วันทำงาน x 12) */
    public static function annualBudget(int $employeeTypeId, float $rate): float
    {
        $rate = max(0.0, $rate);
        $basis = self::payBasis($employeeTypeId);

        return $basis['basis'] === 'daily'
            ? $rate * $basis['work_days'] * 12
            : $rate * 12;
    }

    /** ล้าง cache (ใช้ในเทสต์ หรือหลังแก้ค่าในหน้าตั้งค่า) */
    public static function clearCache(): void
    {
        self::$itemCache = [];
        self::$payBasisCache = null;
    }

    /** อ่านการตั้งค่าจาก categorise.data_json ; null = ยังไม่ได้ตั้งค่า/อ่านไม่ได้ */
    private static function fromDatabase(string $planItemId): ?array
    {
        try {
            $raw = (new Query())
                ->select('data_json')
                ->from('categorise')
                ->where(['name' => 'plan_item', 'code' => $planItemId])
                ->scalar();
        } catch (\Throwable $e) {
            // ฐานข้อมูลยังไม่พร้อม (ตาราง/คอลัมน์หาย, รันในเทสต์) -> ใช้ค่าเดิม
            Yii::warning($e->getMessage(), __METHOD__);
            return null;
        }

        if ($raw === false || $raw === null || $raw === '') {
            return null;
        }

        $json = self::decode($raw);
        $hasTypes = array_key_exists('employee_type_ids', $json);
        $hasAll = array_key_exists('all_employee_types', $json);
        if (!$hasTypes && !$hasAll) {
            return null;
        }

        $allTypes = !empty($json['all_employee_types']);
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) ($json['employee_type_ids'] ?? [])
        ))));

        return [
            'employee_type_ids' => $allTypes ? [] : $ids,
            'all_employee_types' => $allTypes,
        ];
    }

    private static function legacyConfig(string $planItemId): array
    {
        return [
            'employee_type_ids' => self::LEGACY_TYPE_MAP[$planItemId] ?? [],
            'all_employee_types' => in_array($planItemId, self::LEGACY_ALL_TYPES, true),
        ];
    }

    /** @return array<int,array{basis:string,work_days:int}> */
    private static function loadPayBasis(): array
    {
        try {
            $rows = (new Query())->select(['id', 'data_json'])->from('employee_type')->all();
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $json = self::decode($row['data_json'] ?? null);
            $id = (int) $row['id'];
            $basis = strtolower((string) ($json['pay_basis'] ?? ''));
            if ($basis !== 'daily' && $basis !== 'monthly') {
                $basis = self::defaultPayBasis($id)['basis']; // ยังไม่ได้ตั้งค่า -> ใช้ค่าเดิม
            }
            $workDays = (int) ($json['work_days_per_month'] ?? 0);
            $map[$id] = [
                'basis' => $basis,
                'work_days' => $workDays > 0 ? $workDays : self::DEFAULT_WORK_DAYS,
            ];
        }

        return $map;
    }

    /** data_json ของ categorise/employee_type เป็น array เสมอ */
    private static function decode($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true); // เผื่อค่าที่เคยถูก encode ซ้ำ
        }

        return is_array($decoded) ? $decoded : [];
    }

    /** ค่าเดิมก่อนมีการตั้งค่าใน employee_type.data_json (id 5 = ลูกจ้างชั่วคราวรายวัน) */
    private static function defaultPayBasis(int $employeeTypeId): array
    {
        return [
            'basis' => $employeeTypeId === self::LEGACY_DAILY_TYPE_ID ? 'daily' : 'monthly',
            'work_days' => self::DEFAULT_WORK_DAYS,
        ];
    }
}
