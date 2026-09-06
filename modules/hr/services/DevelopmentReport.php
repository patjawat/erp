<?php

namespace app\modules\hr\services;

use Yii;

/**
 * แหล่งความจริงเดียวของ "ตัวเลขรายงานการพัฒนาบุคลากร" (อบรม/ประชุม/ดูงาน/วิทยากร)
 *
 * รวมนิยามตัวชี้วัดไว้ที่เดียว เพื่อให้หน้า dashboard / report / รายหน่วยงาน / รายบุคคล
 * ใช้เลขชุดเดียวกันทั้งหมด ไม่ต่างคนต่างนับเหมือนของเดิม (ดู CHANGELOG ด้านล่าง)
 *
 * นิยามที่ยึด (เฟส 0):
 * - "กิจกรรม" (activities)      = จำนวนใบ development ของปี ยกเว้นสถานะยกเลิก/ไม่อนุมัติ
 * - "คน-ครั้ง" (person-times)    = ผู้ขอ 1 + สมาชิกคณะเดินทางของแต่ละใบ (นับซ้ำข้ามใบได้)
 * - "บุคลากรที่ได้รับการพัฒนา"    = จำนวนคน "ไม่ซ้ำ" (ผู้ขอ ∪ สมาชิก) — ของเดิมนับเฉพาะผู้ขอ
 * - "งบที่ตั้งไว้" (planned)      = ยอดจากแผนการเงิน (plan_order) เฉพาะ plan_item = งบพัฒนาบุคลากร
 * - "งบที่ใช้จริง" (actual)       = ประมาณการค่าใช้จ่ายที่บันทึกในใบ development (data_json + รายการเก่า)
 *
 * แหล่งงบ "ที่ตั้งไว้" ผูกกับแผนการเงินตามที่ผู้ใช้กำหนด (แผน vs ผล) — ไม่คิดเองแยกในโมดูลนี้
 */
class DevelopmentReport
{
    /**
     * plan_item code ในผังงบ (แผนการเงิน) ที่นับเป็น "งบพัฒนาบุคลากร"
     * P42 = ค่าใช้จ่ายด้านการฝึกอบรมในประเทศ (เงินนอกงปม.) หมวด OPS_06
     * P70 = ค่าใช้จ่ายในการประชุม หมวด OPS_05
     * (ผังงบนี้ไม่มีรายการค่าเดินทาง/เบี้ยเลี้ยง/ที่พักแยก — รวมอยู่ใน 2 รายการนี้)
     * *** แก้ที่เดียวจุดนี้หากนิยามงบเปลี่ยน (เช่น เพิ่ม P15 โครงการเงินนอกงบฯ) ***
     */
    public const BUDGET_PLAN_ITEMS = ['P42', 'P70'];

    /** สถานะใบ development ที่ไม่นับว่าเป็นการพัฒนา (ยกเลิก/ไม่อนุมัติ) */
    public const EXCLUDED_DEV_STATUSES = ['Cancel', 'Reject'];

    /** สถานะแผน plan_order ที่ไม่นำมารวมเป็นงบที่ตั้งไว้ (สอดคล้องกับหน้า /plan/overview) */
    public const EXCLUDED_PLAN_STATUSES = ['reject'];

    /**
     * ป้ายสั้นของประเภทการพัฒนา (map จากรหัส categorise development_type ที่เสถียร)
     * ใช้ในมุมมองกะทัดรัด (ป้ายรายคน) แทนชื่อเต็มที่ยาว — ชื่อเต็มยังใช้ในตารางรายงานทางการ
     */
    public const TYPE_SHORT = [
        'dev1' => 'ประชุมงาน',
        'dev2' => 'อบรม',
        'dev3' => 'วิทยากร',
        'dev4' => 'นำเสนอ',
        'dev5' => 'ดูงาน',
        'dev6' => 'อื่นๆ',
    ];

    /** คืนป้ายสั้นของประเภทการพัฒนาจากรหัส ถ้าไม่รู้จักรหัสให้ใช้ชื่อเต็ม (ตัด "เพื่อ" นำหน้า) */
    public static function typeShort(?string $code, ?string $titleFallback = null): string
    {
        if ($code !== null && isset(self::TYPE_SHORT[$code])) {
            return self::TYPE_SHORT[$code];
        }
        $t = trim((string) $titleFallback);
        if ($t === '') {
            return 'ไม่ระบุ';
        }
        return mb_substr($t, 0, 3) === 'เพื่อ' ? mb_substr($t, 3) : $t;
    }

    /**
     * หมวดประมาณการค่าใช้จ่ายใน development.data_json → ป้ายแสดงผล
     * ใช้ทั้งการรวมยอด (actualSpend) และการแตกรายการ (actualSpendByComponent)
     */
    private const COST_COMPONENTS = [
        'estimated_cost_registration' => 'ค่าลงทะเบียน',
        'estimated_cost_accommodation' => 'ค่าที่พัก',
        'estimated_cost_allowance' => 'ค่าเบี้ยเลี้ยง',
        'estimated_cost_vehicle_fuel' => 'ค่าพาหนะ/น้ำมัน',
        'estimated_cost_other' => 'ค่าใช้จ่ายอื่น',
    ];

    /**
     * จำนวนกิจกรรมการพัฒนาของปี (ยกเว้นยกเลิก/ไม่อนุมัติ)
     */
    public static function activities(int $thaiYear): int
    {
        return (int) (new \yii\db\Query())
            ->from('development')
            ->where(['thai_year' => $thaiYear, 'deleted_at' => null])
            ->andWhere(['not in', 'status', self::EXCLUDED_DEV_STATUSES])
            ->count();
    }

    /**
     * คน-ครั้ง = ผู้ขอ (1 ต่อใบ) + สมาชิกคณะเดินทางทุกคน (นับซ้ำข้ามใบได้)
     * สะท้อน "ปริมาณงาน/โอกาสการพัฒนา" ต่างจากจำนวนคนไม่ซ้ำ
     */
    public static function personTimes(int $thaiYear): int
    {
        $requesters = self::activities($thaiYear); // 1 ผู้ขอ/ใบ ที่ไม่ถูกยกเลิก
        $members = (int) (new \yii\db\Query())
            ->from(['dd' => 'development_detail'])
            ->innerJoin(['d' => 'development'], 'd.id = dd.development_id')
            ->where([
                'dd.name' => 'member',
                'd.thai_year' => $thaiYear,
                'd.deleted_at' => null,
            ])
            ->andWhere(['not in', 'd.status', self::EXCLUDED_DEV_STATUSES])
            // สมาชิกที่ซ้ำกับผู้ขอในใบเดียวกัน ไม่นับซ้ำ (บางใบเก็บผู้ขอไว้ใน member ด้วย)
            ->andWhere('dd.emp_id <> d.emp_id')
            ->count();

        return $requesters + $members;
    }

    /**
     * จำนวนบุคลากร "ไม่ซ้ำ" ที่ได้รับการพัฒนาในปี (ผู้ขอ ∪ สมาชิกคณะเดินทาง)
     *
     * *** จุดแก้หลักของเฟส 0 *** — ของเดิมนับเฉพาะ DISTINCT ผู้ขอ ทำให้ coverage ต่ำกว่าจริงมาก
     */
    public static function personsDeveloped(int $thaiYear): int
    {
        $sql = "
            SELECT COUNT(*) FROM (
                SELECT d.emp_id
                FROM development d
                WHERE d.thai_year = :y AND d.deleted_at IS NULL
                  AND d.status NOT IN (:s1, :s2)
                  AND d.emp_id REGEXP '^[0-9]+$'
                UNION
                SELECT dd.emp_id
                FROM development_detail dd
                JOIN development d ON d.id = dd.development_id
                WHERE dd.name = 'member' AND d.thai_year = :y AND d.deleted_at IS NULL
                  AND d.status NOT IN (:s1, :s2)
                  AND dd.emp_id REGEXP '^[0-9]+$'
            ) AS people
        ";

        return (int) Yii::$app->db->createCommand($sql, [
            ':y' => $thaiYear,
            ':s1' => self::EXCLUDED_DEV_STATUSES[0],
            ':s2' => self::EXCLUDED_DEV_STATUSES[1],
        ])->queryScalar();
    }

    /**
     * คน-ครั้งการเข้าร่วม แยกตามหน่วยงาน (Top N) — ผู้ขอ + สมาชิกคณะเดินทาง
     * ใช้หน่วยงานของบุคคลจาก employees.department -> tree.name
     * @return array<int,array{name:string,n:int}>
     */
    public static function participationByDepartment(int $thaiYear, int $limit = 8): array
    {
        $sql = "
            SELECT COALESCE(t.name, 'ไม่ระบุหน่วยงาน') AS name, COUNT(*) AS n
            FROM (
                SELECT d.emp_id
                FROM development d
                WHERE d.thai_year = :y AND d.deleted_at IS NULL
                  AND d.status NOT IN (:s1, :s2) AND d.emp_id REGEXP '^[0-9]+$'
                UNION ALL
                SELECT dd.emp_id
                FROM development_detail dd
                JOIN development d ON d.id = dd.development_id
                WHERE dd.name = 'member' AND d.thai_year = :y AND d.deleted_at IS NULL
                  AND d.status NOT IN (:s1, :s2) AND dd.emp_id REGEXP '^[0-9]+$'
                  AND dd.emp_id <> d.emp_id
            ) p
            JOIN employees e ON e.id = p.emp_id
            LEFT JOIN tree t ON t.id = e.department
            GROUP BY e.department, t.name
            ORDER BY n DESC
            LIMIT :lim
        ";

        $rows = Yii::$app->db->createCommand($sql, [
            ':y' => $thaiYear,
            ':s1' => self::EXCLUDED_DEV_STATUSES[0],
            ':s2' => self::EXCLUDED_DEV_STATUSES[1],
            ':lim' => $limit,
        ])->queryAll();

        return array_map(static function ($r) {
            return ['name' => $r['name'], 'n' => (int) $r['n']];
        }, $rows);
    }

    /**
     * รายงานรายหน่วยงาน (เฟส 2) — coverage / คน-ครั้ง / งบใช้จริง / จำนวนกิจกรรม ต่อหน่วย
     *
     * การผูกหน่วยงาน:
     * - บุคลากร/ผู้เข้าร่วม ใช้หน่วยงานของ "ตัวบุคคล" (employees.department -> tree.name)
     * - งบใช้จริง/กิจกรรม ผูกกับหน่วยงานของ "ผู้ขอ" (เจ้าของใบเป็นผู้ถืองบเดินทาง)
     *
     * แสดงทุกหน่วยที่มีบุคลากรปฏิบัติงาน (แม้ coverage = 0) เพื่อเห็น gap
     * @return array<int,array<string,mixed>>
     */
    public static function byDepartment(int $thaiYear): array
    {
        $p = [':y' => $thaiYear, ':s1' => self::EXCLUDED_DEV_STATUSES[0], ':s2' => self::EXCLUDED_DEV_STATUSES[1]];
        $db = Yii::$app->db;

        // ฐาน: ทุกหน่วยที่มีบุคลากรปฏิบัติงาน
        $rows = [];
        $staff = (new \yii\db\Query())
            ->select(['dept_id' => 'e.department', 'name' => "COALESCE(t.name,'ไม่ระบุหน่วยงาน')", 'staff' => 'COUNT(*)'])
            ->from(['e' => 'employees'])
            ->leftJoin(['t' => 'tree'], 't.id = e.department')
            ->where(['e.status' => 1])
            ->groupBy(['e.department', 't.name'])
            ->all();
        foreach ($staff as $r) {
            $rows[(int) $r['dept_id']] = [
                'dept_id' => (int) $r['dept_id'], 'name' => $r['name'], 'staff' => (int) $r['staff'],
                'developed' => 0, 'person_times' => 0, 'activities' => 0, 'actual_spend' => 0.0, 'coverage_percent' => 0.0,
            ];
        }

        $devFrom = "
            SELECT d.emp_id FROM development d
            WHERE d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND d.emp_id REGEXP '^[0-9]+$'
            UNION
            SELECT dd.emp_id FROM development_detail dd JOIN development d ON d.id=dd.development_id
            WHERE dd.name='member' AND d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND dd.emp_id REGEXP '^[0-9]+$'
        ";
        // จำนวนคนไม่ซ้ำที่ได้รับการพัฒนา ต่อหน่วย
        foreach ($db->createCommand("SELECT e.department dept_id, COUNT(*) n FROM ($devFrom) persons JOIN employees e ON e.id=persons.emp_id GROUP BY e.department", $p)->queryAll() as $r) {
            if (isset($rows[(int) $r['dept_id']])) {
                $rows[(int) $r['dept_id']]['developed'] = (int) $r['n'];
            }
        }

        // คน-ครั้ง ต่อหน่วย (ผู้ขอ + สมาชิก นับซ้ำข้ามใบได้)
        $ptFrom = "
            SELECT d.emp_id FROM development d
            WHERE d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND d.emp_id REGEXP '^[0-9]+$'
            UNION ALL
            SELECT dd.emp_id FROM development_detail dd JOIN development d ON d.id=dd.development_id
            WHERE dd.name='member' AND d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND dd.emp_id REGEXP '^[0-9]+$' AND dd.emp_id<>d.emp_id
        ";
        foreach ($db->createCommand("SELECT e.department dept_id, COUNT(*) n FROM ($ptFrom) p JOIN employees e ON e.id=p.emp_id GROUP BY e.department", $p)->queryAll() as $r) {
            if (isset($rows[(int) $r['dept_id']])) {
                $rows[(int) $r['dept_id']]['person_times'] = (int) $r['n'];
            }
        }

        // กิจกรรม + งบใช้จริง (data_json) ต่อหน่วยของผู้ขอ
        $costSum = self::costSumExpr('d.data_json');
        $reqSql = "
            SELECT e.department dept_id, COUNT(*) activities, COALESCE(SUM($costSum),0) spend
            FROM development d JOIN employees e ON e.id=d.emp_id
            WHERE d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND d.emp_id REGEXP '^[0-9]+$'
            GROUP BY e.department
        ";
        foreach ($db->createCommand($reqSql, $p)->queryAll() as $r) {
            if (isset($rows[(int) $r['dept_id']])) {
                $rows[(int) $r['dept_id']]['activities'] = (int) $r['activities'];
                $rows[(int) $r['dept_id']]['actual_spend'] += (float) $r['spend'];
            }
        }

        // งบใช้จริงจากรายการเก่า (expense_type) ต่อหน่วยของผู้ขอ
        $legSql = "
            SELECT e.department dept_id, COALESCE(SUM(dd.price*COALESCE(dd.qty,1)),0) spend
            FROM development_detail dd
            JOIN development d ON d.id=dd.development_id
            JOIN employees e ON e.id=d.emp_id
            WHERE dd.name='expense_type' AND d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND d.emp_id REGEXP '^[0-9]+$'
            GROUP BY e.department
        ";
        foreach ($db->createCommand($legSql, $p)->queryAll() as $r) {
            if (isset($rows[(int) $r['dept_id']])) {
                $rows[(int) $r['dept_id']]['actual_spend'] += (float) $r['spend'];
            }
        }

        foreach ($rows as &$row) {
            $row['coverage_percent'] = $row['staff'] > 0 ? round($row['developed'] / $row['staff'] * 100, 2) : 0.0;
        }
        unset($row);

        // เรียงตามจำนวนบุคลากรมาก -> น้อย (หน่วยใหญ่ก่อน) ; view เน้นสี coverage ต่ำเอง
        usort($rows, static fn($a, $b) => $b['staff'] <=> $a['staff']);

        return array_values($rows);
    }

    /**
     * Drill-down รายคนในหน่วยงานหนึ่ง (เฟส 2) — บุคลากรทุกคนในหน่วย + จำนวนครั้งที่ได้รับการพัฒนา
     * แยกตามประเภทการพัฒนา (ประชุม/อบรม/วิทยากร/ดูงาน...) เพื่อไม่ให้เห็นแค่ยอดรวม
     * ครั้ง = 0 คือ "ยังไม่ได้รับการพัฒนา" (gap ที่นำไปตามได้)
     * @return array<int,array{id:int,name:string,times:int,by_code:array<string,int>}>
     *         by_code = จำนวนครั้งแยกตามรหัสประเภท (dev1..dev6) — รหัสที่ไม่รู้จัก/ว่าง รวมเข้า dev6
     */
    public static function departmentPeople(int $thaiYear, int $deptId): array
    {
        $p = [
            ':y' => $thaiYear, ':dept' => $deptId,
            ':s1' => self::EXCLUDED_DEV_STATUSES[0], ':s2' => self::EXCLUDED_DEV_STATUSES[1],
        ];

        // รายชื่อบุคลากรทุกคนในหน่วย (รวมคนที่ยังไม่พัฒนา = 0 ครั้ง)
        $name = self::fullnameExpr('e');
        $staff = Yii::$app->db->createCommand(
            "SELECT e.id, $name AS name FROM employees e WHERE e.status=1 AND e.department=:dept ORDER BY name ASC",
            [':dept' => $deptId]
        )->queryAll();

        $rows = [];
        foreach ($staff as $s) {
            $rows[(int) $s['id']] = [
                'id' => (int) $s['id'],
                'name' => trim((string) $s['name']) ?: ('#' . $s['id']),
                'times' => 0,
                'by_code' => [],
            ];
        }

        // จำนวนครั้งแยกตามประเภท ต่อคน (ผู้ขอ + คณะเดินทาง) เฉพาะคนในหน่วยนี้
        $typeFrom = "
            SELECT d.emp_id, d.development_type_id AS type_id FROM development d
            WHERE d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2) AND d.emp_id REGEXP '^[0-9]+$'
            UNION ALL
            SELECT dd.emp_id, d.development_type_id FROM development_detail dd JOIN development d ON d.id=dd.development_id
            WHERE dd.name='member' AND d.thai_year=:y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2)
              AND dd.emp_id REGEXP '^[0-9]+$' AND dd.emp_id <> d.emp_id
        ";
        $breakdown = Yii::$app->db->createCommand("
            SELECT u.emp_id, u.type_id AS code, COALESCE(c.title,'ไม่ระบุประเภท') AS title, COUNT(*) AS n
            FROM ($typeFrom) u
            JOIN employees e ON e.id = u.emp_id AND e.department = :dept
            LEFT JOIN categorise c ON c.code = u.type_id AND c.name='development_type'
            GROUP BY u.emp_id, u.type_id, c.title
            ORDER BY n DESC
        ", $p)->queryAll();

        foreach ($breakdown as $b) {
            $id = (int) $b['emp_id'];
            if (!isset($rows[$id])) {
                continue;
            }
            // รหัสที่ไม่อยู่ในชุดมาตรฐาน (null/legacy) รวมเข้า "อื่นๆ" เพื่อให้ผลรวมคอลัมน์ = จำนวนรวม
            $code = isset(self::TYPE_SHORT[$b['code']]) ? $b['code'] : 'dev6';
            $rows[$id]['times'] += (int) $b['n'];
            $rows[$id]['by_code'][$code] = ($rows[$id]['by_code'][$code] ?? 0) + (int) $b['n'];
        }

        // เรียงตามจำนวนครั้งมาก -> น้อย แล้วตามชื่อ
        usort($rows, static function ($a, $b) {
            return $b['times'] <=> $a['times'] ?: strcmp($a['name'], $b['name']);
        });

        return array_values($rows);
    }

    /**
     * สมุดพกการพัฒนารายบุคคล (เฟส 3) — ประวัติการพัฒนาของบุคลากรหนึ่งคน
     *
     * รวมทั้งบทบาท "ผู้ขอ" และ "คณะเดินทาง" ; นับวันพัฒนาโดยประมาณจากช่วงวันที่
     * คืน: ข้อมูลบุคคล, รายการกิจกรรมของปี, สถิติของปี, และสถิติสะสมทุกปี
     *
     * @return array{emp:array,activities:array,stats:array,lifetime:array}
     */
    public static function personPassport(int $thaiYear, int $empId): array
    {
        $s1 = self::EXCLUDED_DEV_STATUSES[0];
        $s2 = self::EXCLUDED_DEV_STATUSES[1];
        $db = Yii::$app->db;

        // ข้อมูลบุคคล
        $name = self::fullnameExpr('e');
        $emp = (new \yii\db\Query())
            ->select(['id' => 'e.id', 'name' => $name, 'dept' => 't.name'])
            ->from(['e' => 'employees'])
            ->leftJoin(['t' => 'tree'], 't.id = e.department')
            ->where(['e.id' => $empId])
            ->one();
        $emp = $emp ?: ['id' => $empId, 'name' => '#' . $empId, 'dept' => null];
        $emp['name'] = trim((string) $emp['name']) ?: ('#' . $empId);

        // นิพจน์จำนวนวัน (อย่างน้อย 1 วัน)
        $days = 'GREATEST(1, COALESCE(DATEDIFF(d.date_end, d.date_start) + 1, 1))';
        $typeJoin = "LEFT JOIN categorise c ON c.code = d.development_type_id AND c.name = 'development_type'";

        $unionYear = "
            SELECT d.id, d.topic, d.date_start, d.date_end, d.status, d.response_status,
                   d.development_type_id AS type_code, c.title AS type_title, 'requester' AS role, $days AS days
            FROM development d $typeJoin
            WHERE d.emp_id = :emp AND d.thai_year = :y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2)
            UNION ALL
            SELECT d.id, d.topic, d.date_start, d.date_end, d.status, d.response_status,
                   d.development_type_id, c.title, 'member', $days
            FROM development_detail dd
            JOIN development d ON d.id = dd.development_id $typeJoin
            WHERE dd.name = 'member' AND dd.emp_id = :emp AND dd.emp_id <> d.emp_id
              AND d.thai_year = :y AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2)
        ";

        $activities = $db->createCommand(
            "SELECT * FROM ($unionYear) a ORDER BY a.date_start DESC, a.id DESC",
            [':emp' => (string) $empId, ':y' => $thaiYear, ':s1' => $s1, ':s2' => $s2]
        )->queryAll();

        // สถิติของปี (จัดกลุ่มประเภทด้วยป้ายสั้น)
        $totalDays = 0;
        $types = [];
        $asSpeaker = 0;
        $asRequester = 0;
        foreach ($activities as &$a) {
            $totalDays += (int) $a['days'];
            $a['type_label'] = self::typeShort($a['type_code'] ?? null, $a['type_title'] ?? null);
            $key = $a['type_code'] ?: ($a['type_title'] ?: 'ไม่ระบุ');
            if (!isset($types[$key])) {
                $types[$key] = ['label' => $a['type_label'], 'n' => 0];
            }
            $types[$key]['n']++;
            if ($a['response_status'] === 'Accept') {
                $asSpeaker++;
            }
            if ($a['role'] === 'requester') {
                $asRequester++;
            }
        }
        unset($a);
        $stats = [
            'times' => count($activities),
            'days' => $totalDays,
            'types' => array_values($types),
            'type_count' => count($types),
            'as_speaker' => $asSpeaker,
            'as_requester' => $asRequester,
            'as_member' => count($activities) - $asRequester,
        ];

        // สถิติสะสมทุกปี
        $lifetime = $db->createCommand("
            SELECT COUNT(*) AS times, COALESCE(SUM(u.days),0) AS days, COUNT(DISTINCT u.thai_year) AS years
            FROM (
                SELECT d.id, d.thai_year, $days AS days
                FROM development d
                WHERE d.emp_id = :emp AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2)
                UNION ALL
                SELECT d.id, d.thai_year, $days
                FROM development_detail dd JOIN development d ON d.id = dd.development_id
                WHERE dd.name = 'member' AND dd.emp_id = :emp AND dd.emp_id <> d.emp_id
                  AND d.deleted_at IS NULL AND d.status NOT IN (:s1,:s2)
            ) u
        ", [':emp' => (string) $empId, ':s1' => $s1, ':s2' => $s2])->queryOne();

        return [
            'emp' => $emp,
            'activities' => $activities,
            'stats' => $stats,
            'lifetime' => [
                'times' => (int) ($lifetime['times'] ?? 0),
                'days' => (int) ($lifetime['days'] ?? 0),
                'years' => (int) ($lifetime['years'] ?? 0),
            ],
        ];
    }

    /**
     * ความครอบคลุม IDP ระดับองค์กร (เฟส 5, report-only) — % บุคลากรที่มีแผนพัฒนารายบุคคลในปีนั้น
     * ผูกปีงบผ่าน idp_cycle.fiscal_year ; ไม่นับแผนที่ยกเลิก
     * @return array{with_idp:int,active_staff:int,percent:float}
     */
    public static function idpCoverage(int $thaiYear): array
    {
        $withIdp = (int) (new \yii\db\Query())
            ->from(['p' => 'idp_plan'])
            ->innerJoin(['c' => 'idp_cycle'], 'c.id = p.cycle_id')
            ->where(['c.fiscal_year' => $thaiYear])
            ->andWhere(['<>', 'p.status', 'cancelled'])
            ->count('DISTINCT p.emp_id');
        $staff = self::activeStaff();

        return [
            'with_idp' => $withIdp,
            'active_staff' => $staff,
            'percent' => $staff > 0 ? round($withIdp / $staff * 100, 2) : 0.0,
        ];
    }

    /**
     * แผนพัฒนารายบุคคล (IDP) ของบุคลากรหนึ่งคน สำหรับแสดงคู่กับ passport (เฟส 5, report-only)
     * เลือกแผนของปีงบที่ระบุก่อน ถ้าไม่มีใช้แผนล่าสุด ; คืน null เมื่อไม่มีแผน
     * @return array|null plan + goals[] (แต่ละ goal มี activities[])
     */
    public static function personIdp(int $empId, ?int $thaiYear = null): ?array
    {
        $q = (new \yii\db\Query())
            ->select(['id' => 'p.id', 'status' => 'p.status', 'progress' => 'p.progress_percent', 'cycle' => 'c.title', 'fiscal_year' => 'c.fiscal_year'])
            ->from(['p' => 'idp_plan'])
            ->innerJoin(['c' => 'idp_cycle'], 'c.id = p.cycle_id')
            ->where(['p.emp_id' => $empId])
            ->andWhere(['<>', 'p.status', 'cancelled']);
        if ($thaiYear !== null) {
            $q->andWhere(['c.fiscal_year' => $thaiYear]);
        }
        $plan = $q->orderBy(['c.fiscal_year' => SORT_DESC, 'p.id' => SORT_DESC])->one();
        if (!$plan) {
            return null;
        }

        $goals = (new \yii\db\Query())
            ->from('idp_goal')
            ->where(['plan_id' => $plan['id']])
            ->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        foreach ($goals as &$g) {
            $g['activities'] = (new \yii\db\Query())
                ->from('idp_activity')
                ->where(['goal_id' => $g['id']])
                ->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
        }
        unset($g);
        $plan['goals'] = $goals;

        return $plan;
    }

    /** จำนวนบุคลากรที่ปฏิบัติงาน (ตัวหารของ coverage) */
    public static function activeStaff(): int
    {
        return (int) (new \yii\db\Query())
            ->from('employees')
            ->where(['status' => 1])
            ->count();
    }

    /**
     * งบที่ "ตั้งไว้" ตามแผนการเงิน = SUM(plan_order.order_price) ของ plan_item งบพัฒนาบุคลากร
     * (ทุกสถานะยกเว้น reject/ลบ — ให้ตรงกับหน้าติดตามแผนรายจ่าย /plan/overview)
     */
    public static function plannedBudget(int $thaiYear): float
    {
        return (float) (new \yii\db\Query())
            ->from('plan_order')
            ->where([
                'thai_year' => $thaiYear,
                'plan_item_id' => self::BUDGET_PLAN_ITEMS,
                'deleted_at' => null,
            ])
            ->andWhere(['not in', 'status', self::EXCLUDED_PLAN_STATUSES])
            ->sum('order_price');
    }

    /** นิพจน์ SQL แปลงค่าเงินหนึ่งคีย์ใน data_json เป็นตัวเลข (กัน comma หลักพัน/ค่าว่าง) */
    private static function costExpr(string $key, string $col = 'data_json'): string
    {
        return "COALESCE(CAST(REPLACE(JSON_UNQUOTE(JSON_EXTRACT($col, '$.$key')), ',', '') AS DECIMAL(15,2)), 0)";
    }

    /** ผลรวมทุกหมวดค่าใช้จ่ายใน data_json (คอลัมน์ $col) เป็นนิพจน์เดียว พร้อมกัน JSON เสีย */
    private static function costSumExpr(string $col = 'data_json'): string
    {
        $terms = [];
        foreach (array_keys(self::COST_COMPONENTS) as $k) {
            $terms[] = self::costExpr($k, $col);
        }
        return 'CASE WHEN JSON_VALID(' . $col . ') THEN (' . implode(' + ', $terms) . ') ELSE 0 END';
    }

    /** ยอดค่าใช้จ่ายจาก data_json ของปี — ถ้าไม่ระบุ $key รวมทุกหมวด, ถ้าระบุ รวมเฉพาะหมวดนั้น */
    private static function jsonSpend(int $thaiYear, ?string $key = null): float
    {
        $expr = $key === null
            ? self::costSumExpr('data_json')
            : 'CASE WHEN JSON_VALID(data_json) THEN ' . self::costExpr($key) . ' ELSE 0 END';

        $sql = "
            SELECT COALESCE(SUM($expr), 0)
            FROM development
            WHERE thai_year = :y AND deleted_at IS NULL AND status NOT IN (:s1, :s2)
        ";

        return (float) Yii::$app->db->createCommand($sql, [
            ':y' => $thaiYear,
            ':s1' => self::EXCLUDED_DEV_STATUSES[0],
            ':s2' => self::EXCLUDED_DEV_STATUSES[1],
        ])->queryScalar();
    }

    /** ยอดค่าใช้จ่ายจากรายการแบบเก่า (name = expense_type) ของปี */
    private static function legacyDetailSpend(int $thaiYear): float
    {
        return (float) (new \yii\db\Query())
            ->from(['dd' => 'development_detail'])
            ->innerJoin(['d' => 'development'], 'd.id = dd.development_id')
            ->where([
                'dd.name' => 'expense_type',
                'd.thai_year' => $thaiYear,
                'd.deleted_at' => null,
            ])
            ->andWhere(['not in', 'd.status', self::EXCLUDED_DEV_STATUSES])
            ->sum('dd.price * COALESCE(dd.qty, 1)');
    }

    /**
     * งบที่ตั้งไว้ "แยกตามรายการงบ" ในผังงบ (P42/P70) — รายการที่ยังไม่มีแผนแสดงยอด 0
     * ผลรวมของทุกแถวเท่ากับ plannedBudget()
     * @return array<int,array{code:string,title:string,amount:float}>
     */
    public static function plannedBudgetByItem(int $thaiYear): array
    {
        $rows = (new \yii\db\Query())
            ->select([
                'code' => 'i.code',
                'title' => 'i.title',
                'amount' => 'COALESCE(SUM(o.order_price), 0)',
            ])
            ->from(['i' => 'categorise'])
            ->leftJoin(
                ['o' => 'plan_order'],
                "o.plan_item_id = i.code AND o.thai_year = :y AND o.deleted_at IS NULL AND o.status NOT IN ('reject')",
                [':y' => $thaiYear]
            )
            ->where(['i.name' => 'plan_item', 'i.code' => self::BUDGET_PLAN_ITEMS])
            ->groupBy(['i.code', 'i.title'])
            ->orderBy(['i.code' => SORT_ASC])
            ->all();

        return array_map(static function ($r) {
            return ['code' => $r['code'], 'title' => $r['title'], 'amount' => (float) $r['amount']];
        }, $rows);
    }

    /**
     * งบที่ "ใช้จริง" ตามใบ development = ประมาณการใน data_json + รายการค่าใช้จ่ายเก่า (expense_type)
     * ตรงกับนิยามใน Development::estimatedCostAmounts() แต่รวมทั้งปีในคิวรีเดียว
     */
    public static function actualSpend(int $thaiYear): float
    {
        return self::jsonSpend($thaiYear) + self::legacyDetailSpend($thaiYear);
    }

    /**
     * งบใช้จริง "แยกตามหมวดค่าใช้จ่าย" (ลงทะเบียน/ที่พัก/เบี้ยเลี้ยง/พาหนะ/อื่นๆ) + รายการเก่า
     * ผลรวมของทุกแถวเท่ากับ actualSpend() — ใช้แสดงตารางแตกรายการในรายงาน
     * @return array<int,array{key:string,label:string,amount:float}>
     */
    public static function actualSpendByComponent(int $thaiYear): array
    {
        $rows = [];
        foreach (self::COST_COMPONENTS as $key => $label) {
            $rows[] = ['key' => $key, 'label' => $label, 'amount' => self::jsonSpend($thaiYear, $key)];
        }
        $legacy = self::legacyDetailSpend($thaiYear);
        if ($legacy > 0) {
            $rows[] = ['key' => 'legacy_detail', 'label' => 'รายการค่าใช้จ่าย (แบบเดิม)', 'amount' => $legacy];
        }
        return $rows;
    }

    /**
     * อัตราส่งสรุปผล (เชิงคุณภาพ) = ใบที่มีสรุปผลสถานะ submitted/acknowledged ÷ กิจกรรมทั้งหมด
     * คืน ['submitted','acknowledged','total','percent'] — total = activities ของปี
     */
    public static function summaryCompletion(int $thaiYear): array
    {
        $total = self::activities($thaiYear);

        $rows = (new \yii\db\Query())
            ->select(['s.status', 'n' => 'COUNT(*)'])
            ->from(['s' => 'development_summary'])
            ->innerJoin(['d' => 'development'], 'd.id = s.development_id')
            ->where(['d.thai_year' => $thaiYear, 'd.deleted_at' => null])
            ->andWhere(['not in', 'd.status', self::EXCLUDED_DEV_STATUSES])
            ->andWhere(['in', 's.status', ['submitted', 'acknowledged']])
            ->groupBy('s.status')
            ->all();

        $by = ['submitted' => 0, 'acknowledged' => 0];
        foreach ($rows as $r) {
            $by[$r['status']] = (int) $r['n'];
        }
        $done = $by['submitted'] + $by['acknowledged'];

        return [
            'submitted' => $by['submitted'],
            'acknowledged' => $by['acknowledged'],
            'total' => $total,
            'percent' => $total > 0 ? round($done / $total * 100, 2) : 0.0,
        ];
    }

    /**
     * นิพจน์ชื่อผู้ขอจากตาราง employees (alias e) — คำนำหน้า+ชื่อ+สกุล
     */
    private static function fullnameExpr(string $alias = 'e'): string
    {
        return "TRIM(CONCAT(COALESCE($alias.prefix,''), COALESCE($alias.fname,''), ' ', COALESCE($alias.lname,'')))";
    }

    /**
     * ติดตามการปิด loop สรุปผล (เชิงคุณภาพ HA) แยกสถานะละเอียด
     * @return array{acknowledged:int,submitted:int,draft:int,none:int,reported:int,total:int,percent:float}
     */
    public static function followupBreakdown(int $thaiYear): array
    {
        $total = self::activities($thaiYear);

        $rows = (new \yii\db\Query())
            ->select(['status' => 's.status', 'n' => 'COUNT(*)'])
            ->from(['s' => 'development_summary'])
            ->innerJoin(['d' => 'development'], 'd.id = s.development_id')
            ->where(['d.thai_year' => $thaiYear, 'd.deleted_at' => null])
            ->andWhere(['not in', 'd.status', self::EXCLUDED_DEV_STATUSES])
            ->groupBy('s.status')
            ->all();

        $by = ['draft' => 0, 'submitted' => 0, 'acknowledged' => 0];
        foreach ($rows as $r) {
            if (isset($by[$r['status']])) {
                $by[$r['status']] = (int) $r['n'];
            }
        }
        $withAny = $by['draft'] + $by['submitted'] + $by['acknowledged'];
        $reported = $by['submitted'] + $by['acknowledged'];

        return [
            'acknowledged' => $by['acknowledged'],
            'submitted' => $by['submitted'],
            'draft' => $by['draft'],
            'none' => max(0, $total - $withAny),
            'reported' => $reported,
            'total' => $total,
            'percent' => $total > 0 ? round($reported / $total * 100, 2) : 0.0,
        ];
    }

    /**
     * คลังการนำไปใช้ประโยชน์ (benefit register) — ใบที่ "รายงานผลแล้ว" (submitted/acknowledged)
     * แสดงสาระที่ได้ / การนำไปใช้ประโยชน์ / ข้อเสนอแนะ พร้อมผู้รายงานและหน่วยงาน
     * @return array<int,array<string,mixed>>
     */
    public static function benefitRegister(int $thaiYear, int $limit = 100): array
    {
        return (new \yii\db\Query())
            ->select([
                'id' => 'd.id',
                'topic' => 'd.topic',
                'date_start' => 'd.date_start',
                'status' => 's.status',
                'content' => 's.content',
                'benefit' => 's.benefit',
                'suggestion' => 's.suggestion',
                'ref' => 's.ref',
                'requester' => self::fullnameExpr('e'),
                'dept' => 't.name',
            ])
            ->from(['s' => 'development_summary'])
            ->innerJoin(['d' => 'development'], 'd.id = s.development_id')
            ->leftJoin(['e' => 'employees'], 'e.id = d.emp_id')
            ->leftJoin(['t' => 'tree'], 't.id = e.department')
            ->where(['d.thai_year' => $thaiYear, 'd.deleted_at' => null])
            ->andWhere(['in', 's.status', ['submitted', 'acknowledged']])
            ->andWhere(['not in', 'd.status', self::EXCLUDED_DEV_STATUSES])
            ->orderBy(['d.date_start' => SORT_DESC, 'd.id' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * รายการที่ "ยังไม่ปิด loop" — ยังไม่มีสรุปผล หรือค้างเป็นฉบับร่าง (chase list ที่นำไปตามได้จริง)
     * @return array<int,array<string,mixed>>
     */
    public static function pendingSummary(int $thaiYear, int $limit = 200): array
    {
        return (new \yii\db\Query())
            ->select([
                'id' => 'd.id',
                'topic' => 'd.topic',
                'date_start' => 'd.date_start',
                'dev_status' => 'd.status',
                'summary_status' => 's.status',
                'requester' => self::fullnameExpr('e'),
                'dept' => 't.name',
            ])
            ->from(['d' => 'development'])
            ->leftJoin(['s' => 'development_summary'], 's.development_id = d.id')
            ->leftJoin(['e' => 'employees'], 'e.id = d.emp_id')
            ->leftJoin(['t' => 'tree'], 't.id = e.department')
            ->where(['d.thai_year' => $thaiYear, 'd.deleted_at' => null])
            ->andWhere(['not in', 'd.status', self::EXCLUDED_DEV_STATUSES])
            ->andWhere(['or', ['s.id' => null], ['s.status' => 'draft']])
            ->orderBy(['d.date_start' => SORT_DESC, 'd.id' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * ชุดตัวชี้วัดระดับองค์กรของปี พร้อมเปรียบเทียบปีก่อนหน้า (YoY)
     *
     * ใช้เป็นแหล่งเดียวให้ Development::getYearlyDevelopmentSummary() และหน้ารายงานเฟสถัดไป
     */
    public static function orgSummary(int $thaiYear): array
    {
        $prevYear = $thaiYear - 1;

        $activities = self::activities($thaiYear);
        $prevActivities = self::activities($prevYear);

        $persons = self::personsDeveloped($thaiYear);
        $staff = self::activeStaff();

        $planned = self::plannedBudget($thaiYear);
        $actual = self::actualSpend($thaiYear);
        $prevActual = self::actualSpend($prevYear);

        return [
            'year' => $thaiYear,
            'activities' => $activities,
            'person_times' => self::personTimes($thaiYear),
            'persons_developed' => $persons,
            'active_staff' => $staff,
            'coverage_percent' => $staff > 0 ? round($persons / $staff * 100, 2) : 0.0,

            'planned_budget' => $planned,
            'actual_spend' => $actual,
            'budget_used_percent' => $planned > 0 ? round($actual / $planned * 100, 2) : 0.0,
            'budget_remaining' => $planned - $actual,
            'planned_by_item' => self::plannedBudgetByItem($thaiYear),
            'actual_by_component' => self::actualSpendByComponent($thaiYear),

            'summary' => self::summaryCompletion($thaiYear),

            'activities_change_percent' => self::changePercent($activities, $prevActivities),
            'spend_change_percent' => self::changePercent($actual, $prevActual),
        ];
    }

    /** % เปลี่ยนแปลงเทียบปีก่อน (0 เมื่อฐานเป็น 0) */
    private static function changePercent($current, $previous): float
    {
        $previous = (float) $previous;
        if ($previous == 0.0) {
            return 0.0;
        }
        return round((((float) $current - $previous) / $previous) * 100, 2);
    }
}
