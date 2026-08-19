<?php

namespace app\modules\hr\helpers;

use app\modules\hr\models\Organization;
use Yii;
use yii\db\Query;

/**
 * รวมยอดบุคลากรจากหน่วยย่อยขึ้นไปหา "กลุ่มงาน" (roll-up)
 *
 * ผังหน่วยงานเก็บใน tree เป็น nested set — คนผูกอยู่ที่ node ระดับล่าง (งาน)
 * แต่เกณฑ์กรอบอัตรากำลังของ สป.สธ. กำหนดที่ระดับกลุ่มงาน จึงต้องรวมขึ้นไปก่อนเทียบ
 *
 *   กลุ่มอำนวยการ (lvl 0)
 *     └ กลุ่มงานบริหารทั่วไป (lvl 1)  ← ระดับที่เกณฑ์กำหนดกรอบ
 *         └ งานการเงินและบัญชี (lvl 2) ← ระดับที่คนผูกอยู่จริง
 *
 * เป็น single source of truth สำหรับทุกหน้าที่ต้องรวมยอดตามกลุ่มงาน
 * (โมดูลกรอบอัตรากำลัง, Dashboard บุคลากร, LINE dashboard)
 *
 * หมายเหตุ: ไม่แตะโค้ดเดิมที่ยัง join ตาราง categorise — ตั้งใจให้เรียกใช้ใหม่ทีละหน้า
 */
class OrgRollupHelper
{
    /** ระดับใน tree ที่ถือว่าเป็น "กลุ่มงาน" */
    public const WORKGROUP_LEVEL = 1;

    /** สาขาที่เป็นโรงพยาบาลแม่ (BRANCH = รพ.สต./ลูกข่าย) */
    public const BRANCH_MAIN = 'MAIN';

    /** id ผู้ใช้ระบบที่ไม่ใช่บุคลากรจริง */
    public const SYSTEM_EMPLOYEE_ID = 1;

    /**
     * เงื่อนไขนับ "บุคลากรที่ปฏิบัติราชการ" ให้ตรงกันทุกหน้า
     *
     * ใช้นิยามเดียวกับ Dashboard บุคลากร: branch=MAIN, status=1, ตัด id ระบบ
     *
     * @param string $alias ชื่อ alias ของตาราง employees ใน query
     */
    public static function activeCondition(string $alias = 'e'): array
    {
        return [
            'and',
            [$alias . '.branch' => self::BRANCH_MAIN],
            [$alias . '.status' => '1'],
            ['not', [$alias . '.id' => self::SYSTEM_EMPLOYEE_ID]],
        ];
    }

    /**
     * กลุ่มงานทั้งหมดในผัง เรียงตามลำดับในผังจริง
     *
     * รวม node ระดับ 0 ที่มีคนผูกอยู่ด้วย เพราะถ้าตัดทิ้งยอดรวมจะขาด
     *
     * @return array<int,array{id:int,name:string,lvl:int}> key = tree.id
     */
    public static function workgroups(bool $mainOnly = true): array
    {
        $tree = Organization::tableName();

        $query = (new Query())
            ->select(['id', 'name', 'lvl', 'root', 'lft'])
            ->from($tree)
            ->where(['lvl' => self::WORKGROUP_LEVEL])
            ->andWhere(['active' => 1]);

        if ($mainOnly) {
            $query->andWhere(['root' => self::mainRootIds()]);
        }

        $rows = $query->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])->all();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['id']] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'lvl' => (int) $row['lvl'],
            ];
        }

        return $result;
    }

    /**
     * root ของผังที่ถือเป็นโรงพยาบาลแม่ — หาจากที่ที่บุคลากร branch=MAIN ผูกอยู่จริง
     *
     * ไม่ hardcode id เพราะโรงพยาบาลอื่นที่ใช้ระบบนี้จะมีผังคนละชุด
     *
     * @return int[]
     */
    public static function mainRootIds(): array
    {
        $tree = Organization::tableName();

        $ids = (new Query())
            ->select('t.root')
            ->distinct()
            ->from(['e' => 'employees'])
            ->innerJoin(['t' => $tree], 't.id = e.department')
            ->where(self::activeCondition('e'))
            ->column();

        $ids = array_values(array_unique(array_map('intval', $ids)));

        return $ids !== [] ? $ids : [1];
    }

    /**
     * map: tree.id ของหน่วยใด ๆ => tree.id ของกลุ่มงานที่สังกัด
     *
     * node ที่อยู่เหนือระดับกลุ่มงาน (lvl 0) ให้ถือว่าตัวเองเป็นกลุ่มงาน
     * ไม่งั้นคนที่ผูกไว้ที่ node ระดับบนสุดจะหายไปจากยอดรวม
     *
     * @return array<int,int>
     */
    public static function unitToWorkgroup(bool $mainOnly = true): array
    {
        $tree = Organization::tableName();

        $query = (new Query())
            ->select([
                'unit_id' => 't.id',
                'wg_id' => 'COALESCE(g.id, t.id)',
            ])
            ->from(['t' => $tree])
            ->leftJoin(
                ['g' => $tree],
                'g.root = t.root AND g.lft <= t.lft AND g.rgt >= t.rgt AND g.lvl = :wglvl',
                [':wglvl' => self::WORKGROUP_LEVEL]
            );

        if ($mainOnly) {
            $query->where(['t.root' => self::mainRootIds()]);
        }

        $map = [];
        foreach ($query->all() as $row) {
            $map[(int) $row['unit_id']] = (int) $row['wg_id'];
        }

        return $map;
    }

    /**
     * tree.id ทั้งหมดที่อยู่ใต้กลุ่มงานหนึ่ง (รวมตัวเอง) — ใช้กรองบุคลากรตามกลุ่มงาน
     *
     * @return int[]
     */
    public static function unitIdsUnder(int $workgroupId): array
    {
        $tree = Organization::tableName();

        $node = (new Query())
            ->select(['root', 'lft', 'rgt'])
            ->from($tree)
            ->where(['id' => $workgroupId])
            ->one();

        if ($node === false || $node === null) {
            return [];
        }

        $ids = (new Query())
            ->select('id')
            ->from($tree)
            ->where(['root' => (int) $node['root']])
            ->andWhere(['>=', 'lft', (int) $node['lft']])
            ->andWhere(['<=', 'rgt', (int) $node['rgt']])
            ->column();

        return array_map('intval', $ids);
    }

    /**
     * จำนวนบุคลากรรายกลุ่มงาน เรียงตามลำดับในผัง
     *
     * @param array $filters ตัวกรองเพิ่ม เช่น ['e.employee_type_id' => 1]
     * @return array<int,array{id:int,name:string,count:int}>
     */
    public static function countsByWorkgroup(array $filters = [], bool $mainOnly = true): array
    {
        $rows = self::baseCountQuery($filters, $mainOnly)
            ->select([
                'wg_id' => 'COALESCE(g.id, t.id)',
                'cnt' => 'COUNT(e.id)',
            ])
            ->groupBy(['COALESCE(g.id, t.id)'])
            ->all();

        return self::attachNames($rows, 'cnt', $mainOnly);
    }

    /**
     * จำนวนบุคลากรรายกลุ่มงาน แยกตามประเภทการจ้าง — ใช้กับกราฟแท่งซ้อน
     *
     * @return array<int,array{id:int,name:string,count:int,by_type:array<int,int>}>
     */
    public static function countsByWorkgroupAndType(array $filters = [], bool $mainOnly = true): array
    {
        $rows = self::baseCountQuery($filters, $mainOnly)
            ->select([
                'wg_id' => 'COALESCE(g.id, t.id)',
                'type_id' => 'e.employee_type_id',
                'cnt' => 'COUNT(e.id)',
            ])
            ->groupBy(['COALESCE(g.id, t.id)', 'e.employee_type_id'])
            ->all();

        $byWorkgroup = [];
        foreach ($rows as $row) {
            $wgId = (int) $row['wg_id'];
            $typeId = (int) $row['type_id'];
            $count = (int) $row['cnt'];

            if (!isset($byWorkgroup[$wgId])) {
                $byWorkgroup[$wgId] = ['wg_id' => $wgId, 'cnt' => 0, 'by_type' => []];
            }
            $byWorkgroup[$wgId]['cnt'] += $count;
            $byWorkgroup[$wgId]['by_type'][$typeId] = ($byWorkgroup[$wgId]['by_type'][$typeId] ?? 0) + $count;
        }

        $result = self::attachNames(array_values($byWorkgroup), 'cnt', $mainOnly);

        foreach ($result as $index => $item) {
            $result[$index]['by_type'] = $byWorkgroup[$item['id']]['by_type'] ?? [];
        }

        return $result;
    }

    /**
     * จำนวนบุคลากรราย (หน่วยงาน × ตำแหน่ง × ประเภทการจ้าง)
     *
     * เป็นชุดข้อมูลตั้งต้นของตารางกรอบอัตรากำลัง — คงไว้ที่หน่วยที่คนผูกอยู่จริง
     * ไม่รวมขึ้นกลุ่มงาน เพราะกรอบต้องแสดงรายตำแหน่งในหน่วยนั้น
     *
     * @return array<int,array{unit_id:int,workgroup_id:int,position_id:int,type_id:int,count:int}>
     */
    public static function headcountMatrix(array $filters = [], bool $mainOnly = true): array
    {
        $rows = self::baseCountQuery($filters, $mainOnly)
            ->select([
                'unit_id' => 't.id',
                'wg_id' => 'COALESCE(g.id, t.id)',
                'position_id' => 'e.employee_position_id',
                'type_id' => 'e.employee_type_id',
                'cnt' => 'COUNT(e.id)',
            ])
            ->groupBy(['t.id', 'COALESCE(g.id, t.id)', 'e.employee_position_id', 'e.employee_type_id'])
            ->all();

        return array_map(static function ($row) {
            return [
                'unit_id' => (int) $row['unit_id'],
                'workgroup_id' => (int) $row['wg_id'],
                'position_id' => (int) $row['position_id'],
                'type_id' => (int) $row['type_id'],
                'count' => (int) $row['cnt'],
            ];
        }, $rows);
    }

    /**
     * query ตั้งต้นที่ join ผัง + หา ancestor ระดับกลุ่มงานไว้ให้แล้ว
     */
    private static function baseCountQuery(array $filters, bool $mainOnly): Query
    {
        $tree = Organization::tableName();

        $query = (new Query())
            ->from(['e' => 'employees'])
            ->innerJoin(['t' => $tree], 't.id = e.department')
            ->leftJoin(
                ['g' => $tree],
                'g.root = t.root AND g.lft <= t.lft AND g.rgt >= t.rgt AND g.lvl = :wglvl',
                [':wglvl' => self::WORKGROUP_LEVEL]
            )
            ->where(self::activeCondition('e'));

        if ($mainOnly) {
            $query->andWhere(['t.root' => self::mainRootIds()]);
        }

        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $query->andWhere([$column => $value]);
        }

        return $query;
    }

    /**
     * เติมชื่อกลุ่มงานและเรียงตามลำดับในผัง — กลุ่มที่ไม่มีคนจะไม่อยู่ในผลลัพธ์
     */
    private static function attachNames(array $rows, string $countKey, bool $mainOnly): array
    {
        $workgroups = self::workgroups($mainOnly);

        // node ระดับ 0 ที่มีคนผูกอยู่ ไม่อยู่ใน workgroups() ต้องดึงชื่อเพิ่ม
        $missing = [];
        foreach ($rows as $row) {
            $wgId = (int) $row['wg_id'];
            if (!isset($workgroups[$wgId])) {
                $missing[] = $wgId;
            }
        }
        if ($missing !== []) {
            $extra = (new Query())
                ->select(['id', 'name', 'lvl'])
                ->from(Organization::tableName())
                ->where(['id' => array_values(array_unique($missing))])
                ->all();
            foreach ($extra as $row) {
                $workgroups[(int) $row['id']] = [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'lvl' => (int) $row['lvl'],
                ];
            }
        }

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['wg_id']] = (int) $row[$countKey];
        }

        $result = [];
        foreach ($workgroups as $wgId => $workgroup) {
            if (!isset($counts[$wgId])) {
                continue;
            }
            $result[] = [
                'id' => $wgId,
                'name' => $workgroup['name'],
                'count' => $counts[$wgId],
            ];
        }

        return $result;
    }
}
