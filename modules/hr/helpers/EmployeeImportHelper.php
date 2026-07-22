<?php

namespace app\modules\hr\helpers;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use Yii;
use yii\db\Expression;

/**
 * แหล่งข้อมูล dropdown + ตัวแปลง label→id/code สำหรับ template นำเข้าบุคลากร
 *
 * เป็น single source of truth ที่ใช้ร่วมกันระหว่าง:
 *  - actionImportTemplate (สร้าง Excel + dropdown)
 *  - parseRows / applyRows (validate + map ค่ากลับเป็น id/code แล้ว update/insert)
 *
 * หลักการ: ไม่สร้าง master/tree ใหม่ — ค่าที่ไม่ตรง dropdown ถือเป็น error
 */
class EmployeeImportHelper
{
    /**
     * นิยามคอลัมน์ template ตามลำดับ (index = ตำแหน่งคอลัมน์ใน Excel เริ่มที่ 0 = A)
     *
     * key      = ชื่ออ้างอิงภายใน
     * header   = หัวข้อภาษาไทย
     * type     = text | date | number | dropdown | flag
     * option   = ชื่อชุดตัวเลือก (เมื่อ type = dropdown) อ้างไป optionSets()
     * required = บังคับกรอกหรือไม่
     */
    public static function schema()
    {
        return [
            ['key' => 'cid',              'header' => 'เลขบัตรประชาชน',   'type' => 'text',     'required' => true],
            ['key' => 'gender',           'header' => 'เพศ',              'type' => 'dropdown', 'option' => 'gender'],
            ['key' => 'prefix',           'header' => 'คำนำหน้า',         'type' => 'dropdown', 'option' => 'prefix'],
            ['key' => 'fname',            'header' => 'ชื่อ',             'type' => 'text',     'required' => true],
            ['key' => 'lname',            'header' => 'นามสกุล',          'type' => 'text',     'required' => true],
            ['key' => 'birthday',         'header' => 'วันเกิด',          'type' => 'date'],
            ['key' => 'email',            'header' => 'Email',            'type' => 'text'],
            ['key' => 'address',          'header' => 'ที่อยู่',          'type' => 'text'],
            ['key' => 'zipcode',          'header' => 'รหัสไปรษณีย์',      'type' => 'text'],
            ['key' => 'phone',            'header' => 'หมายเลขโทรศัพท์',   'type' => 'text'],
            ['key' => 'status',           'header' => 'สถานะ',            'type' => 'dropdown', 'option' => 'status'],
            ['key' => 'position',         'header' => 'ตำแหน่ง',          'type' => 'dropdown', 'option' => 'position'],
            ['key' => 'position_level',   'header' => 'ระดับตำแหน่ง',      'type' => 'dropdown', 'option' => 'level'],
            ['key' => 'position_type',    'header' => 'ประเภท',           'type' => 'dropdown', 'option' => 'type'],
            ['key' => 'expertise',        'header' => 'ความเชี่ยวชาญ',     'type' => 'dropdown', 'option' => 'expertise'],
            ['key' => 'position_group',   'header' => 'ประเภท/กลุ่มงาน',   'type' => 'dropdown', 'option' => 'group'],
            ['key' => 'salary',           'header' => 'เงินเดือน',         'type' => 'number'],
            ['key' => 'org_group',        'header' => 'กลุ่มงาน',          'type' => 'dropdown', 'option' => 'tree_group'],
            ['key' => 'org_unit',         'header' => 'หน่วยงาน',          'type' => 'dropdown', 'option' => 'tree_unit'],
            ['key' => 'is_group_leader',  'header' => 'หัวหน้ากลุ่มงาน',   'type' => 'flag',     'option' => 'flag'],
            ['key' => 'is_unit_leader',   'header' => 'หัวหน้าหน่วยงาน',   'type' => 'flag',     'option' => 'flag'],
            ['key' => 'appoint_date',     'header' => 'วันที่แต่งตั้ง',    'type' => 'date'],
            ['key' => 'movement',         'header' => 'รายการเคลื่อนไหว',  'type' => 'text'],
            ['key' => 'position_number',  'header' => 'เลขประจำตำแหน่ง',   'type' => 'text'],
            ['key' => 'join_date',        'header' => 'วันที่เริ่มงาน',    'type' => 'date'],
        ];
    }

    /**
     * ชุดตัวเลือก dropdown ทั้งหมด (cache ต่อ request)
     * แต่ละชุด: ['labels' => [แสดงใน dropdown], 'map' => [label => ค่าที่บันทึกลง DB]]
     */
    public static function optionSets()
    {
        static $sets = null;
        if ($sets !== null) {
            return $sets;
        }

        $sets = [
            'gender'     => self::plainList(self::distinctColumn('gender')),
            'prefix'     => self::plainList(self::distinctColumn('prefix')),
            'status'     => self::categoriseSet('emp_status'),
            'level'      => self::categoriseSet('position_level'),
            'expertise'  => self::categoriseSet('expertise'),
            'position'   => self::masterSet(EmployeePosition::class),
            'type'       => self::masterSet(EmployeeType::class),
            'group'      => self::masterSet(EmployeePositionGroup::class),
            'tree_group' => self::treeGroupSet(),
            'tree_unit'  => self::treeUnitSet(),
            'flag'       => ['labels' => ['Y'], 'map' => ['Y' => 'Y']],
        ];

        return $sets;
    }

    /** ตัวเลือกของ 1 คอลัมน์ (ตาม option key) — คืน [] ถ้าไม่ใช่ dropdown */
    public static function options($optionKey)
    {
        $sets = self::optionSets();
        return $sets[$optionKey] ?? ['labels' => [], 'map' => []];
    }

    /**
     * แถวตัวอย่างสำหรับ template (2 รายการ) — ใช้ค่าจริงจาก dropdown ในระบบ
     * เพื่อให้ผู้ใช้เห็นรูปแบบค่าที่ถูกต้อง (ลบทิ้งก่อนนำเข้าจริง)
     * คืน array เรียงตามลำดับคอลัมน์ของ schema()
     */
    public static function exampleRows()
    {
        $sets = self::optionSets();
        // เลือก label ลำดับที่ idx (ถ้าไม่มีคืนค่าว่าง)
        $pick = function ($key, $idx = 0) use ($sets) {
            $labels = $sets[$key]['labels'] ?? [];
            if (empty($labels)) {
                return '';
            }
            return $labels[min($idx, count($labels) - 1)];
        };

        $samples = [
            [
                'cid' => '1100000000001', 'gender' => $pick('gender', 0), 'prefix' => $pick('prefix', 0),
                'fname' => 'สมชาย', 'lname' => 'ใจดี', 'birthday' => '15/05/2530',
                'email' => 'somchai@example.com', 'address' => '123 หมู่ 1 ต.ด่านซ้าย อ.ด่านซ้าย จ.เลย', 'zipcode' => '42120', 'phone' => '0812345678',
                'status' => $pick('status', 0), 'position' => $pick('position', 0), 'position_level' => $pick('level', 0),
                'position_type' => $pick('type', 0), 'expertise' => $pick('expertise', 0), 'position_group' => $pick('group', 0),
                'salary' => '25000', 'org_group' => $pick('tree_group', 0), 'org_unit' => $pick('tree_unit', 0),
                'is_group_leader' => '', 'is_unit_leader' => 'Y',
                'appoint_date' => '01/10/2566', 'movement' => 'คำสั่งแต่งตั้ง ที่ 1/2566', 'position_number' => '12345', 'join_date' => '01/06/2558',
            ],
            [
                'cid' => '1100000000002', 'gender' => $pick('gender', 1), 'prefix' => $pick('prefix', 1),
                'fname' => 'สมหญิง', 'lname' => 'รักงาน', 'birthday' => '20/11/2535',
                'email' => 'somying@example.com', 'address' => '456 หมู่ 2 ต.โคกงาม อ.ด่านซ้าย จ.เลย', 'zipcode' => '42120', 'phone' => '0898765432',
                'status' => $pick('status', 1), 'position' => $pick('position', 1), 'position_level' => $pick('level', 1),
                'position_type' => $pick('type', 1), 'expertise' => $pick('expertise', 1), 'position_group' => $pick('group', 1),
                'salary' => '18000', 'org_group' => $pick('tree_group', 1), 'org_unit' => $pick('tree_unit', 1),
                'is_group_leader' => '', 'is_unit_leader' => '',
                'appoint_date' => '01/04/2567', 'movement' => 'คำสั่งบรรจุ ที่ 5/2567', 'position_number' => '67890', 'join_date' => '01/04/2567',
            ],
        ];

        // แปลง assoc → เรียงตามลำดับคอลัมน์ของ schema()
        $ordered = [];
        foreach ($samples as $s) {
            $line = [];
            foreach (self::schema() as $col) {
                $line[] = $s[$col['key']] ?? '';
            }
            $ordered[] = $line;
        }

        return $ordered;
    }

    // ---- ตัวช่วยสร้างชุดตัวเลือก ----

    /** ค่า distinct จาก column ของตาราง employees (เช่น gender, prefix) */
    protected static function distinctColumn($column)
    {
        return Employees::find()
            ->select($column)
            ->distinct()
            ->where(['not', [$column => null]])
            ->andWhere(['<>', $column, ''])
            ->orderBy($column)
            ->column();
    }

    /** ลิสต์ธรรมดา: บันทึกค่า label ตรงๆ (map เป็น identity) */
    protected static function plainList($labels)
    {
        $labels = array_values(array_unique(array_filter(array_map('trim', $labels), fn($v) => $v !== '')));
        $map = [];
        foreach ($labels as $label) {
            $map[$label] = $label;
        }
        return ['labels' => $labels, 'map' => $map];
    }

    /** ชุดจาก categorise (name ที่กำหนด) → label = title, value = code */
    protected static function categoriseSet($name)
    {
        $rows = Categorise::find()
            ->select(['code', 'title'])
            ->where(['name' => $name])
            ->andWhere(['active' => 1])
            ->andWhere(['not', ['title' => null]])
            ->andWhere(['<>', 'title', ''])
            ->orderBy('sort')
            ->asArray()
            ->all();
        return self::buildSet($rows, 'title', 'code');
    }

    /** ชุดจาก master table (EmployeeType/EmployeePosition/EmployeePositionGroup) → label = title, value = id */
    protected static function masterSet($modelClass)
    {
        $rows = $modelClass::find()
            ->select(['id', 'title'])
            ->where(['active' => 1])
            ->andWhere(['not', ['title' => null]])
            ->andWhere(['<>', 'title', ''])
            ->orderBy('sort')
            ->asArray()
            ->all();
        return self::buildSet($rows, 'title', 'id');
    }

    /** กลุ่มงาน = tree lvl 1 → label = name, value = node id */
    protected static function treeGroupSet()
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT id, name FROM tree WHERE lvl = 1 ORDER BY lft"
        )->queryAll();
        return self::buildSet($rows, 'name', 'id');
    }

    /**
     * หน่วยงาน = tree lvl 2 → label = "กลุ่มงาน › หน่วยงาน" (กันชื่อซ้ำ), value = node id
     * เก็บ parent group id ไว้ใน key พิเศษ 'parent' เพื่อใช้ตอน set หัวหน้ากลุ่มงาน
     */
    protected static function treeUnitSet()
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT u.id AS id, u.name AS unit, p.id AS parent_id, p.name AS grp
             FROM tree u
             LEFT JOIN tree p ON p.lft < u.lft AND p.rgt > u.rgt AND p.lvl = u.lvl - 1 AND p.root = u.root
             WHERE u.lvl = 2
             ORDER BY u.lft"
        )->queryAll();

        $labels = [];
        $map = [];
        $parent = [];
        foreach ($rows as $r) {
            $label = ($r['grp'] !== null && $r['grp'] !== '')
                ? $r['grp'] . ' › ' . $r['unit']
                : $r['unit'];
            $labels[] = $label;
            $map[$label] = (int) $r['id'];
            $parent[$label] = $r['parent_id'] !== null ? (int) $r['parent_id'] : null;
        }
        return ['labels' => $labels, 'map' => $map, 'parent' => $parent];
    }

    protected static function buildSet($rows, $labelKey, $valueKey)
    {
        $labels = [];
        $map = [];
        foreach ($rows as $r) {
            $label = trim((string) $r[$labelKey]);
            if ($label === '' || isset($map[$label])) {
                continue; // ข้ามค่าว่าง/ชื่อซ้ำ (กันกำกวม)
            }
            $labels[] = $label;
            $map[$label] = $r[$valueKey];
        }
        return ['labels' => $labels, 'map' => $map];
    }

    // ============================================================
    //  Phase 1 : validate + parse (ยังไม่แตะ DB)
    // ============================================================

    /** header ภาษาไทยของคอลัมน์ตาม key */
    protected static function headerOf($key)
    {
        foreach (self::schema() as $col) {
            if ($col['key'] === $key) {
                return $col['header'];
            }
        }
        return $key;
    }

    protected static function isEmptyRow($raw)
    {
        foreach ((array) $raw as $v) {
            if (trim((string) $v) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * ตรวจสอบและแปลงข้อมูลทั้งไฟล์ (rows[0] = header)
     * คืน ['parsed'=>[], 'errors'=>[['row','cid','messages']], 'fatal'=>string|null]
     */
    public static function parseRows(array $rows)
    {
        $schema = self::schema();
        $sets = self::optionSets();
        $out = ['parsed' => [], 'errors' => [], 'fatal' => null];

        if (count($rows) < 2) {
            $out['fatal'] = 'ไฟล์ไม่มีข้อมูล (ต้องมีหัวตาราง + อย่างน้อย 1 แถว)';
            return $out;
        }

        array_shift($rows); // ตัดหัวตาราง
        $seenCid = [];
        $rowNo = 1;

        foreach ($rows as $raw) {
            $rowNo++;
            if (self::isEmptyRow($raw)) {
                continue;
            }

            $cell = [];
            foreach ($schema as $i => $col) {
                $cell[$col['key']] = isset($raw[$i]) ? trim((string) $raw[$i]) : '';
            }

            $errs = [];

            // cid
            $cid = preg_replace('/\D/', '', $cell['cid']);
            if (strlen($cid) !== 13) {
                $errs[] = 'เลขบัตรประชาชนต้องมี 13 หลัก';
            } elseif (isset($seenCid[$cid])) {
                $out['fatal'] = "เลขบัตรประชาชน {$cid} ซ้ำกันในไฟล์ (แถว {$seenCid[$cid]} และ {$rowNo}) — แก้ไขให้ไม่ซ้ำก่อนนำเข้า";
                return $out;
            } else {
                $seenCid[$cid] = $rowNo;
            }

            // required
            foreach (['fname', 'lname'] as $rk) {
                if ($cell[$rk] === '') {
                    $errs[] = 'ต้องระบุ' . self::headerOf($rk);
                }
            }

            // dropdown / flag → resolve
            $resolved = [];
            foreach ($schema as $col) {
                if (in_array($col['type'], ['dropdown', 'flag'], true) && $cell[$col['key']] !== '') {
                    $map = $sets[$col['option']]['map'] ?? [];
                    $label = $cell[$col['key']];
                    if (!array_key_exists($label, $map)) {
                        $errs[] = $col['header'] . ' "' . $label . '" ไม่มีในระบบ';
                    } else {
                        $resolved[$col['key']] = $map[$label];
                    }
                }
            }

            // dates
            $dateVals = [];
            foreach (['birthday', 'appoint_date', 'join_date'] as $dk) {
                if ($cell[$dk] !== '') {
                    $d = AppHelper::normalizeDateToDb($cell[$dk]);
                    if ($d === null) {
                        $errs[] = self::headerOf($dk) . ' รูปแบบวันที่ไม่ถูกต้อง (' . $cell[$dk] . ')';
                    } else {
                        $dateVals[$dk] = $d;
                    }
                }
            }

            if ($errs) {
                $out['errors'][] = ['row' => $rowNo, 'cid' => $cid, 'messages' => $errs];
                continue;
            }

            // tree nodes: หน่วยงาน (leaf) กำหนด department; ถ้าไม่มีใช้กลุ่มงาน
            $unitId = $resolved['org_unit'] ?? null;
            $groupId = $resolved['org_group'] ?? null;
            if ($unitId !== null && $groupId === null) {
                $groupId = $sets['tree_unit']['parent'][$cell['org_unit']] ?? null;
            }
            $department = $unitId ?? $groupId;

            $out['parsed'][] = [
                'cid' => $cid,
                'attrs' => [
                    'gender' => $cell['gender'] ?: null,
                    'prefix' => $cell['prefix'] ?: null,
                    'fname' => $cell['fname'],
                    'lname' => $cell['lname'],
                    'birthday' => $dateVals['birthday'] ?? null,
                    'email' => $cell['email'] ?: null,
                    'address' => $cell['address'] ?: null,
                    'zipcode' => $cell['zipcode'] !== '' ? (int) preg_replace('/\D/', '', $cell['zipcode']) : null,
                    'phone' => $cell['phone'] ?: null,
                    'status' => $resolved['status'] ?? null,
                    'employee_position_id' => $resolved['position'] ?? null,
                    'position_level' => $resolved['position_level'] ?? null,
                    'employee_type_id' => $resolved['position_type'] ?? null,
                    'expertise' => $resolved['expertise'] ?? null,
                    'employee_position_group_id' => $resolved['position_group'] ?? null,
                    'salary' => $cell['salary'] !== '' ? (int) str_replace(',', '', $cell['salary']) : null,
                    'department' => $department,
                    'position_number' => $cell['position_number'] ?: null,
                    'join_date' => $dateVals['join_date'] ?? null,
                ],
                'group_node_id' => $groupId,
                'unit_node_id' => $unitId,
                'is_group_leader' => isset($resolved['is_group_leader']),
                'is_unit_leader' => isset($resolved['is_unit_leader']),
                'appoint_date' => $dateVals['appoint_date'] ?? null,
                'join_date' => $dateVals['join_date'] ?? null,
                'movement' => $cell['movement'] ?: '',
                'texts' => [
                    'position_name_text' => $cell['position'] ?: '',
                    'position_level_text' => $cell['position_level'] ?: '',
                    'position_type_text' => $cell['position_type'] ?: '',
                    'position_group_text' => $cell['position_group'] ?: '',
                    'status_text' => $cell['status'] ?: '',
                ],
            ];
        }

        return $out;
    }

    // ============================================================
    //  Phase 2 : apply to DB (update/insert + history + leaders)
    // ============================================================

    /** บันทึกแถวที่ผ่าน validate ทั้งหมด (transaction ต่อแถว) */
    public static function applyRows(array $parsed)
    {
        $inserted = 0;
        $updated = 0;
        $failures = [];

        foreach ($parsed as $p) {
            $tx = Yii::$app->db->beginTransaction();
            try {
                $emp = Employees::find()->where(['cid' => $p['cid']])->one();
                $isNew = false;
                if (!$emp) {
                    $emp = new Employees();
                    $emp->cid = $p['cid'];
                    $emp->user_id = 0;
                    $emp->branch = 'MAIN';
                    $isNew = true;
                }
                // อัปเดตเฉพาะช่องที่มีค่า (ไม่ทับข้อมูลเดิมด้วยค่าว่างตอน update)
                foreach ($p['attrs'] as $attr => $val) {
                    if ($val !== null) {
                        $emp->$attr = $val;
                    }
                }
                // Employees::beforeSave() แปลง birthday ด้วย DateToDb() ซึ่งคาดหวังรูปแบบ d/m/Y (พ.ศ.)
                // จึงต้องส่ง birthday เป็น d/m/Y เสมอ (ทั้งค่าใหม่จาก import และค่าเดิม Y-m-d จาก DB)
                // มิฉะนั้น beforeSave จะแปลงพลาดแล้วลบวันเกิดทิ้ง
                if (!empty($emp->birthday) && strpos((string) $emp->birthday, '-') !== false) {
                    $emp->birthday = AppHelper::DateFormDb($emp->birthday) ?: $emp->birthday;
                }
                $emp->save(false);

                if ($p['is_group_leader'] && $p['group_node_id']) {
                    self::setLeader($p['group_node_id'], $emp->id);
                }
                if ($p['is_unit_leader'] && $p['unit_node_id']) {
                    self::setLeader($p['unit_node_id'], $emp->id);
                }

                self::upsertPositionHistory($emp->id, $p);

                $tx->commit();
                $isNew ? $inserted++ : $updated++;
            } catch (\Throwable $e) {
                $tx->rollBack();
                $failures[] = ['cid' => $p['cid'], 'message' => $e->getMessage()];
            }
        }

        return ['inserted' => $inserted, 'updated' => $updated, 'failures' => $failures];
    }

    /** ตั้งหัวหน้า (leader1) ของ tree node = emp id */
    protected static function setLeader($nodeId, $empId)
    {
        $node = Organization::findOne($nodeId);
        if (!$node) {
            return;
        }
        $json = is_array($node->data_json) ? $node->data_json : (json_decode((string) $node->data_json, true) ?: []);
        $json['leader1'] = (string) $empId;
        $node->data_json = $json;
        $node->save(false);
    }

    /** สร้าง/อัปเดตประวัติตำแหน่ง: record แต่งตั้ง + record เริ่มงาน (dedup ด้วย date_start) */
    protected static function upsertPositionHistory($empId, $p)
    {
        if ($p['appoint_date']) {
            self::writePositionRecord($empId, $p['appoint_date'], $p['movement'], $p);
        }
        // วันที่เริ่มงาน → record "เริ่มงาน" เว้นแต่ตรงกับวันแต่งตั้ง (ยึด record แต่งตั้ง)
        if ($p['join_date'] && $p['join_date'] !== $p['appoint_date']) {
            self::writePositionRecord($empId, $p['join_date'], 'เริ่มงาน', $p);
        }
    }

    protected static function writePositionRecord($empId, $dateStart, $movement, $p)
    {
        $detail = EmployeeDetail::find()
            ->where(['emp_id' => $empId, 'name' => 'position'])
            ->andWhere(new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '\$.date_start')) = :d", [':d' => $dateStart]))
            ->one();
        if (!$detail) {
            $detail = new EmployeeDetail();
            $detail->emp_id = $empId;
            $detail->name = 'position';
        }

        $json = is_array($detail->data_json) ? $detail->data_json : [];
        $json = array_merge($json, [
            'date_start' => $dateStart,
            'statuslist' => $movement,
            'position_number' => $p['attrs']['position_number'],
            'employee_position_id' => $p['attrs']['employee_position_id'],
            'position_name_text' => $p['texts']['position_name_text'],
            'employee_type_id' => $p['attrs']['employee_type_id'],
            'position_type_text' => $p['texts']['position_type_text'],
            'employee_position_group_id' => $p['attrs']['employee_position_group_id'],
            'position_group_text' => $p['texts']['position_group_text'],
            'position_level' => $p['attrs']['position_level'],
            'position_level_text' => $p['texts']['position_level_text'],
            'status' => $p['attrs']['status'],
            'status_text' => $p['texts']['status_text'],
        ]);
        $detail->data_json = $json;
        $detail->save(false);
    }
}
