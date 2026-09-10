<?php

namespace app\modules\hr\services;

use app\components\AppHelper;
use Yii;

/**
 * เครื่องคำนวณตัวชี้วัดสำหรับ HRD Dashboard V2 (ภาพรวมการพัฒนาบุคลากร)
 *
 * ออกแบบให้ "ไม่กระทบประสิทธิภาพระบบ":
 *  - ทุกตัวเลขคำนวณด้วย aggregate query เดียว (ไม่วนนับรายคนแบบ dashboard เดิม)
 *  - ผลลัพธ์ถูก cache ด้วย FileCache (@see CACHE_TTL) แยกตามปีงบประมาณ
 *    ภาระ query จึงเกิดครั้งเดียวต่อช่วง TTL ไม่ใช่ทุกครั้งที่เปิดหน้า
 *  - ทุก query ป้องกันตารางที่ยังไม่ถูก migrate (รพ.บางแห่งอาจยังไม่มีตาราง)
 *    ด้วย tableExists() แล้วคืนค่าว่าง/ศูนย์แทนการ throw
 *
 * ยึด "ปีงบประมาณไทย (พ.ศ.)" เป็นแกนเวลาของทุกตัวชี้วัด
 */
class HrdMetricsService
{
    /** ระยะเวลา cache ผลลัพธ์ (วินาที) — ข้อมูล HRD เปลี่ยนช้า (รายวัน/รายสัปดาห์) */
    public const CACHE_TTL = 1800;

    /** เกณฑ์ "ผ่าน" ของคะแนนสมรรถนะ (%) ใช้แยกครอบคลุม vs ช่องว่าง */
    public const COVERAGE_THRESHOLD = 80.0;

    private int $fiscalYear;

    public function __construct(?int $fiscalYear = null)
    {
        $this->fiscalYear = $fiscalYear ?: (int) AppHelper::YearBudget();
    }

    public function getFiscalYear(): int
    {
        return $this->fiscalYear;
    }

    // -----------------------------------------------------------------
    // KPI ทั้ง 6 ใบ (คืน array พร้อมค่าหลัก + ตัวตั้ง/ตัวหาร ไว้ทำ drill-down)
    // -----------------------------------------------------------------

    /**
     * รวม KPI ทุกใบเป็น payload เดียว (cache ทั้งก้อน)
     */
    public function kpis(): array
    {
        return $this->cache(__FUNCTION__, function () {
            return [
                'headcount' => $this->computeHeadcount(),
                'skillCoverage' => $this->computeSkillCoverage(),
                'trainingCompletion' => $this->computeTrainingCompletion(),
                'idpSuccess' => $this->computeIdpSuccess(),
                'successionReady' => $this->computeSuccessionReady(),
            ];
        });
    }

    /** จำนวนบุคลากรที่ปฏิบัติงานอยู่ (status=1, ไม่รวมบัญชีระบบ id=1) — ตรงกับ detailHeadcount */
    private function computeHeadcount(): array
    {
        $count = (int) $this->scalar(
            "SELECT COUNT(*) FROM employees WHERE status = 1 AND id <> 1"
        );
        return ['value' => $count];
    }

    /**
     * ความครอบคลุมทักษะ = % ผู้ถูกประเมินสมรรถนะรอบล่าสุดของปีงบที่คะแนน >= เกณฑ์
     * ช่องว่างทักษะ = ส่วนที่เหลือ (100 - coverage)
     */
    private function computeSkillCoverage(): array
    {
        $empty = ['percent' => null, 'gap_percent' => null, 'pass' => 0, 'total' => 0, 'round_id' => null];
        if (!$this->tableExists('hr_competency_evaluation') || !$this->tableExists('hr_competency_assignment') || !$this->tableExists('hr_appraisal_round')) {
            return $empty;
        }
        // รอบล่าสุดของปีงบที่เปิด/ปิดแล้ว (มีคะแนนจริง)
        $roundId = $this->scalar(
            "SELECT id FROM hr_appraisal_round
             WHERE fiscal_year = :fy AND status IN ('open', 'closed')
             ORDER BY round_no DESC, id DESC LIMIT 1",
            [':fy' => $this->fiscalYear]
        );
        if ($roundId === null || $roundId === false) {
            return $empty;
        }
        $row = $this->row(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN e.score_percent >= :th THEN 1 ELSE 0 END) AS pass
             FROM hr_competency_evaluation e
             INNER JOIN hr_competency_assignment a ON a.id = e.assignment_id
             WHERE a.round_id = :rid AND e.status = 'submitted' AND e.score_percent IS NOT NULL",
            [':th' => self::COVERAGE_THRESHOLD, ':rid' => (int) $roundId]
        );
        $total = (int) ($row['total'] ?? 0);
        $pass = (int) ($row['pass'] ?? 0);
        $percent = $total > 0 ? round($pass * 100 / $total, 1) : null;
        return [
            'percent' => $percent,
            'gap_percent' => $percent === null ? null : round(100 - $percent, 1),
            'pass' => $pass,
            'total' => $total,
            'round_id' => (int) $roundId,
        ];
    }

    /** การอบรมเสร็จสิ้น = แผนพัฒนารายบุคคลที่ completed / แผน active ในปีงบ */
    private function computeTrainingCompletion(): array
    {
        $empty = ['percent' => null, 'done' => 0, 'total' => 0];
        if (!$this->tableExists('employee_training_plan')) {
            return $empty;
        }
        $range = AppHelper::BudgetYearRange($this->fiscalYear);
        $row = $this->row(
            "SELECT
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS done,
                SUM(CASE WHEN status IN ('assigned','in_progress','assessment','completed') THEN 1 ELSE 0 END) AS total
             FROM employee_training_plan
             WHERE start_date BETWEEN :start AND :end",
            [':start' => $range['start'], ':end' => $range['end']]
        );
        $total = (int) ($row['total'] ?? 0);
        $done = (int) ($row['done'] ?? 0);
        return [
            'percent' => $total > 0 ? round($done * 100 / $total, 1) : null,
            'done' => $done,
            'total' => $total,
        ];
    }

    /** IDP สำเร็จ = idp_plan completed / plan ทั้งหมด (ไม่รวมยกเลิก) ในรอบ IDP ของปีงบ */
    private function computeIdpSuccess(): array
    {
        $empty = ['percent' => null, 'done' => 0, 'total' => 0, 'avg_progress' => null, 'cycle_id' => null];
        if (!$this->tableExists('idp_plan') || !$this->tableExists('idp_cycle')) {
            return $empty;
        }
        $cycleId = $this->scalar(
            "SELECT id FROM idp_cycle WHERE fiscal_year = :fy
             ORDER BY (status = 'active') DESC, id DESC LIMIT 1",
            [':fy' => $this->fiscalYear]
        );
        if ($cycleId === null || $cycleId === false) {
            return $empty;
        }
        $row = $this->row(
            "SELECT
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS done,
                SUM(CASE WHEN status <> 'cancelled' THEN 1 ELSE 0 END) AS total,
                AVG(progress_percent) AS avg_progress
             FROM idp_plan WHERE cycle_id = :cid",
            [':cid' => (int) $cycleId]
        );
        $total = (int) ($row['total'] ?? 0);
        $done = (int) ($row['done'] ?? 0);
        return [
            'percent' => $total > 0 ? round($done * 100 / $total, 1) : null,
            'done' => $done,
            'total' => $total,
            'avg_progress' => isset($row['avg_progress']) && $row['avg_progress'] !== null ? round((float) $row['avg_progress'], 1) : null,
            'cycle_id' => (int) $cycleId,
        ];
    }

    /** ผู้สืบทอดพร้อม = คนใน 9-box ที่ potential = สูง (box 7-9) / คนที่ถูกประเมินในปีงบ */
    private function computeSuccessionReady(): array
    {
        $empty = ['percent' => null, 'ready' => 0, 'total' => 0];
        if (!$this->tableExists('hr_talent_grid')) {
            return $empty;
        }
        $row = $this->row(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN potential = 3 THEN 1 ELSE 0 END) AS ready
             FROM hr_talent_grid WHERE fiscal_year = :fy",
            [':fy' => $this->fiscalYear]
        );
        $total = (int) ($row['total'] ?? 0);
        $ready = (int) ($row['ready'] ?? 0);
        return [
            'percent' => $total > 0 ? round($ready * 100 / $total, 1) : null,
            'ready' => $ready,
            'total' => $total,
        ];
    }

    // -----------------------------------------------------------------
    // แนวโน้มการพัฒนารายเดือน (สำหรับกราฟเส้น) ตลอดปีงบประมาณ
    // -----------------------------------------------------------------

    /**
     * คืน ['months' => ['ต.ค.', ...], 'series' => [['name'=>.., 'data'=>[..]], ...]]
     * 3 เส้น: อบรม/พัฒนา (training result), IDP (activity completed), ประเมินสมรรถนะ (evaluation submitted)
     */
    public function developmentTrend(): array
    {
        return $this->cache(__FUNCTION__, function () {
            $range = AppHelper::BudgetYearRange($this->fiscalYear);
            $months = $this->fiscalMonthKeys($range['start']); // 12 คีย์ Y-m ต.ค.->ก.ย.
            $labels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];

            $training = $this->monthlyCounts('employee_training_result', 'assessed_at', $range);
            $idp = $this->monthlyCounts('idp_activity', 'completed_at', $range);
            $assess = $this->monthlyCounts('hr_competency_evaluation', 'submitted_at', $range);

            $pick = function (array $map) use ($months) {
                return array_map(fn ($k) => (int) ($map[$k] ?? 0), $months);
            };
            return [
                'months' => $labels,
                'series' => [
                    ['name' => 'อบรม/พัฒนา', 'data' => $pick($training)],
                    ['name' => 'IDP', 'data' => $pick($idp)],
                    ['name' => 'ประเมินสมรรถนะ', 'data' => $pick($assess)],
                ],
            ];
        });
    }

    /**
     * นับจำนวนแถวรายเดือน (Y-m => count) จากคอลัมน์วันที่ ในช่วงปีงบ
     * ป้องกันตาราง/คอลัมน์ที่ยังไม่มี
     */
    private function monthlyCounts(string $table, string $dateCol, array $range): array
    {
        if (!$this->tableExists($table) || !$this->columnExists($table, $dateCol)) {
            return [];
        }
        $rows = Yii::$app->db->createCommand(
            "SELECT DATE_FORMAT($dateCol, '%Y-%m') AS ym, COUNT(*) AS cnt
             FROM {$table}
             WHERE $dateCol BETWEEN :start AND :end
             GROUP BY ym",
            [':start' => $range['start'] . ' 00:00:00', ':end' => $range['end'] . ' 23:59:59']
        )->queryAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['ym']] = (int) $r['cnt'];
        }
        return $out;
    }

    // -----------------------------------------------------------------
    // เฟส 2: feed กิจกรรม / งานรออนุมัติ / กำหนดการที่จะถึง
    // -----------------------------------------------------------------

    /** SQL ชื่อ-สกุลจากตาราง employees (alias) */
    private static function nameSql(string $a): string
    {
        return "TRIM(CONCAT(COALESCE({$a}.prefix,''), {$a}.fname, ' ', {$a}.lname))";
    }

    /**
     * กิจกรรมพัฒนาบุคลากรล่าสุดในระบบ (รวมจากหลายโมดูล เรียงเวลาใหม่->เก่า)
     * cache สั้น (5 นาที) เพราะเป็นข้อมูลเคลื่อนไหว
     *
     * @return array<int, array{at:string, icon:string, color:string, name:string, text:string, url:string}>
     */
    public function recentActivity(int $limit = 8): array
    {
        return $this->cache(__FUNCTION__, function () use ($limit) {
            $items = [];
            $name = self::nameSql('e');

            // IDP: ส่งแผน / ปิดแผน
            if ($this->tableExists('idp_plan')) {
                foreach ($this->all(
                    "SELECT {$name} AS name, p.submitted_at AS at, 'submitted' AS kind
                     FROM idp_plan p INNER JOIN employees e ON e.id = p.emp_id
                     WHERE p.submitted_at IS NOT NULL
                     UNION ALL
                     SELECT {$name} AS name, p.completed_at AS at, 'completed' AS kind
                     FROM idp_plan p INNER JOIN employees e ON e.id = p.emp_id
                     WHERE p.completed_at IS NOT NULL
                     ORDER BY at DESC LIMIT 12"
                ) as $r) {
                    $done = $r['kind'] === 'completed';
                    $items[] = [
                        'at' => $r['at'], 'icon' => $done ? 'bi-clipboard2-check' : 'bi-send',
                        'color' => $done ? 'success' : 'warning', 'name' => $r['name'],
                        'text' => $done ? 'ปิดแผน IDP เสร็จสิ้น' : 'ส่งแผน IDP รอเห็นชอบ',
                        'route' => ['/hr/idp/index'],
                    ];
                }
            }
            // ประเมินสมรรถนะ: ส่งผล
            if ($this->tableExists('hr_competency_evaluation') && $this->tableExists('hr_competency_assignment')) {
                foreach ($this->all(
                    "SELECT {$name} AS name, ev.submitted_at AS at
                     FROM hr_competency_evaluation ev
                     INNER JOIN hr_competency_assignment a ON a.id = ev.assignment_id
                     INNER JOIN employees e ON e.id = a.emp_id
                     WHERE ev.submitted_at IS NOT NULL
                     ORDER BY ev.submitted_at DESC LIMIT 12"
                ) as $r) {
                    $items[] = [
                        'at' => $r['at'], 'icon' => 'bi-bullseye', 'color' => 'primary',
                        'name' => $r['name'], 'text' => 'ส่งผลประเมินสมรรถนะ',
                        'route' => ['/hr/competency'],
                    ];
                }
            }
            // อบรม/พัฒนา: สำเร็จ
            if ($this->tableExists('employee_training_plan')) {
                foreach ($this->all(
                    "SELECT {$name} AS name, p.completed_at AS at
                     FROM employee_training_plan p INNER JOIN employees e ON e.id = p.emp_id
                     WHERE p.completed_at IS NOT NULL
                     ORDER BY p.completed_at DESC LIMIT 12"
                ) as $r) {
                    $items[] = [
                        'at' => $r['at'], 'icon' => 'bi-mortarboard', 'color' => 'info',
                        'name' => $r['name'], 'text' => 'แผนพัฒนา/อบรมสำเร็จ',
                        'route' => ['/hr/training-roadmap/index'],
                    ];
                }
            }

            usort($items, fn ($a, $b) => strcmp((string) $b['at'], (string) $a['at']));
            return array_slice($items, 0, $limit);
        }, 300);
    }

    /**
     * งานที่รอดำเนินการของ HR/หัวหน้า (นับจำนวน + ลิงก์ไปจัดการ) — แสดงเฉพาะที่ค้างจริง
     *
     * @return array<int, array{label:string, count:int, url:string, icon:string, color:string}>
     */
    public function workflowInbox(): array
    {
        return $this->cache(__FUNCTION__, function () {
            $out = [];
            $push = function ($label, $count, array $route, $icon, $color) use (&$out) {
                if ($count > 0) {
                    $out[] = ['label' => $label, 'count' => (int) $count, 'route' => $route, 'icon' => $icon, 'color' => $color];
                }
            };
            if ($this->tableExists('idp_plan')) {
                $idpRoute = ['/hr/idp/index'];
                $push('IDP รอหัวหน้าเห็นชอบ', $this->scalar("SELECT COUNT(*) FROM idp_plan WHERE status='submitted'"), $idpRoute, 'bi-person-check', 'warning');
                $push('IDP รอ HR เปิดบันทึก', $this->scalar("SELECT COUNT(*) FROM idp_plan WHERE status='approved'"), $idpRoute, 'bi-unlock', 'primary');
                $push('IDP รอปิดรอบ', $this->scalar("SELECT COUNT(*) FROM idp_plan WHERE status='assessment'"), $idpRoute, 'bi-flag', 'info');
            }
            if ($this->tableExists('employee_training_plan')) {
                $push('แผนอบรมรอประเมินผล', $this->scalar("SELECT COUNT(*) FROM employee_training_plan WHERE status='assessment'"), ['/hr/training-roadmap/index'], 'bi-clipboard-data', 'success');
            }
            // ประเมินสมรรถนะรอบเปิดของปีงบ ที่ยังไม่ส่งผล
            if ($this->tableExists('hr_appraisal_round') && $this->tableExists('hr_competency_assignment')) {
                $pending = $this->scalar(
                    "SELECT COUNT(*)
                     FROM hr_competency_assignment a
                     INNER JOIN hr_appraisal_round r ON r.id = a.round_id
                     LEFT JOIN hr_competency_evaluation ev ON ev.assignment_id = a.id AND ev.status='submitted'
                     WHERE r.fiscal_year = :fy AND r.status='open' AND ev.id IS NULL",
                    [':fy' => $this->fiscalYear]
                );
                $push('รอส่งผลประเมินสมรรถนะ', $pending, ['/hr/competency'], 'bi-bullseye', 'secondary');
            }
            return $out;
        }, 300);
    }

    /**
     * กำหนดการพัฒนาที่จะครบใน N วันข้างหน้า (แผนอบรมใกล้ครบกำหนด + กำหนดรอบ IDP)
     *
     * @return array<int, array{date:string, label:string, name:?string, icon:string, color:string}>
     */
    public function upcomingDeadlines(int $days = 60): array
    {
        return $this->cache(__FUNCTION__, function () use ($days) {
            $today = date('Y-m-d');
            $until = date('Y-m-d', strtotime("+{$days} day"));
            $items = [];

            if ($this->tableExists('employee_training_plan') && $this->columnExists('employee_training_plan', 'target_end_date')) {
                $name = self::nameSql('e');
                foreach ($this->all(
                    "SELECT {$name} AS name, p.target_end_date AS date
                     FROM employee_training_plan p INNER JOIN employees e ON e.id = p.emp_id
                     WHERE p.target_end_date BETWEEN :today AND :until
                       AND p.status IN ('assigned','in_progress','assessment')
                     ORDER BY p.target_end_date ASC LIMIT 15",
                    [':today' => $today, ':until' => $until]
                ) as $r) {
                    $items[] = ['date' => $r['date'], 'label' => 'ครบกำหนดแผนพัฒนา/อบรม', 'name' => $r['name'], 'icon' => 'bi-mortarboard', 'color' => 'info'];
                }
            }
            if ($this->tableExists('idp_cycle')) {
                foreach ($this->all(
                    "SELECT title, submission_due_date, review_due_date FROM idp_cycle
                     WHERE fiscal_year = :fy AND status='active' LIMIT 3",
                    [':fy' => $this->fiscalYear]
                ) as $c) {
                    foreach ([['submission_due_date', 'ครบกำหนดส่งแผน IDP'], ['review_due_date', 'ครบกำหนดทบทวน IDP']] as $d) {
                        $dt = $c[$d[0]] ?? null;
                        if ($dt && $dt >= $today && $dt <= $until) {
                            $items[] = ['date' => $dt, 'label' => $d[1] . ' (' . $c['title'] . ')', 'name' => null, 'icon' => 'bi-clipboard2-check', 'color' => 'warning'];
                        }
                    }
                }
            }
            usort($items, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));
            return array_slice($items, 0, 10);
        }, 300);
    }

    // -----------------------------------------------------------------
    // Drill-down: รายชื่อเบื้องหลังตัวเลขแต่ละ KPI (สำหรับเปิดใน modal)
    // -----------------------------------------------------------------

    /**
     * @return array{title:string, columns:array<int,array{label:string,align?:string}>, rows:array, empty:string}
     */
    public function detail(string $kpi): array
    {
        return $this->cache('detail_' . $kpi, function () use ($kpi) {
            switch ($kpi) {
                case 'headcount': return $this->detailHeadcount();
                case 'coverage': return $this->detailCoverage(true);
                case 'gap': return $this->detailCoverage(false);
                case 'training': return $this->detailTraining();
                case 'idp': return $this->detailIdp();
                case 'succession': return $this->detailSuccession();
                default: return ['title' => '', 'columns' => [], 'rows' => [], 'empty' => 'ไม่พบข้อมูล'];
            }
        }, 300);
    }

    private const PERSON_SELECT = "TRIM(CONCAT(COALESCE(e.prefix,''), e.fname, ' ', e.lname)) AS name,
        COALESCE(p.title,'ไม่ระบุ') AS position, COALESCE(org.name,'ไม่ระบุ') AS dept";
    private const PERSON_JOINS = "LEFT JOIN employee_position p ON p.id = e.employee_position_id
        LEFT JOIN tree org ON org.id = e.department AND org.tb_name = 'diagram'";

    private function detailHeadcount(): array
    {
        // ให้ตรงกับตัวเลขบนการ์ด (WorkforceController นับผู้ปฏิบัติราชการทุก branch: status=1)
        $rows = $this->all(
            "SELECT " . self::PERSON_SELECT . "
             FROM employees e " . self::PERSON_JOINS . "
             WHERE e.status=1 AND e.id<>1
             ORDER BY e.fname, e.lname"
        );
        return [
            'title' => 'บุคลากรที่ปฏิบัติงาน',
            'columns' => [['label' => 'ชื่อ-สกุล'], ['label' => 'ตำแหน่ง'], ['label' => 'หน่วยงาน']],
            'rows' => array_map(fn ($r) => [$r['name'], $r['position'], $r['dept']], $rows),
            'empty' => 'ไม่มีข้อมูลบุคลากร',
        ];
    }

    private function detailCoverage(bool $pass): array
    {
        $empty = ['title' => $pass ? 'ผู้ผ่านเกณฑ์ทักษะ' : 'ผู้มีช่องว่างทักษะ', 'columns' => [], 'rows' => [], 'empty' => 'ยังไม่มีผลประเมินสมรรถนะในปีนี้'];
        if (!$this->tableExists('hr_competency_evaluation') || !$this->tableExists('hr_competency_assignment') || !$this->tableExists('hr_appraisal_round')) {
            return $empty;
        }
        $roundId = $this->scalar(
            "SELECT id FROM hr_appraisal_round WHERE fiscal_year=:fy AND status IN ('open','closed') ORDER BY round_no DESC, id DESC LIMIT 1",
            [':fy' => $this->fiscalYear]
        );
        if (!$roundId) {
            return $empty;
        }
        $op = $pass ? '>=' : '<';
        $rows = $this->all(
            "SELECT " . self::PERSON_SELECT . ", ev.score_percent AS score
             FROM hr_competency_evaluation ev
             INNER JOIN hr_competency_assignment a ON a.id = ev.assignment_id
             INNER JOIN employees e ON e.id = a.emp_id " . self::PERSON_JOINS . "
             WHERE a.round_id=:rid AND ev.status='submitted' AND ev.score_percent IS NOT NULL
               AND ev.score_percent {$op} :th
             ORDER BY ev.score_percent " . ($pass ? 'DESC' : 'ASC'),
            [':rid' => (int) $roundId, ':th' => self::COVERAGE_THRESHOLD]
        );
        return [
            'title' => $pass ? 'ผู้ผ่านเกณฑ์ทักษะ (≥ ' . (int) self::COVERAGE_THRESHOLD . '%)' : 'ผู้มีช่องว่างทักษะ (< ' . (int) self::COVERAGE_THRESHOLD . '%)',
            'columns' => [['label' => 'ชื่อ-สกุล'], ['label' => 'หน่วยงาน'], ['label' => 'คะแนน', 'align' => 'end']],
            'rows' => array_map(fn ($r) => [$r['name'], $r['dept'], ['t' => rtrim(rtrim(number_format((float) $r['score'], 1), '0'), '.') . '%', 'align' => 'end']], $rows),
            'empty' => $empty['empty'],
        ];
    }

    private function detailTraining(): array
    {
        $empty = ['title' => 'แผนอบรมที่สำเร็จ', 'columns' => [], 'rows' => [], 'empty' => 'ยังไม่มีแผนพัฒนาที่สำเร็จในปีนี้'];
        if (!$this->tableExists('employee_training_plan')) {
            return $empty;
        }
        $range = AppHelper::BudgetYearRange($this->fiscalYear);
        $hasRoadmap = $this->tableExists('training_roadmap');
        $rmSelect = $hasRoadmap ? ", COALESCE(rm.title,'—') AS roadmap" : ", '—' AS roadmap";
        $rmJoin = $hasRoadmap ? "LEFT JOIN training_roadmap rm ON rm.id = tp.roadmap_id" : '';
        $rows = $this->all(
            "SELECT TRIM(CONCAT(COALESCE(e.prefix,''), e.fname, ' ', e.lname)) AS name,
                    COALESCE(org.name,'ไม่ระบุ') AS dept, tp.completed_at {$rmSelect}
             FROM employee_training_plan tp
             INNER JOIN employees e ON e.id = tp.emp_id
             LEFT JOIN tree org ON org.id = e.department AND org.tb_name='diagram'
             {$rmJoin}
             WHERE tp.status='completed' AND tp.start_date BETWEEN :s AND :en
             ORDER BY tp.completed_at DESC",
            [':s' => $range['start'], ':en' => $range['end']]
        );
        return [
            'title' => 'แผนอบรม/พัฒนาที่สำเร็จ',
            'columns' => [['label' => 'ชื่อ-สกุล'], ['label' => 'หน่วยงาน'], ['label' => 'หลักสูตร/Roadmap']],
            'rows' => array_map(fn ($r) => [$r['name'], $r['dept'], $r['roadmap']], $rows),
            'empty' => $empty['empty'],
        ];
    }

    private function detailIdp(): array
    {
        $empty = ['title' => 'IDP ที่เสร็จสิ้น', 'columns' => [], 'rows' => [], 'empty' => 'ยังไม่มี IDP ที่ปิดรอบในปีนี้'];
        if (!$this->tableExists('idp_plan') || !$this->tableExists('idp_cycle')) {
            return $empty;
        }
        $cycleId = $this->scalar(
            "SELECT id FROM idp_cycle WHERE fiscal_year=:fy ORDER BY (status='active') DESC, id DESC LIMIT 1",
            [':fy' => $this->fiscalYear]
        );
        if (!$cycleId) {
            return $empty;
        }
        $rows = $this->all(
            "SELECT TRIM(CONCAT(COALESCE(e.prefix,''), e.fname, ' ', e.lname)) AS name,
                    COALESCE(org.name,'ไม่ระบุ') AS dept, ip.completed_at
             FROM idp_plan ip
             INNER JOIN employees e ON e.id = ip.emp_id
             LEFT JOIN tree org ON org.id = e.department AND org.tb_name='diagram'
             WHERE ip.cycle_id=:cid AND ip.status='completed'
             ORDER BY ip.completed_at DESC",
            [':cid' => (int) $cycleId]
        );
        return [
            'title' => 'IDP ที่เสร็จสิ้น',
            'columns' => [['label' => 'ชื่อ-สกุล'], ['label' => 'หน่วยงาน']],
            'rows' => array_map(fn ($r) => [$r['name'], $r['dept']], $rows),
            'empty' => $empty['empty'],
        ];
    }

    private function detailSuccession(): array
    {
        $empty = ['title' => 'ผู้สืบทอดพร้อม', 'columns' => [], 'rows' => [], 'empty' => 'ยังไม่ได้จัด 9-Box ในปีนี้'];
        if (!$this->tableExists('hr_talent_grid')) {
            return $empty;
        }
        $rows = $this->all(
            "SELECT TRIM(CONCAT(COALESCE(e.prefix,''), e.fname, ' ', e.lname)) AS name,
                    COALESCE(org.name,'ไม่ระบุ') AS dept, g.performance, g.potential, g.box_no
             FROM hr_talent_grid g
             INNER JOIN employees e ON e.id = g.emp_id
             LEFT JOIN tree org ON org.id = e.department AND org.tb_name='diagram'
             WHERE g.fiscal_year=:fy AND g.potential=3
             ORDER BY g.box_no DESC, e.fname",
            [':fy' => $this->fiscalYear]
        );
        $lvl = [1 => 'ต่ำ', 2 => 'ปานกลาง', 3 => 'สูง'];
        return [
            'title' => 'ผู้สืบทอดพร้อม (High Potential)',
            'columns' => [['label' => 'ชื่อ-สกุล'], ['label' => 'หน่วยงาน'], ['label' => 'ผลงาน'], ['label' => 'ศักยภาพ'], ['label' => 'กล่อง', 'align' => 'end']],
            'rows' => array_map(fn ($r) => [
                $r['name'], $r['dept'], $lvl[(int) $r['performance']] ?? '-', $lvl[(int) $r['potential']] ?? '-',
                ['t' => (string) (int) $r['box_no'], 'align' => 'end'],
            ], $rows),
            'empty' => $empty['empty'],
        ];
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /** สร้างคีย์เดือน Y-m 12 เดือน เริ่มจากเดือนเริ่มปีงบ (ต.ค.) */
    private function fiscalMonthKeys(string $start): array
    {
        $keys = [];
        $ts = strtotime($start);
        for ($i = 0; $i < 12; $i++) {
            $keys[] = date('Y-m', strtotime("+$i month", $ts));
        }
        return $keys;
    }

    private function cache(string $suffix, callable $fn, ?int $ttl = null)
    {
        $cache = Yii::$app->has('cache') ? Yii::$app->cache : null;
        $key = ['hrd-metrics', $suffix, $this->fiscalYear];
        if ($cache !== null) {
            $hit = $cache->get($key);
            if ($hit !== false) {
                return $hit;
            }
        }
        $data = $fn();
        if ($cache !== null) {
            $cache->set($key, $data, $ttl ?? self::CACHE_TTL);
        }
        return $data;
    }

    /** ล้าง cache ของปีงบนี้ (เรียกหลังมีการเปลี่ยนข้อมูลสำคัญ ถ้าต้องการ realtime) */
    public function flush(): void
    {
        if (!Yii::$app->has('cache')) {
            return;
        }
        $suffixes = ['kpis', 'developmentTrend', 'recentActivity', 'workflowInbox', 'upcomingDeadlines'];
        foreach (['headcount', 'coverage', 'gap', 'training', 'idp', 'succession'] as $k) {
            $suffixes[] = 'detail_' . $k;
        }
        foreach ($suffixes as $suffix) {
            Yii::$app->cache->delete(['hrd-metrics', $suffix, $this->fiscalYear]);
        }
    }

    private function scalar(string $sql, array $params = [])
    {
        return Yii::$app->db->createCommand($sql, $params)->queryScalar();
    }

    private function row(string $sql, array $params = []): array
    {
        return Yii::$app->db->createCommand($sql, $params)->queryOne() ?: [];
    }

    private function all(string $sql, array $params = []): array
    {
        return Yii::$app->db->createCommand($sql, $params)->queryAll();
    }

    private function tableExists(string $table): bool
    {
        return Yii::$app->db->getTableSchema($table) !== null;
    }

    private function columnExists(string $table, string $column): bool
    {
        $schema = Yii::$app->db->getTableSchema($table);
        return $schema !== null && isset($schema->columns[$column]);
    }
}
