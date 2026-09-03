<?php

namespace app\modules\helpdesk2\helpers;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\models\Categorise;
use app\components\AppHelper;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskSlaSetting;

/**
 * เตรียมข้อมูล KPI / กราฟ / ตาราง สำหรับแดชบอร์ดงานซ่อม
 *
 * เป็น helper กลางใช้ได้ทั้ง 3 ศูนย์ (ทั่วไป=1 / คอมพิวเตอร์=2 / เครื่องมือแพทย์=3)
 * รองรับตัวกรอง (ปีงบ/ช่วงเวลา/ประเภทอุปกรณ์/ความเร่งด่วน/ช่าง/หน่วยงาน)
 * และเมื่อเปิด $haitMode จะคำนวณตัวชี้วัดเพิ่มตามมาตรฐาน HAIT หมวด 4
 * (MTTA / MTTR / %SLA / ความพึงพอใจ)
 */
class RepairDashboardV2Helper
{
    /** สถานะที่ถือว่า "เปิดค้าง" (ยังไม่ปิดงาน) */
    private const OPEN_STATUSES = ['pending', 'receive', 'in_progress'];

    /**
     * @param int|null $repairGroup 1=ทั่วไป, 2=คอม, 3=แพทย์ — null = ทุกกลุ่ม
     * @param array $filters ตัวกรองจาก query params: year, date_start, date_end,
     *                        device_type_id, urgency, technician, department
     * @param bool $haitMode เปิดการคำนวณตัวชี้วัด HAIT
     * @return array<string, mixed>
     */
    public static function prepareViewParams(?int $repairGroup = null, array $filters = [], bool $haitMode = false): array
    {
        $filters = self::normalizeFilters($filters);
        $range = self::resolveDateRange($filters);

        // ---- base query (ActiveQuery ของ Helpdesk) — reuse ได้ทั้ง dashboard และ drill-down ----
        $base = static function () use ($repairGroup, $filters) {
            return self::baseQuery($repairGroup, $filters);
        };

        // ---- นับพื้นฐาน ----
        $totalTickets = (int) $base()->count();
        $openTickets = (int) $base()->andWhere(['helpdesk.status' => self::OPEN_STATUSES])->count();
        $pendingTickets = (int) $base()->andWhere(['helpdesk.status' => 'pending'])->count();
        $inProgressTickets = (int) $base()->andWhere(['helpdesk.status' => 'in_progress'])->count();
        $resolvedToday = (int) $base()
            ->andWhere(['helpdesk.status' => 'success'])
            ->andWhere(new Expression('DATE(helpdesk.updated_at) = CURDATE()'))
            ->count();

        $statusSummary = $base()
            ->select(['status' => 'helpdesk.status', 'cnt' => 'COUNT(*)'])
            ->groupBy(['helpdesk.status'])
            ->asArray()
            ->all();

        $recentTickets = $base()
            ->orderBy(['helpdesk.created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // ---- หมวดปัญหายอดนิยม (แก้ N+1: preload map ประเภทอุปกรณ์) ----
        $deviceTypeMap = self::deviceTypeTitleMap();
        $topCategoriesRaw = $base()
            ->select(['device_type_id' => 'helpdesk.device_type_id', 'cnt' => 'COUNT(*)'])
            ->groupBy(['helpdesk.device_type_id'])
            ->orderBy(['cnt' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();
        $topCategories = [];
        foreach ($topCategoriesRaw as $row) {
            $code = (string) ($row['device_type_id'] ?? '');
            $topCategories[] = [
                'device_type_id' => $code,
                'title' => $deviceTypeMap[$code] ?? ($code !== '' ? $code : 'ไม่ระบุ'),
                'cnt' => (int) $row['cnt'],
            ];
        }

        // ---- ภาระงานช่าง (respect filter ช่วงเวลา/กลุ่ม) ----
        $staffWorkload = self::staffWorkload($repairGroup, $range, $filters);

        // ---- ตัวชี้วัดจาก timeline (SLA / MTTA / MTTR / ความพึงพอใจ) ----
        $metrics = self::computeMetrics($base());

        // ---- legacy keys (คงไว้ให้ view เดิมของศูนย์อื่นทำงานได้) ----
        $result = [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'pendingTickets' => $pendingTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedToday' => $resolvedToday,
            'statusSummary' => $statusSummary,
            'recentTickets' => $recentTickets,
            'topCategories' => $topCategories,
            'staffWorkload' => $staffWorkload,
            'slaNear' => $metrics['sla_near'],
            'slaBreached' => $metrics['sla_breached_open'],
        ];

        // ---- ข้อมูลเพิ่มสำหรับ HAIT ----
        $result['haitMode'] = $haitMode;
        $result['filters'] = $filters;
        $result['dateRange'] = $range;
        $result['filterOptions'] = self::filterOptions($repairGroup);
        $result['repairGroup'] = $repairGroup;
        $result['kpi'] = [
            'total' => $totalTickets,
            'open' => $openTickets,
            'pending' => $pendingTickets,
            'in_progress' => $inProgressTickets,
            'resolved_today' => $resolvedToday,
            'sla_met' => $metrics['sla_met'],
            'sla_breached' => $metrics['sla_breached'],
            'sla_breached_open' => $metrics['sla_breached_open'],
            'sla_pct' => $metrics['sla_pct'],
            'sla_evaluable' => $metrics['sla_evaluable'],
            'mtta_seconds' => $metrics['mtta_seconds'],
            'mtta_median_seconds' => $metrics['mtta_median_seconds'],
            'mtta_count' => $metrics['mtta_count'],
            'mttr_seconds' => $metrics['mttr_seconds'],
            'mttr_median_seconds' => $metrics['mttr_median_seconds'],
            'mttr_count' => $metrics['mttr_count'],
            'rating_avg' => $metrics['rating_avg'],
            'rating_count' => $metrics['rating_count'],
            'closed_total' => $metrics['closed_total'],
        ];

        // ---- รายงาน HAIT (ฉบับ 1: SLA ต่อบริการ, ฉบับ 2: Pareto) ----
        $result['slaByService'] = $metrics['sla_by_service'];
        $result['paretoDevice'] = $metrics['pareto_device'];
        $result['paretoDepartment'] = $metrics['pareto_department'];

        // ---- HAIT ระดับ 2: แนวโน้ม / Problem Management ----
        $result['monthlyTrend'] = $metrics['monthly_trend'];
        $scopeIds = $base()->select(['helpdesk.id'])->column();
        $result['rootCauses'] = HelpdeskTimelineHelper::rootCauseSummary($scopeIds, 10);

        // ---- HAIT 7: Capacity (อายุครุภัณฑ์คอมพิวเตอร์) — เฉพาะโหมด HAIT ----
        if ($haitMode) {
            $result['assetCapacity'] = self::assetCapacity('COM', $repairGroup);
        }

        return $result;
    }

    /**
     * สร้าง ActiveQuery พื้นฐานตามตัวกรอง — public เพื่อให้ drill-down ใช้ซ้ำ
     * (คืน query ที่ยังไม่ได้ select/order เพื่อให้ผู้เรียกต่อยอดได้)
     *
     * @param array $filters ตัวกรองดิบหรือที่ normalize แล้วก็ได้
     */
    public static function baseQuery(?int $repairGroup, array $filters): \yii\db\ActiveQuery
    {
        $filters = self::normalizeFilters($filters);
        $range = self::resolveDateRange($filters);

        $q = Helpdesk::find()->where(['helpdesk.name' => 'repair']);
        if ($repairGroup !== null) {
            $q->andWhere(['helpdesk.repair_group' => $repairGroup]);
        }
        if ($range['start'] && $range['end']) {
            $q->andWhere(['between', new Expression('DATE(helpdesk.created_at)'), $range['start'], $range['end']]);
        }
        if (!empty($filters['device_type_id'])) {
            $q->andWhere(['helpdesk.device_type_id' => $filters['device_type_id']]);
        }
        if ($filters['urgency'] !== null && $filters['urgency'] !== '') {
            $q->andWhere(['=', new Expression("JSON_UNQUOTE(JSON_EXTRACT(helpdesk.data_json, '$.urgency'))"), (string) $filters['urgency']]);
        }
        if (!empty($filters['department'])) {
            $q->andWhere(['helpdesk.created_by' => (new Query())
                ->select('user_id')->from('{{%employees}}')
                ->where(['department' => (int) $filters['department']])]);
        }
        if (!empty($filters['technician'])) {
            $q->andWhere(['exists', (new Query())
                ->from('{{%helpdesk_detail}} d')
                ->where('d.helpdesk_id = helpdesk.id')
                ->andWhere(['d.name' => HelpdeskTimelineHelper::DETAIL_NAME])
                ->andWhere(['d.emp_id' => (string) $filters['technician']])]);
        }
        return $q;
    }

    /** สถานะที่ถือว่า "เปิดค้าง" (เปิด public ให้ drill-down ใช้) */
    public static function openStatuses(): array
    {
        return self::OPEN_STATUSES;
    }

    /**
     * ดึงรายการงานย่อยตาม scope สำหรับ offcanvas drill-down
     *
     * @return array{0: array<int,array<string,mixed>>, 1: array<string,mixed>} [rows, meta]
     */
    public static function drilldownTickets(?int $repairGroup, array $filters, string $scope): array
    {
        $q = self::baseQuery($repairGroup, $filters);

        // เงื่อนไข scope ระดับ SQL
        $needsSlaFilter = false;
        $slaWant = null; // 'breached'|'met'|'pending'
        $onlyOpen = false;
        $wantService = null; // กรองตาม service_code (derive จาก catalog)
        $title = 'รายการงานทั้งหมด';

        if (str_starts_with($scope, 'service:')) {
            $wantService = substr($scope, 8);
            $catalog = HelpdeskSlaSetting::getRecord()->getServiceCatalog();
            $svcTitle = $wantService;
            foreach ($catalog as $entry) {
                if (($entry['code'] ?? '') === $wantService) {
                    $svcTitle = $entry['title'] ?? $wantService;
                    break;
                }
            }
            $title = 'บริการ: ' . $svcTitle;
        } elseif (str_starts_with($scope, 'status:')) {
            $code = substr($scope, 7);
            $q->andWhere(['helpdesk.status' => $code]);
            $title = 'สถานะ: ' . Helpdesk::repairStatusLabel($code);
        } elseif (str_starts_with($scope, 'device_type:')) {
            $code = substr($scope, 12);
            $q->andWhere(['helpdesk.device_type_id' => $code]);
            $map = self::deviceTypeTitleMap();
            $title = 'ประเภทอุปกรณ์: ' . ($map[$code] ?? $code);
        } elseif (str_starts_with($scope, 'month:')) {
            $month = substr($scope, 6); // YYYY-MM
            $q->andWhere(['=', new Expression("DATE_FORMAT(helpdesk.created_at,'%Y-%m')"), $month]);
            $title = 'เดือนที่แจ้ง: ' . $month;
        } elseif (str_starts_with($scope, 'department:')) {
            $deptId = (int) substr($scope, 11);
            $q->andWhere(['helpdesk.created_by' => (new Query())
                ->select('user_id')->from('{{%employees}}')
                ->where(['department' => $deptId])]);
            $deptName = (new Query())->select('name')->from('{{%tree}}')->where(['id' => $deptId])->scalar();
            $title = 'หน่วยงาน: ' . ($deptName ?: ('#' . $deptId));
        } else {
            switch ($scope) {
                case 'open':
                    $q->andWhere(['helpdesk.status' => self::OPEN_STATUSES]);
                    $title = 'งานที่เปิดค้าง';
                    break;
                case 'pending':
                    $q->andWhere(['helpdesk.status' => 'pending']);
                    $title = 'งานรอรับเรื่อง';
                    break;
                case 'in_progress':
                    $q->andWhere(['helpdesk.status' => 'in_progress']);
                    $title = 'งานกำลังดำเนินการ';
                    break;
                case 'closed':
                    $q->andWhere(['helpdesk.status' => 'success']);
                    $title = 'งานที่ปิดแล้ว';
                    break;
                case 'resolved_today':
                    $q->andWhere(['helpdesk.status' => 'success'])
                      ->andWhere(new Expression('DATE(helpdesk.updated_at) = CURDATE()'));
                    $title = 'งานที่ปิดวันนี้';
                    break;
                case 'rating':
                    $q->andWhere(['not', ['helpdesk.rating' => null]])
                      ->andWhere(['>', 'helpdesk.rating', 0]);
                    $title = 'งานที่มีการประเมินความพึงพอใจ';
                    break;
                case 'sla_breached':
                    $needsSlaFilter = true;
                    $slaWant = 'breached';
                    $title = 'งานที่เกิน SLA';
                    break;
                case 'sla_breached_open':
                    $q->andWhere(['helpdesk.status' => self::OPEN_STATUSES]);
                    $needsSlaFilter = true;
                    $slaWant = 'breached';
                    $onlyOpen = true;
                    $title = 'งานเปิดค้างที่เลยกำหนด SLA';
                    break;
                case 'mtta':
                    $title = 'งานที่มีเวลารับเรื่อง (เรียงช้า→เร็ว)';
                    break;
                case 'mttr':
                    $title = 'งานที่มีเวลาซ่อมเสร็จ (เรียงช้า→เร็ว)';
                    break;
                case 'total':
                case 'all':
                default:
                    $title = 'รายการงานทั้งหมด';
                    break;
            }
        }

        $rows = $q
            ->select([
                'id' => 'helpdesk.id',
                'repair_number' => 'helpdesk.repair_number',
                'ticket_title' => 'helpdesk.title',
                'status' => 'helpdesk.status',
                'device_type_id' => 'helpdesk.device_type_id',
                'data_json' => 'helpdesk.data_json',
                'created_at' => 'helpdesk.created_at',
                'updated_at' => 'helpdesk.updated_at',
                'receive_date' => 'helpdesk.receive_date',
                'rating' => 'helpdesk.rating',
                'requester' => new Expression("CONCAT(COALESCE(e.fname,''),' ',COALESCE(e.lname,''))"),
                'department' => 'o.name',
            ])
            ->leftJoin('{{%employees}} e', 'e.user_id = helpdesk.created_by')
            ->leftJoin('{{%tree}} o', 'o.id = e.department')
            ->orderBy(['helpdesk.created_at' => SORT_DESC])
            ->limit(500)
            ->asArray()
            ->all();

        $ids = array_column($rows, 'id');
        $timelines = HelpdeskTimelineHelper::forTicketIds($ids);
        $deviceMap = self::deviceTypeTitleMap();

        $out = [];
        foreach ($rows as $r) {
            $r['data_json'] = self::decodeJson($r['data_json']);
            $tl = HelpdeskTimelineHelper::withFallbackArray($timelines[(int) $r['id']] ?? [], $r);
            $sla = HelpdeskSlaHelper::slaResultFromData($r, $tl);

            if ($needsSlaFilter && $sla['status'] !== $slaWant) {
                continue;
            }
            if ($wantService !== null && $sla['service_code'] !== $wantService) {
                continue;
            }
            $ackSec = HelpdeskTimelineHelper::secondsToAcknowledge($tl);
            $resSec = HelpdeskTimelineHelper::secondsToResolve($tl);

            if ($scope === 'mtta' && $ackSec === null) {
                continue;
            }
            if ($scope === 'mttr' && $resSec === null) {
                continue;
            }

            $out[] = [
                'id' => (int) $r['id'],
                'repair_number' => (string) $r['repair_number'],
                'title' => (string) $r['ticket_title'],
                'status' => (string) $r['status'],
                'status_label' => Helpdesk::repairStatusLabel($r['status']),
                'urgency' => HelpdeskSlaHelper::normalizeUrgencyValue($r['data_json']['urgency'] ?? null),
                'device_type' => $deviceMap[(string) $r['device_type_id']] ?? (string) $r['device_type_id'],
                'created_at' => (string) $r['created_at'],
                'requester' => trim((string) $r['requester']),
                'department' => (string) ($r['department'] ?? ''),
                'rating' => is_numeric($r['rating']) ? (float) $r['rating'] : null,
                'sla_status' => $sla['status'],
                'ack_seconds' => $ackSec,
                'resolve_seconds' => $resSec,
            ];
        }

        // เรียงพิเศษสำหรับ scope เวลา
        if ($scope === 'mtta') {
            usort($out, static fn($a, $b) => ($b['ack_seconds'] ?? 0) <=> ($a['ack_seconds'] ?? 0));
        } elseif ($scope === 'mttr') {
            usort($out, static fn($a, $b) => ($b['resolve_seconds'] ?? 0) <=> ($a['resolve_seconds'] ?? 0));
        }

        $meta = [
            'title' => $title,
            'count' => count($out),
            'scope' => $scope,
        ];

        return [$out, $meta];
    }

    /**
     * คำนวณตัวชี้วัดจาก timeline สำหรับงานทั้งหมดในขอบเขต (batch, กัน N+1)
     *
     * @param \yii\db\ActiveQuery $scopeQuery
     * @return array<string,mixed>
     */
    private static function computeMetrics($scopeQuery): array
    {
        $rows = $scopeQuery
            ->select([
                'id' => 'helpdesk.id',
                'status' => 'helpdesk.status',
                'device_type_id' => 'helpdesk.device_type_id',
                'data_json' => 'helpdesk.data_json',
                'created_at' => 'helpdesk.created_at',
                'updated_at' => 'helpdesk.updated_at',
                'receive_date' => 'helpdesk.receive_date',
                'rating' => 'helpdesk.rating',
                'department' => 'o.name',
                'department_id' => 'o.id',
            ])
            ->leftJoin('{{%employees}} e', 'e.user_id = helpdesk.created_by')
            ->leftJoin('{{%tree}} o', 'o.id = e.department')
            ->asArray()
            ->all();

        $ids = array_column($rows, 'id');
        $timelines = HelpdeskTimelineHelper::forTicketIds($ids);
        $deviceMap = self::deviceTypeTitleMap();

        // ---- buckets สำหรับรายงาน HAIT ----
        $svcBuckets = [];      // service_code => ['title','count','met','breached','resolve_secs'=>[]]
        $paretoDevice = [];    // device_type_id => count
        $paretoDept = [];      // dept_id => ['name','cnt']
        $monthly = [];         // 'YYYY-MM' => ['count','met','breached','mttr'=>[]]

        $mttaTotal = 0;
        $mttaCount = 0;
        $mttaValues = [];
        $mttrTotal = 0;
        $mttrCount = 0;
        $mttrValues = [];
        $slaMet = 0;
        $slaBreached = 0;       // เกิน SLA (รวมงานปิดที่เกิน + งานเปิดที่เลยกำหนด)
        $slaBreachedOpen = 0;   // เฉพาะงานเปิดที่เลยกำหนด
        $slaNear = 0;
        $ratingTotal = 0;
        $ratingCount = 0;
        $closedTotal = 0;

        foreach ($rows as $r) {
            $id = (int) $r['id'];
            $r['data_json'] = self::decodeJson($r['data_json']);

            $tl = HelpdeskTimelineHelper::withFallbackArray($timelines[$id] ?? [], $r);

            // MTTA / MTTR
            $ackSec = HelpdeskTimelineHelper::secondsToAcknowledge($tl);
            if ($ackSec !== null) {
                $mttaTotal += $ackSec;
                $mttaCount++;
                $mttaValues[] = $ackSec;
            }
            $resSec = HelpdeskTimelineHelper::secondsToResolve($tl);
            if ($resSec !== null) {
                $mttrTotal += $resSec;
                $mttrCount++;
                $mttrValues[] = $resSec;
            }

            $isClosed = Helpdesk::normalizeRepairStatus($r['status']) === 'success';
            if ($isClosed) {
                $closedTotal++;
            }

            // SLA
            $sla = HelpdeskSlaHelper::slaResultFromData($r, $tl);
            switch ($sla['status']) {
                case 'met':
                    $slaMet++;
                    break;
                case 'breached':
                    $slaBreached++;
                    if (!$isClosed) {
                        $slaBreachedOpen++;
                    }
                    break;
                case 'pending':
                    // งานเปิดที่ยังไม่เกิน — เช็คว่าใกล้ครบกำหนด (เหลือ < 20% ของเวลา)
                    if (self::isNearDeadline($sla)) {
                        $slaNear++;
                    }
                    break;
            }

            // ---- bucket รายงาน SLA ต่อรายการบริการ (นับเฉพาะที่ประเมินได้ met/breached) ----
            $svcCode = $sla['service_code'];
            if (!isset($svcBuckets[$svcCode])) {
                $svcBuckets[$svcCode] = [
                    'code' => $svcCode,
                    'title' => $sla['service_title'],
                    'count' => 0,
                    'met' => 0,
                    'breached' => 0,
                    'resolve_secs' => [],
                ];
            }
            if (in_array($sla['status'], ['met', 'breached'], true)) {
                $svcBuckets[$svcCode]['count']++;
                if ($sla['status'] === 'met') {
                    $svcBuckets[$svcCode]['met']++;
                } else {
                    $svcBuckets[$svcCode]['breached']++;
                }
                if ($resSec !== null) {
                    $svcBuckets[$svcCode]['resolve_secs'][] = $resSec;
                }
            }

            // ---- Pareto ----
            $dtCode = (string) ($r['device_type_id'] ?? '');
            $dtKey = $dtCode !== '' ? $dtCode : '__none__';
            $paretoDevice[$dtKey] = ($paretoDevice[$dtKey] ?? 0) + 1;

            // ---- แนวโน้มรายเดือน (ตามเดือนที่แจ้ง) ----
            $month = substr((string) ($r['created_at'] ?? ''), 0, 7);
            if ($month !== '') {
                if (!isset($monthly[$month])) {
                    $monthly[$month] = ['count' => 0, 'met' => 0, 'breached' => 0, 'mttr' => []];
                }
                $monthly[$month]['count']++;
                if ($sla['status'] === 'met') {
                    $monthly[$month]['met']++;
                } elseif ($sla['status'] === 'breached') {
                    $monthly[$month]['breached']++;
                }
                if ($resSec !== null) {
                    $monthly[$month]['mttr'][] = $resSec;
                }
            }

            $deptName = trim((string) ($r['department'] ?? ''));
            $deptId = (int) ($r['department_id'] ?? 0);
            $deptKey = $deptId > 0 ? $deptId : 0;
            if (!isset($paretoDept[$deptKey])) {
                $paretoDept[$deptKey] = ['name' => $deptName !== '' ? $deptName : 'ไม่ระบุหน่วยงาน', 'cnt' => 0];
            }
            $paretoDept[$deptKey]['cnt']++;

            // ความพึงพอใจ
            $rating = is_numeric($r['rating']) ? (float) $r['rating'] : null;
            if ($rating !== null && $rating > 0) {
                $ratingTotal += $rating;
                $ratingCount++;
            }
        }

        // ---- สรุป bucket รายการบริการ ----
        $slaByService = [];
        foreach ($svcBuckets as $b) {
            $secs = $b['resolve_secs'];
            $slaByService[] = [
                'code' => $b['code'],
                'title' => $b['title'],
                'count' => $b['count'],
                'met' => $b['met'],
                'breached' => $b['breached'],
                'pct' => $b['count'] > 0 ? round(($b['met'] / $b['count']) * 100, 1) : null,
                'max_secs' => !empty($secs) ? max($secs) : null,
                'min_secs' => !empty($secs) ? min($secs) : null,
                'avg_secs' => !empty($secs) ? (int) round(array_sum($secs) / count($secs)) : null,
            ];
        }
        usort($slaByService, static fn($a, $b) => $b['count'] <=> $a['count']);

        // ---- สรุป Pareto (เรียงมาก→น้อย) ----
        arsort($paretoDevice);
        $paretoDeviceOut = [];
        foreach ($paretoDevice as $code => $cnt) {
            $real = $code === '__none__' ? '' : $code;
            $paretoDeviceOut[] = [
                'code' => $real,
                'title' => $real !== '' ? ($deviceMap[$real] ?? $real) : 'ไม่ระบุ',
                'cnt' => $cnt,
            ];
        }
        uasort($paretoDept, static fn($a, $b) => $b['cnt'] <=> $a['cnt']);
        $paretoDeptOut = [];
        foreach ($paretoDept as $id => $d) {
            $paretoDeptOut[] = ['id' => (int) $id, 'title' => $d['name'], 'cnt' => $d['cnt']];
        }

        // ---- สรุปแนวโน้มรายเดือน (เรียงตามเดือน) ----
        ksort($monthly);
        $monthlyOut = [];
        foreach ($monthly as $m => $b) {
            $eval = $b['met'] + $b['breached'];
            $monthlyOut[] = [
                'month' => $m,
                'count' => $b['count'],
                'sla_pct' => $eval > 0 ? round(($b['met'] / $eval) * 100, 1) : null,
                'mttr_median_secs' => self::median($b['mttr']),
            ];
        }

        $slaEvaluable = $slaMet + $slaBreached;
        $slaPct = $slaEvaluable > 0 ? round(($slaMet / $slaEvaluable) * 100, 1) : null;

        return [
            'mtta_seconds' => $mttaCount > 0 ? (int) round($mttaTotal / $mttaCount) : null,
            'mtta_median_seconds' => self::median($mttaValues),
            'mtta_count' => $mttaCount,
            'mttr_seconds' => $mttrCount > 0 ? (int) round($mttrTotal / $mttrCount) : null,
            'mttr_median_seconds' => self::median($mttrValues),
            'mttr_count' => $mttrCount,
            'sla_met' => $slaMet,
            'sla_breached' => $slaBreached,
            'sla_breached_open' => $slaBreachedOpen,
            'sla_near' => $slaNear,
            'sla_evaluable' => $slaEvaluable,
            'sla_pct' => $slaPct,
            'rating_avg' => $ratingCount > 0 ? round($ratingTotal / $ratingCount, 2) : null,
            'rating_count' => $ratingCount,
            'closed_total' => $closedTotal,
            'sla_by_service' => $slaByService,
            'pareto_device' => $paretoDeviceOut,
            'pareto_department' => $paretoDeptOut,
            'monthly_trend' => $monthlyOut,
        ];
    }

    /**
     * ใกล้ครบกำหนด SLA เมื่อเหลือเวลา < 20% ของ resolve_minutes
     */
    private static function isNearDeadline(array $sla): bool
    {
        if (empty($sla['deadline'])) {
            return false;
        }
        try {
            $deadlineTs = (new \DateTimeImmutable($sla['deadline']))->getTimestamp();
        } catch (\Throwable $e) {
            return false;
        }
        $remaining = $deadlineTs - time();
        if ($remaining <= 0) {
            return false;
        }
        $window = (float) ($sla['resolve_minutes'] ?? 0) * 60 * 0.2;
        return $window > 0 && $remaining <= $window;
    }

    /**
     * ภาระงานช่างจากบันทึกการปฏิบัติงาน (respect ตัวกรองกลุ่ม/ช่วงเวลา/ช่าง)
     */
    private static function staffWorkload(?int $repairGroup, array $range, array $filters): array
    {
        $q = Helpdesk::find()
            ->alias('h')
            ->select([
                'e.id AS emp_id',
                "CONCAT(e.fname, ' ', e.lname) AS fullname",
                'COUNT(DISTINCT h.id) AS total',
                "SUM(CASE WHEN h.status IN ('pending','receive') THEN 1 ELSE 0 END) AS open_total",
                "SUM(CASE WHEN h.status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_total",
                "SUM(CASE WHEN h.status = 'success' THEN 1 ELSE 0 END) AS success_total",
            ])
            ->innerJoin('{{%helpdesk_detail}} d', 'd.helpdesk_id = h.id AND d.name = :name', [':name' => HelpdeskTimelineHelper::DETAIL_NAME])
            ->innerJoin('{{%employees}} e', 'e.id = d.emp_id')
            ->where(['h.name' => 'repair']);

        if ($repairGroup !== null) {
            $q->andWhere(['h.repair_group' => $repairGroup]);
        }
        if ($range['start'] && $range['end']) {
            $q->andWhere(['between', new Expression('DATE(h.created_at)'), $range['start'], $range['end']]);
        }
        if (!empty($filters['technician'])) {
            $q->andWhere(['e.id' => (int) $filters['technician']]);
        }

        return $q->groupBy(['e.id', 'e.fname', 'e.lname'])
            ->orderBy(['total' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();
    }

    /**
     * ศักยภาพ/อายุครุภัณฑ์คอมพิวเตอร์ (HAIT 7 — Capacity Management)
     * แบ่งช่วงอายุ 0-3 / 4-7 / >7 ปี พร้อมจำนวนงานซ่อมที่ผูกกับครุภัณฑ์ได้
     *
     * @param string $assetType asset_type_id (ค่าเริ่มต้น COM)
     * @param int|null $repairGroup กลุ่มงานซ่อมที่ใช้เชื่อม (2=คอม)
     * @return array{bands:array<int,array{band:string,assets:int,repairs:int}>, total:int, linked:int}
     */
    public static function assetCapacity(string $assetType = 'COM', ?int $repairGroup = 2): array
    {
        $bandExpr = "CASE
            WHEN a.receive_date IS NULL THEN 'ไม่ทราบอายุ'
            WHEN TIMESTAMPDIFF(YEAR, a.receive_date, CURDATE()) <= 3 THEN '0-3 ปี'
            WHEN TIMESTAMPDIFF(YEAR, a.receive_date, CURDATE()) <= 7 THEN '4-7 ปี'
            ELSE 'มากกว่า 7 ปี' END";

        $rows = (new Query())
            ->select([
                'band' => new Expression($bandExpr),
                'assets' => 'COUNT(DISTINCT a.id)',
                'repairs' => 'COUNT(h.id)',
            ])
            ->from('{{%asset}} a')
            ->leftJoin(
                '{{%helpdesk}} h',
                'h.asset_number = a.fsn_number AND h.name = :rep' . ($repairGroup !== null ? ' AND h.repair_group = :grp' : ''),
                array_merge([':rep' => 'repair'], $repairGroup !== null ? [':grp' => $repairGroup] : [])
            )
            ->where(['a.deleted_at' => null, 'a.asset_type_id' => $assetType])
            ->groupBy(['band'])
            ->all();

        // เรียงตามลำดับอายุที่ต้องการ
        $order = ['0-3 ปี' => 1, '4-7 ปี' => 2, 'มากกว่า 7 ปี' => 3, 'ไม่ทราบอายุ' => 4];
        usort($rows, static fn($a, $b) => ($order[$a['band']] ?? 9) <=> ($order[$b['band']] ?? 9));

        $total = 0;
        $linked = 0;
        $bands = [];
        foreach ($rows as $r) {
            $bands[] = [
                'band' => (string) $r['band'],
                'assets' => (int) $r['assets'],
                'repairs' => (int) $r['repairs'],
            ];
            $total += (int) $r['assets'];
            $linked += (int) $r['repairs'];
        }

        return ['bands' => $bands, 'total' => $total, 'linked' => $linked];
    }

    /**
     * แผนที่ code → ชื่อประเภทอุปกรณ์ (preload กัน N+1)
     *
     * @return array<string,string>
     */
    private static function deviceTypeTitleMap(): array
    {
        $rows = Categorise::find()
            ->select(['code', 'title'])
            ->where(['name' => 'device_type'])
            ->asArray()
            ->all();
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['code']] = (string) $r['title'];
        }
        return $map;
    }

    /**
     * ตัวเลือกสำหรับแถบกรอง (คงที่ตามกลุ่ม ไม่ขึ้นกับตัวกรองอื่น)
     */
    private static function filterOptions(?int $repairGroup): array
    {
        $scope = static function (Query $q) use ($repairGroup): Query {
            $q->andWhere(['name' => 'repair']);
            if ($repairGroup !== null) {
                $q->andWhere(['repair_group' => $repairGroup]);
            }
            return $q;
        };

        // ปีงบประมาณ
        $years = $scope((new Query())->select('thai_year')->distinct()->from('{{%helpdesk}}'))
            ->andWhere(['not', ['thai_year' => null]])
            ->orderBy(['thai_year' => SORT_DESC])
            ->column();
        $years = array_map('intval', $years);
        $currentYear = (int) AppHelper::YearBudget();
        if (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }

        // ประเภทอุปกรณ์ที่ใช้จริง
        $typeMap = self::deviceTypeTitleMap();
        $usedTypes = $scope((new Query())->select('device_type_id')->distinct()->from('{{%helpdesk}}'))
            ->andWhere(['not', ['device_type_id' => null]])
            ->column();
        $deviceTypes = [];
        foreach ($usedTypes as $code) {
            $code = (string) $code;
            if ($code === '') {
                continue;
            }
            $deviceTypes[$code] = $typeMap[$code] ?? $code;
        }
        asort($deviceTypes);

        // ช่าง (จากบันทึกปฏิบัติงาน)
        $techQ = (new Query())
            ->select(['e.id AS id', "CONCAT(e.fname,' ',e.lname) AS name"])
            ->distinct()
            ->from('{{%helpdesk_detail}} d')
            ->innerJoin('{{%helpdesk}} h', 'h.id = d.helpdesk_id')
            ->innerJoin('{{%employees}} e', 'e.id = d.emp_id')
            ->where(['d.name' => HelpdeskTimelineHelper::DETAIL_NAME, 'h.name' => 'repair']);
        if ($repairGroup !== null) {
            $techQ->andWhere(['h.repair_group' => $repairGroup]);
        }
        $technicians = [];
        foreach ($techQ->all() as $r) {
            $technicians[(int) $r['id']] = trim((string) $r['name']) ?: ('#' . $r['id']);
        }
        asort($technicians);

        // หน่วยงานผู้แจ้ง
        $deptQ = (new Query())
            ->select(['o.id AS id', 'o.name AS name'])
            ->distinct()
            ->from('{{%helpdesk}} h')
            ->innerJoin('{{%employees}} e', 'e.user_id = h.created_by')
            ->innerJoin('{{%tree}} o', 'o.id = e.department')
            ->where(['h.name' => 'repair']);
        if ($repairGroup !== null) {
            $deptQ->andWhere(['h.repair_group' => $repairGroup]);
        }
        $departments = [];
        foreach ($deptQ->all() as $r) {
            if (!empty($r['name'])) {
                $departments[(int) $r['id']] = (string) $r['name'];
            }
        }
        asort($departments);

        return [
            'years' => $years,
            'deviceTypes' => $deviceTypes,
            'technicians' => $technicians,
            'departments' => $departments,
            'urgencies' => [
                'critical' => 'วิกฤต',
                'high' => 'สูง',
                'medium' => 'ปานกลาง',
                'low' => 'ต่ำ',
            ],
        ];
    }

    /**
     * normalize ตัวกรองจาก query params
     */
    private static function normalizeFilters(array $filters): array
    {
        $get = static function ($v) {
            $v = is_array($v) ? null : trim((string) $v);
            return ($v === '' ) ? null : $v;
        };
        return [
            'year' => $get($filters['year'] ?? null),
            'date_start' => $get($filters['date_start'] ?? null),
            'date_end' => $get($filters['date_end'] ?? null),
            'device_type_id' => $get($filters['device_type_id'] ?? null),
            'urgency' => $get($filters['urgency'] ?? null),
            'technician' => $get($filters['technician'] ?? null),
            'department' => $get($filters['department'] ?? null),
        ];
    }

    /**
     * แปลงตัวกรองเป็นช่วงวันที่ (ค.ศ. Y-m-d)
     * ลำดับความสำคัญ: date_start/date_end (พ.ศ. d/m/Y) > ปีงบ > ปีงบปัจจุบัน
     */
    private static function resolveDateRange(array $filters): array
    {
        $start = null;
        $end = null;

        if (!empty($filters['date_start'])) {
            $start = AppHelper::convertToGregorian($filters['date_start']) ?: null;
        }
        if (!empty($filters['date_end'])) {
            $end = AppHelper::convertToGregorian($filters['date_end']) ?: null;
        }
        if ($start && $end) {
            return ['start' => $start, 'end' => $end];
        }

        $year = $filters['year'] !== null ? (int) $filters['year'] : (int) AppHelper::YearBudget();
        $range = AppHelper::BudgetYearRange($year);
        return [
            'start' => $start ?: $range['start'],
            'end' => $end ?: $range['end'],
        ];
    }

    /**
     * มัธยฐาน (median) เป็นวินาที — null ถ้าไม่มีข้อมูล
     *
     * @param int[] $values
     */
    private static function median(array $values): ?int
    {
        $n = count($values);
        if ($n === 0) {
            return null;
        }
        sort($values);
        $mid = intdiv($n, 2);
        if ($n % 2 === 1) {
            return (int) $values[$mid];
        }
        return (int) round(($values[$mid - 1] + $values[$mid]) / 2);
    }

    private static function decodeJson($json): array
    {
        if (is_array($json)) {
            return $json;
        }
        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
