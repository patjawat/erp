<?php

namespace app\modules\development\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\ThaiDateHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Employees;
use app\modules\approveV2\models\Approve;

/**
 * Model สำหรับตาราง "development" (ใช้ table เดิม).
 *
 * @property int $id
 * @property int|null $document_id
 * @property string $topic
 * @property string $status
 * @property int $thai_year
 * @property string $date_start
 * @property string|null $time_start
 * @property string $date_end
 * @property string|null $time_end
 * @property string|null $development_type_id
 * @property string|null $vehicle_type_id
 * @property string $vehicle_date_start
 * @property string $vehicle_date_end
 * @property string|null $driver_id
 * @property string $leader_id
 * @property string|null $leader_group_id
 * @property int $assigned_to
 * @property string $emp_id
 * @property string|null $data_json
 * @property string|null $response_status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 */
class Development extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'development';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['document_id', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['document_id', 'assigned_to', 'created_by', 'updated_by', 'deleted_by', 'thai_year'], 'integer'],
            [['topic', 'status', 'date_start', 'date_end', 'vehicle_date_end', 'leader_id', 'assigned_to', 'emp_id', 'thai_year', 'leader_group_id'], 'required'],
            [['development_type_id', 'date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'response_status'], 'safe'],
            [['topic', 'status', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'leader_id', 'leader_group_id', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thai_year' => 'ปีงบประมาณ',
            'document_id' => 'ตามหนังสือ',
            'response_status' => 'ตอบรับเป็นวิทยากร',
            'topic' => 'หัวข้อ',
            'status' => 'สถานะ',
            'date_start' => 'วันที่เริ่ม',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'development_type_id' => 'ประเภทการพัฒนา',
            'vehicle_type_id' => 'ยานพาหนะ',
            'vehicle_date_start' => 'วันออกเดินทาง',
            'vehicle_date_end' => 'วันกลับ',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้าฝ่าย',
            'leader_group_id' => 'หัวหน้ากลุ่มงาน',
            'assigned_to' => 'มอบหมายงานให้',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'JSON',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /** สถานะ (ใช้ชุดเดียวกับการลา — leave_status) */
    public function getStatusCategorise()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])
            ->andOnCondition(['name' => 'leave_status']);
    }

    /**
     * แสดงสถานะแบบเดียวกับการลา (badge + ชื่อจาก Categorise)
     * @return string|null HTML badge
     */
    public function getStatusHtml()
    {
        try {
            $status = $this->status ?? '';
            $label = $this->statusCategorise ? $this->statusCategorise->title : $status;
            switch ($status) {
                case 'Pending':
                    $color = 'warning';
                    $icon = '<i class="bi bi-hourglass-split"></i>';
                    break;
                case 'Checking':
                case 'Checking1_pass':
                case 'Checking2_pass':
                    $color = 'info';
                    $icon = '<i class="fa-solid fa-circle-check"></i>';
                    break;
                case 'Approve':
                    $color = 'success';
                    $icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                    break;
                case 'Reject':
                    $color = 'danger';
                    $icon = '<i class="bi bi-exclamation-circle-fill text-danger"></i>';
                    break;
                case 'ReqCancel':
                case 'Cancel':
                    $color = 'secondary';
                    $icon = '<i class="bi bi-exclamation-circle-fill text-secondary"></i>';
                    break;
                default:
                    $color = 'warning';
                    $icon = '<i class="bi bi-hourglass-split"></i>';
            }
            return '<span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1">' . $icon . ' ' . \yii\helpers\Html::encode($label) . '</span>';
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getDevelopmentType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'development_type_id'])
            ->andOnCondition(['name' => 'development_type']);
    }

    public function getDevelopmentDetails()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id']);
    }

    public function getExpenses()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id'])
            ->andOnCondition(['name' => 'expense_type']);
    }

    /** ผู้ขอ (emp_id) */
    public function getEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    /** ผู้สร้าง */
    public function getCreatedByEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getVehicleType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vehicle_type_id']);
    }

    /** ผู้รับมอบหมายงาน (assigned_to) */
    public function getAssignedTo()
    {
        return $this->hasOne(Employees::class, ['id' => 'assigned_to']);
    }

    /** คณะเดินทาง (สมาชิกที่ไม่ใช่ผู้ขอ) สำหรับพิมพ์ใบขอไปราชการ */
    public function listMemberPrint()
    {
        return DevelopmentDetail::find()
            ->where(['development_id' => $this->id, 'name' => 'member'])
            ->andWhere(['<>', 'emp_id', $this->emp_id])
            ->all();
    }

    /** ภาพทีมผู้ร่วมเดินทาง (avatar stack) */
    public function StackMember()
    {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->all() as $key => $item) {
            $emp = Employees::findOne(['id' => $item->emp_id]);
            if ($emp) {
                $data .= Html::a(Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']), ['/me/development-detail/update', 'id' => $item->id, 'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'open-modal', 'data' => [
                    'size' => 'model-md',
                    'bs-toggle' => 'tooltip',
                    'bs-placement' => 'top',
                    'bs-title' => $emp->fullname
                ]]);
            }
        }
        $data .= '</div>';
        return $data;
    }

    /** ภาพทีมผู้อนุมัติ (avatar stack) */
    public function stackChecker()
    {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (Approve::find()->where(['from_id' => (string) $this->id, 'name' => 'development'])->andWhere(['not in', 'status', ['None', 'Pending']])->orderBy(['level' => SORT_DESC])->all() as $key => $item) {
            try {
                $data .= Html::img('@web/img/loading.gif', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload' . ($item->status == 'Reject' ? ' border-danger' : null),
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item->employee ? $item->employee->ShowAvatar() : ''
                    ]
                ]);
            } catch (\Throwable $th) {
                // ignore
            }
        }
        $data .= '</div>';
        return $data;
    }

    /** รายการผู้อนุมัติระดับ 4 (สำหรับแสดงใน view) */
    public function getApproverRecord()
    {
        return \app\modules\approveV2\models\Approve::find()
            ->andWhere(['from_id' => (string) $this->id, 'name' => 'development', 'level' => 4])
            ->joinWith(['employee'])
            ->one();
    }

    /** วันที่อนุมัติ (ระดับ 4) สำหรับแสดงในใบขอไปราชการ */
    public function approveDate()
    {
        $approve = $this->getApproverRecord();
        if ($approve && isset($approve->data_json['approve_date'])) {
            $dateStr = $approve->data_json['approve_date'];
            if (!empty($dateStr)) {
                return $dateStr;
            }
        }
        return null;
    }

    /** ช่วงวันที่กิจกรรม (สำหรับแสดงใน view) */
    public function showDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end, 'short');
    }

    /** ช่วงวันที่เดินทาง (สำหรับแสดงใน view) */
    public function showVehicleDateRange()
    {
        return $this->vehicle_date_start && $this->vehicle_date_end
            ? ThaiDateHelper::formatThaiDateRange($this->vehicle_date_start, $this->vehicle_date_end, 'short')
            : 'ไม่ระบุ';
    }

    /**
     * สรุปสำหรับ dashboard ตามปีงบประมาณ
     * @param int $thaiYear ปีงบประมาณ (พ.ศ.)
     * @return array ['total_count' => int, 'total_price' => float, 'emp_count' => int]
     */
    public static function getDashboardSummary($thaiYear)
    {
        $totalCount = (int) self::find()->where(['thai_year' => $thaiYear])->count();
        $empCount = (int) self::find()->where(['thai_year' => $thaiYear])->select('emp_id')->distinct()->count();
        $totalPrice = (float) DevelopmentDetail::find()
            ->innerJoin('development d', 'd.id = development_detail.development_id')
            ->andWhere(['d.thai_year' => $thaiYear])
            ->andWhere(['development_detail.name' => 'expense_type'])
            ->andWhere(['not', ['development_detail.price' => null]])
            ->sum('development_detail.price');
        return [
            'total_count' => $totalCount,
            'total_price' => $totalPrice,
            'emp_count' => $empCount,
        ];
    }

    /**
     * สรุปเปรียบเทียบปีปัจจุบันกับปีที่แล้ว (จำนวนกิจกรรม, งบประมาณ, บุคลากร, % เปลี่ยนแปลง)
     * @param int $thaiYear ปีงบประมาณ (พ.ศ.)
     * @return array
     */
    public static function getYearlyDevelopmentSummary($thaiYear)
    {
        $totalEmployees = (int) Employees::find()->where(['status' => 1])->count();
        if ($totalEmployees <= 0) {
            $totalEmployees = 1;
        }
        $prevYear = $thaiYear - 1;
        $curr = self::getDashboardSummary($thaiYear);
        $prev = self::getDashboardSummary($prevYear);

        $countChange = 0;
        $countStatus = 'เท่าเดิม';
        if ($prev['total_count'] > 0) {
            $countChange = round((($curr['total_count'] - $prev['total_count']) / $prev['total_count']) * 100, 2);
            $countStatus = $curr['total_count'] > $prev['total_count'] ? 'เพิ่มขึ้น' : ($curr['total_count'] < $prev['total_count'] ? 'ลดลง' : 'เท่าเดิม');
        }
        $pricePercentOfLastYear = 0;
        if ($prev['total_price'] > 0) {
            $pricePercentOfLastYear = round(($curr['total_price'] / $prev['total_price']) * 100, 2);
        }
        $empPercent = round(($curr['emp_count'] / $totalEmployees) * 100, 2);

        return [
            'total_count' => $curr['total_count'],
            'total_price' => $curr['total_price'],
            'emp_count' => $curr['emp_count'],
            'emp_percent' => $empPercent,
            'price_percent_change' => $pricePercentOfLastYear,
            'count_percent_change' => $countChange,
            'count_status' => $countStatus,
            'price_status' => $countStatus === 'เพิ่มขึ้น'
                ? '<span class="text-success"><i class="bi bi-caret-up-fill"></i> เพิ่มขึ้น</span>'
                : ($countStatus === 'ลดลง' ? '<span class="text-danger"><i class="bi bi-caret-down-fill"></i> ลดลง</span>' : '<span class="text-muted">เท่าเดิม</span>'),
        ];
    }

    /** จำนวนการอบรม/ประชุม/ดูงานเทียบรายปี (สำหรับ bar chart เทียบปี) */
    public static function getYearlyCountComparison($numYears = 6)
    {
        $currentYear = (int) \app\components\AppHelper::YearBudget();
        $years = [];
        for ($i = $numYears - 1; $i >= 0; $i--) {
            $years[] = $currentYear - $i;
        }
        $rows = self::find()
            ->select(['thai_year', 'COUNT(*) AS cnt'])
            ->where(['thai_year' => $years])
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_ASC])
            ->asArray()
            ->all();
        $countByYear = array_column($rows, 'cnt', 'thai_year');
        $categories = [];
        $series = [];
        foreach ($years as $y) {
            $categories[] = (string) $y;
            $series[] = (int) ($countByYear[$y] ?? 0);
        }
        return ['categories' => $categories, 'series' => $series];
    }

    /** สรุปสถานะ (จำนวนรายการแยกตามสถานะ) สำหรับ donut chart หรือการ์ดสรุป */
    public static function getStatusSummary($thaiYear)
    {
        $sql = "SELECT d.status, COALESCE(c.title, d.status) AS title, COUNT(*) AS total
                FROM development d
                LEFT JOIN categorise c ON c.code = d.status AND c.name = 'leave_status'
                WHERE d.thai_year = :thai_year
                GROUP BY d.status, c.title
                ORDER BY total DESC";
        $rows = Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $thaiYear)->queryAll();
        $labels = array_column($rows, 'title');
        $series = array_map('intval', array_column($rows, 'total'));
        return ['labels' => $labels, 'series' => $series];
    }

    /** สัดส่วนประเภทการอบรม/ประชุม/ดูงาน (สำหรับ donut chart) */
    public static function getActivityTypeSummary($thaiYear)
    {
        $sql = "SELECT c.code, c.title, COUNT(d.id) AS total
                FROM categorise c
                LEFT JOIN development d ON d.development_type_id = c.code AND d.thai_year = :thai_year
                WHERE c.name = 'development_type' AND (c.active = 1 OR c.active IS NULL)
                GROUP BY c.code, c.title
                ORDER BY c.title ASC";
        $rows = Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $thaiYear)->queryAll();
        $labels = array_column($rows, 'title');
        $series = array_map('intval', array_column($rows, 'total'));
        return ['labels' => $labels, 'series' => $series];
    }

    /** แนวโน้มรายเดือน (ปีงบประมาณ ต.ค.–ก.ย.) สำหรับ line/bar chart */
    public static function getMonthlyTrendSummary($thaiYear)
    {
        $list = self::listSummaryMonth($thaiYear);
        $series = [];
        foreach ($list as $item) {
            $series[] = [
                'name' => $item['title'],
                'data' => [
                    (int) $item['m10'], (int) $item['m11'], (int) $item['m12'],
                    (int) $item['m1'], (int) $item['m2'], (int) $item['m3'],
                    (int) $item['m4'], (int) $item['m5'], (int) $item['m6'],
                    (int) $item['m7'], (int) $item['m8'], (int) $item['m9'],
                ],
            ];
        }
        return [
            'series' => $series,
            'categories' => ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
        ];
    }

    /** การใช้งบประมาณตามประเภทกิจกรรม (bar) */
    public static function getBudgetByTypeSummary($thaiYear)
    {
        $sql = "SELECT c.title, COALESCE(SUM(dd.price), 0) AS total
                FROM categorise c
                LEFT JOIN development d ON d.development_type_id = c.code AND d.thai_year = :thai_year
                LEFT JOIN development_detail dd ON dd.development_id = d.id AND dd.name = 'expense_type' AND dd.price IS NOT NULL
                WHERE c.name = 'development_type' AND (c.active = 1 OR c.active IS NULL)
                GROUP BY c.code, c.title
                ORDER BY c.title ASC";
        $rows = Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $thaiYear)->queryAll();
        return [
            'labels' => array_column($rows, 'title'),
            'series' => array_map('floatval', array_column($rows, 'total')),
        ];
    }

    /** การเข้าร่วมกิจกรรมตามหน่วยงาน (horizontal bar) */
    public static function getParticipationByDepartmentSummary($thaiYear)
    {
        $sql = "SELECT COALESCE(t.name, 'ไม่ระบุหน่วยงาน') AS dept_name, COUNT(DISTINCT d.emp_id) AS cnt
                FROM development d
                LEFT JOIN employees e ON e.id = d.emp_id
                LEFT JOIN tree t ON t.id = e.department
                WHERE d.thai_year = :thai_year
                GROUP BY e.department, t.name
                ORDER BY cnt DESC
                LIMIT 15";
        $rows = Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $thaiYear)->queryAll();
        return [
            'labels' => array_column($rows, 'dept_name'),
            'series' => array_map('intval', array_column($rows, 'cnt')),
        ];
    }

    /** สรุปข้อมูลการอบรมประจำปีงบประมาณ แถว = ประเภท, คอลัมน์ = เดือน (ต.ค.–ก.ย.) */
    public static function listSummaryMonth($thaiYear)
    {
        $sql = "SELECT c.code, c.title,
                COUNT(CASE WHEN MONTH(d.date_start) = 10 THEN 1 END) AS m10,
                COUNT(CASE WHEN MONTH(d.date_start) = 11 THEN 1 END) AS m11,
                COUNT(CASE WHEN MONTH(d.date_start) = 12 THEN 1 END) AS m12,
                COUNT(CASE WHEN MONTH(d.date_start) = 1 THEN 1 END) AS m1,
                COUNT(CASE WHEN MONTH(d.date_start) = 2 THEN 1 END) AS m2,
                COUNT(CASE WHEN MONTH(d.date_start) = 3 THEN 1 END) AS m3,
                COUNT(CASE WHEN MONTH(d.date_start) = 4 THEN 1 END) AS m4,
                COUNT(CASE WHEN MONTH(d.date_start) = 5 THEN 1 END) AS m5,
                COUNT(CASE WHEN MONTH(d.date_start) = 6 THEN 1 END) AS m6,
                COUNT(CASE WHEN MONTH(d.date_start) = 7 THEN 1 END) AS m7,
                COUNT(CASE WHEN MONTH(d.date_start) = 8 THEN 1 END) AS m8,
                COUNT(CASE WHEN MONTH(d.date_start) = 9 THEN 1 END) AS m9
                FROM categorise c
                LEFT JOIN development d ON d.development_type_id = c.code AND d.thai_year = :thai_year
                WHERE c.name = 'development_type' AND (c.active = 1 OR c.active IS NULL)
                GROUP BY c.code, c.title
                ORDER BY c.title ASC";
        return Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $thaiYear)->queryAll();
    }
}
