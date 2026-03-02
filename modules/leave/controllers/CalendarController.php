<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use app\components\UserHelper;

/**
 * ปฏิทินการลา — แสดงการลาของหน่วยงาน กรองตาม department
 * ใช้ raw DB query เพื่อไม่ต้องพึ่ง HR models
 */
class CalendarController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('กรุณาเข้าสู่ระบบก่อน');
        }
        return true;
    }

    /**
     * หน้าปฏิทินการลา
     */
    public function actionIndex()
    {
        $me         = UserHelper::GetEmployee();
        $myDeptId   = $me ? (int) $me->department : 0;
        $leaveTypes = $this->getLeaveTypes();

        return $this->render('index', [
            'myDeptId'   => $myDeptId,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * คืน JSON events สำหรับ FullCalendar
     * GET params: start, end, dept (comma-separated ids หรือ 'all')
     */
    public function actionEvents()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $start  = Yii::$app->request->get('start', date('Y-m-01'));
        $end    = Yii::$app->request->get('end',   date('Y-m-t'));
        $dept   = Yii::$app->request->get('dept',  '');

        // validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $start)) {
            $start = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $end)) {
            $end = date('Y-m-t');
        }
        $start = substr($start, 0, 10);
        $end   = substr($end,   0, 10);

        // ดึง dept_ids ที่ต้องการ filter
        $deptIds = [];
        if ($dept !== '' && $dept !== 'all') {
            foreach (explode(',', $dept) as $d) {
                $d = (int) trim($d);
                if ($d > 0) $deptIds[] = $d;
            }
        }

        $db = Yii::$app->db;

        // ดึง leaves พร้อมข้อมูล employee + department (raw SQL)
        $sqlBase = "
            SELECT
                l.id,
                l.emp_id,
                l.leave_type_id,
                l.date_start,
                l.date_end,
                l.total_days,
                l.status,
                CONCAT(COALESCE(e.fname,''), ' ', COALESCE(e.lname,'')) AS fullname,
                e.department AS dept_id,
                o.name AS dept_name,
                c.title AS leave_type_title,
                c.data_json AS lt_data_json
            FROM `leave` l
            INNER JOIN `employees` e ON e.id = l.emp_id
            LEFT  JOIN `tree` o ON o.id = e.department
            LEFT  JOIN `categorise` c ON c.code = l.leave_type_id AND c.name = 'leave_type'
            WHERE l.date_start <= :date_end
              AND l.date_end   >= :date_start
              AND l.status NOT IN ('Reject', 'Cancel')
        ";

        $params = [':date_start' => $start, ':date_end' => $end];

        if (!empty($deptIds)) {
            // รวม sub-department (ลูกหลานทั้งหมดในต้นไม้) โดย query nested set
            $allDeptIds = $this->getAllDescendantIds($deptIds);
            $placeholders = implode(',', array_fill(0, count($allDeptIds), '?'));
            $sqlBase .= " AND e.department IN ($placeholders)";
            $rows = $db->createCommand($sqlBase . ' ORDER BY l.date_start, fullname', $params)
                ->bindValues(array_merge($params, array_values($allDeptIds)))
                ->queryAll();
            // bindValues กับ mixed params ต้องทำแยก
            $cmd = $db->createCommand($sqlBase . ' ORDER BY l.date_start, fullname');
            $cmd->bindValue(':date_start', $start);
            $cmd->bindValue(':date_end',   $end);
            foreach ($allDeptIds as $i => $id) {
                $cmd->bindValue($i + 1, $id, \PDO::PARAM_INT);
            }
            $rows = $cmd->queryAll();
        } else {
            $rows = $db->createCommand($sqlBase . ' ORDER BY l.date_start, fullname', $params)->queryAll();
        }

        // แปลงเป็น FullCalendar events
        $events = [];
        foreach ($rows as $row) {
            $color     = $this->getStatusColor($row['status']);
            $ltIcon    = '';
            try {
                $ltJson = is_string($row['lt_data_json']) ? json_decode($row['lt_data_json'], true) : $row['lt_data_json'];
                $ltIcon = $ltJson['icon'] ?? '';
            } catch (\Throwable $e) {}

            $events[] = [
                'id'              => $row['id'],
                'title'           => $row['fullname'],
                'start'           => $row['date_start'],
                'end'             => date('Y-m-d', strtotime($row['date_end'] . ' +1 day')), // FullCalendar end is exclusive
                'allDay'          => true,
                'backgroundColor' => $color['bg'],
                'borderColor'     => $color['border'],
                'textColor'       => '#fff',
                'extendedProps'   => [
                    'emp_id'           => $row['emp_id'],
                    'dept_name'        => $row['dept_name'] ?? '—',
                    'dept_id'          => $row['dept_id'],
                    'leave_type'       => $row['leave_type_title'] ?? $row['leave_type_id'],
                    'total_days'       => $row['total_days'],
                    'status'           => $row['status'],
                    'status_label'     => $this->getStatusLabel($row['status']),
                    'status_color'     => $color['label'],
                    'icon'             => $ltIcon,
                ],
            ];
        }

        return $events;
    }

    // ─────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────

    /**
     * ดึงโครงสร้าง department tree จาก organization table โดยตรง
     */
    private function getDepartmentTree(): array
    {
        try {
            return Yii::$app->db->createCommand("
                SELECT id, name, lvl AS depth, lft, rgt, icon
                FROM `tree`
                WHERE active = 1
                ORDER BY root, lft
            ")->queryAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * คืน IDs ของ department ที่ให้มาและลูกหลานทั้งหมด (nested set)
     */
    private function getAllDescendantIds(array $deptIds): array
    {
        if (empty($deptIds)) return [];
        try {
            $placeholders = implode(',', array_fill(0, count($deptIds), '?'));
            // หา lft/rgt ของ nodes ที่เลือก
            $nodes = Yii::$app->db->createCommand("
                SELECT lft, rgt FROM `tree`
                WHERE id IN ($placeholders)
            ", $deptIds)->queryAll();

            if (empty($nodes)) return $deptIds;

            // รวม conditions สำหรับทุก node ที่เลือก
            $conditions = [];
            $params = [];
            foreach ($nodes as $i => $node) {
                $conditions[] = "(lft >= :lft{$i} AND rgt <= :rgt{$i})";
                $params[":lft{$i}"] = $node['lft'];
                $params[":rgt{$i}"] = $node['rgt'];
            }
            $sql = "SELECT id FROM `tree` WHERE (" . implode(' OR ', $conditions) . ")";
            return Yii::$app->db->createCommand($sql, $params)->queryColumn();
        } catch (\Throwable $e) {
            return $deptIds;
        }
    }

    private function getLeaveTypes(): array
    {
        try {
            return Yii::$app->db->createCommand("
                SELECT code, title, data_json
                FROM categorise WHERE name = 'leave_type' AND active = 1 ORDER BY code
            ")->queryAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getStatusColor(string $status): array
    {
        return match ($status) {
            'Approve'                          => ['bg' => '#198754', 'border' => '#157347', 'label' => 'success'],
            'Checking', 'Checking1_pass',
            'Checking2_pass'                   => ['bg' => '#fd7e14', 'border' => '#dc6c02', 'label' => 'warning'],
            'Pending'                          => ['bg' => '#6c757d', 'border' => '#5a6268', 'label' => 'secondary'],
            default                            => ['bg' => '#0d6efd', 'border' => '#0b5ed7', 'label' => 'primary'],
        };
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'Approve'          => 'อนุมัติแล้ว',
            'Checking'         => 'รอตรวจสอบ',
            'Checking1_pass'   => 'ผ่านชั้นที่ 1',
            'Checking2_pass'   => 'ผ่านชั้นที่ 2',
            'Pending'          => 'รอดำเนินการ',
            'ReqCancel'        => 'ขอยกเลิก',
            default            => $status,
        };
    }
}
