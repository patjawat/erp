<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\db\Expression;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use app\modules\development\components\DevelopmentTelegramService;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\Documents;
use app\modules\approveV2\models\Approve;
use app\modules\usermanager\models\User;
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\hr\models\DevelopmentSummary;

/**
 * This is the model class for table "development".
 *
 * @property int $id
 * @property int|null $document_id ตามหนังสือ
 * @property string $topic หัวข้อ
 * @property string $status สถานะ
 * @property string $date_start วันที่เริ่ม
 * @property string|null $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $vehicle_type_id ยานพาหนะ
 * @property string $vehicle_date_start วันออกเดินทาง
 * @property string $vehicle_date_end วันกลับ
 * @property string|null $driver_id พนักงานขับ
 * @property string $leader_id หัวหน้าฝ่าย
 * @property int $assigned_to มอบหมายงานให้
 * @property string $emp_id ผู้ขอ
 * @property array|string|null $data_json JSON
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Development extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;

    public $q_department;
    public $date_filter;
    public $q_status;

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
            [['development_type_id', 'date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'q_department', 'response_status', 'date_filter','q_status'], 'safe'],
            [['topic', 'status', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
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

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->normalizeDataJson();
    }

    public function beforeValidate()
    {
        $this->normalizeDataJson();
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->normalizeDataJson();
        if (is_array($this->data_json) && !$this->isNativeJsonColumn()) {
            $this->data_json = Json::encode($this->data_json);
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->normalizeDataJson();
    }

    private function normalizeDataJson(): void
    {
        if (is_array($this->data_json)) {
            return;
        }

        if (!is_string($this->data_json) || trim($this->data_json) === '') {
            $this->data_json = [];
            return;
        }

        try {
            $decoded = Json::decode($this->data_json, true);
            $this->data_json = is_array($decoded) ? $decoded : [];
        } catch (\InvalidArgumentException $e) {
            $this->data_json = [];
        }
    }

    private function isNativeJsonColumn(): bool
    {
        $column = static::getTableSchema()->getColumn('data_json');

        return $column !== null
            && ($column->type === 'json' || stripos((string) $column->dbType, 'json') !== false);
    }

    public function getDevelopmentType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'development_type_id'])->andOnCondition(['name' => 'development_type']);
    }

    public function getDevelopmentDetail()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id']);
    }

    public function getDocument()
    {
        return $this->hasOne(Documents::class, ['id' => 'document_id']);
    }

    public function getExpenses()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id'])->andOnCondition(['name' => 'expense_type']);
    }

    /** สรุปผลการประชุม/อบรม ของใบนี้ (มีได้ใบละ 1 แถว) */
    public function getSummaryReport()
    {
        return $this->hasOne(DevelopmentSummary::class, ['development_id' => 'id']);
    }

    /** ใบจองรถที่สร้างจากใบไปราชการใบนี้ */
    public function getVehicleBookings()
    {
        return $this->hasMany(Vehicle::class, ['development_id' => 'id']);
    }

    /**
     * ยอดประมาณค่าใช้จ่ายแยกตามหมวด (float ดิบ)
     *
     * แหล่งข้อมูลหลักคือช่อง «ประมาณค่าใช้จ่ายฯ» ในฟอร์ม ที่บันทึกลง data_json เป็นคีย์ estimated_cost_*
     *
     * @param bool $withLegacyDetails true = บวกรายการ expense_type แบบเก่าเข้าไปด้วย (ใช้ตอนออก PDF)
     *                                false = คิดจาก data_json อย่างเดียว ไม่ยิง query (ใช้ในหน้าทะเบียน)
     */
    public function estimatedCostAmounts(bool $withLegacyDetails = false): array
    {
        $dataJson = is_array($this->data_json) ? $this->data_json : [];
        $amountOf = static function ($value): float {
            if ($value === null || $value === '') {
                return 0.0;
            }
            return (float) str_replace(',', '', (string) $value);
        };

        $amounts = [
            'registration_amount' => $amountOf($dataJson['estimated_cost_registration'] ?? $dataJson['registration_amount'] ?? null),
            'accommodation_amount' => $amountOf($dataJson['estimated_cost_accommodation'] ?? null),
            'vehicle_amount' => $amountOf($dataJson['estimated_cost_vehicle_fuel'] ?? null),
            'allowance_amount' => $amountOf($dataJson['estimated_cost_allowance'] ?? null),
            'other_amount' => $amountOf($dataJson['estimated_cost_other'] ?? null),
        ];

        if (!$withLegacyDetails) {
            return $amounts;
        }

        foreach ($this->getExpenses()->with('expenseType')->all() as $detail) {
            $amount = (float) ($detail->qty ?? 0) * (float) ($detail->price ?? 0);
            $title = $detail->expenseType ? (string) $detail->expenseType->title : '';
            if (stripos($title, 'ลงทะเบียน') !== false) {
                $amounts['registration_amount'] += $amount;
            } elseif (stripos($title, 'ที่พัก') !== false) {
                $amounts['accommodation_amount'] += $amount;
            } elseif (stripos($title, 'ยานพาหนะ') !== false || stripos($title, 'พาหนะ') !== false || stripos($title, 'น้ำมัน') !== false) {
                $amounts['vehicle_amount'] += $amount;
            } elseif (stripos($title, 'เบี้ยเลี้ยง') !== false) {
                $amounts['allowance_amount'] += $amount;
            } else {
                $amounts['other_amount'] += $amount;
            }
        }

        return $amounts;
    }

    /** ยอดรวมงบประมาณของใบนี้ */
    public function totalEstimatedCost(bool $withLegacyDetails = false): float
    {
        return (float) array_sum($this->estimatedCostAmounts($withLegacyDetails));
    }

    /**
     * สถานะสรุปผลสำหรับแสดงในทะเบียน — คืน label/สี/ไอคอน พร้อมใช้
     *
     * แดง = ยังไม่บันทึกหรือยังเป็นฉบับร่าง, เหลือง = ส่งแล้วรอรับทราบ, เขียว = รับทราบครบแล้ว
     */
    public function summaryState(): array
    {
        $status = $this->summaryReport?->status ?? DevelopmentSummary::STATUS_DRAFT;

        return match ($status) {
            DevelopmentSummary::STATUS_ACKNOWLEDGED => [
                'status' => $status,
                'label' => 'รับทราบแล้ว',
                'color' => 'success',
                'icon' => 'bi-check-circle-fill',
            ],
            DevelopmentSummary::STATUS_SUBMITTED => [
                'status' => $status,
                'label' => 'รอผู้รับทราบ',
                'color' => 'warning',
                'icon' => 'bi-clock-fill',
            ],
            default => [
                'status' => DevelopmentSummary::STATUS_DRAFT,
                'label' => $this->summaryReport ? 'ฉบับร่าง ยังไม่ส่ง' : 'ยังไม่สรุปผล',
                'color' => 'danger',
                'icon' => 'bi-x-circle-fill',
            ],
        };
    }

    /**
     * ผู้ใช้ปัจจุบันแก้สรุปผลของใบนี้ได้หรือไม่ — เจ้าของใบและคณะเดินทางทุกคนแก้ได้
     */
    public function canEditSummary($empId = null): bool
    {
        $empId = $empId ?? (UserHelper::GetEmployee()->id ?? null);
        if (!$empId) {
            return false;
        }
        if ((string) $this->emp_id === (string) $empId) {
            return true;
        }
        return DevelopmentDetail::find()
            ->where(['development_id' => $this->id, 'name' => 'member', 'emp_id' => (string) $empId])
            ->exists();
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getCreatedByEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getVehicleType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vehicle_type_id']);
    }


    public function groupYear()
    {
        $year = self::find()
            ->andWhere(['IS NOT', 'thai_year', null])
            ->groupBy(['thai_year'])
            ->orderBy(['thai_year' => SORT_DESC])
            ->all();
        return ArrayHelper::map($year, 'thai_year', function ($model) {
            return 'ปีงบประมาณ ' . $model->thai_year;
        });
    }

    // สรุปข้อมูลการอบรม/ประชุม/ดูงาน
    public function getSummary()
    {
        return [
            'listSummaryMonth' => $this->listSummaryMonth(),
            'activityType' => $this->activityType(),
            'monthlyTrend' => $this->monthlyTrend(),
        ];
    }

    // แนวโน้มการอบรม/ประชุม/ดูงานรายเดือน
    public function monthlyTrend()
    {
        $list = $this->listSummaryMonth();
        $series = [];
        foreach ($list as $item) {
            $series[] = [
                'name' => $item['title'],
                'data' => [
                    (int)$item['m1'],
                    (int)$item['m2'],
                    (int)$item['m3'],
                    (int)$item['m4'],
                    (int)$item['m5'],
                    (int)$item['m6'],
                    (int)$item['m7'],
                    (int)$item['m8'],
                    (int)$item['m9'],
                    (int)$item['m10'],
                    (int)$item['m11'],
                    (int)$item['m12']
                ]
            ];
        }
        return [
            'series' => $series,
            'categories' => [
                'ม.ค.',
                'ก.พ.',
                'มี.ค.',
                'เม.ย.',
                'พ.ค.',
                'มิ.ย.',
                'ก.ค.',
                'ส.ค.',
                'ก.ย.',
                'ต.ค.',
                'พ.ย.',
                'ธ.ค.'
            ]
        ];
    }
    // จำนวนการอบรม/ประชุม/ดูงานทั้งหมด
    public function activityType()
    {
        $sql = "SELECT 
            c.code,
            c.title,
            count(d.id) as total
            FROM categorise c
            LEFT JOIN development d 
                ON d.development_type_id = c.code AND d.thai_year = :thai_year
            WHERE c.name = 'development_type'
            GROUP BY c.code, c.title;";
        $data = Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $this->thai_year)->queryAll();

        $series = [];
        foreach ($data as $item) {
            $series[] = (int)$item['total'];
        }

        return ['series' => $series, 'labels' => array_column($data, 'title')];
    }

    public function listSummaryMonth()
    {

        $sql = "
                SELECT 
                    c.code,
                    c.title,
                    COUNT(CASE WHEN MONTH(d.date_start) = 1 THEN 1 END) AS m1,
                    COUNT(CASE WHEN MONTH(d.date_start) = 2 THEN 1 END) AS m2,
                    COUNT(CASE WHEN MONTH(d.date_start) = 3 THEN 1 END) AS m3,
                    COUNT(CASE WHEN MONTH(d.date_start) = 4 THEN 1 END) AS m4,
                    COUNT(CASE WHEN MONTH(d.date_start) = 5 THEN 1 END) AS m5,
                    COUNT(CASE WHEN MONTH(d.date_start) = 6 THEN 1 END) AS m6,
                    COUNT(CASE WHEN MONTH(d.date_start) = 7 THEN 1 END) AS m7,
                    COUNT(CASE WHEN MONTH(d.date_start) = 8 THEN 1 END) AS m8,
                    COUNT(CASE WHEN MONTH(d.date_start) = 9 THEN 1 END) AS m9,
                    COUNT(CASE WHEN MONTH(d.date_start) = 10 THEN 1 END) AS m10,
                    COUNT(CASE WHEN MONTH(d.date_start) = 11 THEN 1 END) AS m11,
                    COUNT(CASE WHEN MONTH(d.date_start) = 12 THEN 1 END) AS m12
                FROM categorise c
                LEFT JOIN development d 
                    ON d.development_type_id = c.code AND d.thai_year = :thaiYear
                WHERE c.name = 'development_type'
                GROUP BY c.code, c.title
                ORDER BY c.code
                ";

        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(':thaiYear', $this->thai_year);

        $data = $command->queryAll();
        return $data;
    }

    // เปรียบเทียบข้อมูลการพัฒนารายปี
    public  function getYearlyDevelopmentSummary()
    {

        // ดึงจำนวนบุคลากรทั้งหมด
        $totalEmployees = Employees::find()
            ->where(['status' => 1])
            ->count();

        $sql = "
        SELECT 
            thai_year,
            total_price,
            total_count,
            unique_emp_count,

            -- ความต่างจำนวนครั้ง
            IFNULL(total_count - LAG(total_count) OVER (ORDER BY thai_year), 0) AS count_difference,

            -- สถานะ (ดูจากจำนวนครั้ง)
            CASE
                WHEN LAG(total_count) OVER (ORDER BY thai_year) IS NULL THEN 'N/A'
                WHEN total_count > LAG(total_count) OVER (ORDER BY thai_year) THEN 'เพิ่มขึ้น'
                WHEN total_count < LAG(total_count) OVER (ORDER BY thai_year) THEN 'ลดลง'
                ELSE 'เท่าเดิม'
            END AS count_status,

            -- % เปลี่ยนจำนวนครั้ง
            CASE
                WHEN LAG(total_count) OVER (ORDER BY thai_year) IS NULL THEN 0
                WHEN LAG(total_count) OVER (ORDER BY thai_year) = 0 THEN 0
                ELSE ROUND(
                    ((total_count - LAG(total_count) OVER (ORDER BY thai_year)) / LAG(total_count) OVER (ORDER BY thai_year)) * 100,
                    2
                )
            END AS count_percent_change,

            -- % บุคลากรที่เข้าร่วมกิจกรรม
            ROUND((unique_emp_count / :totalEmployees) * 100, 2) AS emp_percent

        FROM (
            SELECT 
                d.thai_year, 
                SUM(t.price) AS total_price,
                COUNT(t.id) AS total_count,
                COUNT(DISTINCT d.emp_id) AS unique_emp_count
            FROM development d
            LEFT JOIN development_detail t ON t.development_id = d.id
            WHERE t.price IS NOT NULL
            AND d.thai_year IN (:last_year, :current_year)
            GROUP BY d.thai_year
        ) AS yearly;
            ";


        $lastYear = $this->thai_year - 1;
        $currentYear = $this->thai_year;
        $data =  Yii::$app->db->createCommand($sql)
            ->bindValue(':last_year', $lastYear)
            ->bindValue(':current_year', $currentYear)
            ->bindValue(':totalEmployees', $totalEmployees)
            ->queryAll();
        return [
            'total_count' => $data[1]['total_count'] ?? 0,
            'total_price' => $data[1]['total_price'] ?? 0,
            'emp_count' => $data[1]['unique_emp_count'] ?? 0,
            'emp_percent' => $data[1]['emp_percent'] ?? 0,
            'price_percent_change' => $data[1]['price_percent_change'] ?? 0,
            'count_percent_change' => $data[1]['count_percent_change'] ?? 0,
            'year' => $data[1]['thai_year'] ?? 0,
            'price_status' => (isset($data[1]['count_status'])
                ? ($data[1]['count_status'] == 'เพิ่มขึ้น'
                    ? '<span class="text-success"><i class="fa-solid fa-caret-up"></i> เพิ่มขึ้น</span>'
                    : '<span class="text-danger"><i class="fa-solid fa-caret-down"></i> ลดลง</span>')
                : '-'),


        ];
    }



    public function listApprove()
    {
        return Approve::find()->where(['name' => 'development', 'from_id' => $this->id])->orderBy(['level' => SORT_ASC])->all();
    }

    // แสดงวันที่สร้าง
    // public function viewCreated()
    // {
    //     // return Yii::$app->thaiFormatter->asDate($this->created_at, 'long');
    //     return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
    // }

    // ส่ง Msg เมื่อผ่านการอนุมัติ

    public function MsgApprove()
    {
        $message = $this->topic . 'ได้รับการอนุมัติแล้ว';
        $lineId = $this->createdByEmp->user->line_id;
        LineMsg::sendMsg($lineId, $message);
    }

    public function getLeader()
    /**
     * Gets query for [[Leader]].
     *
     * @return yii\db\ActiveQuery
     */
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getAssignedTo()
    {
        return $this->hasOne(Employees::class, ['id' => 'assigned_to']);
    }

 public function ApproveDate()
{
    // ค้นหาข้อมูลการอนุมัติ
    $approve = Approve::findOne(['from_id' => $this->id,'name' => 'development', 'level' => 4]);
    
    // ตรวจสอบว่าพบข้อมูล และมีค่า approve_date ใน json หรือไม่
    if ($approve && isset($approve->data_json['approve_date'])) {
        $dateStr = $approve->data_json['approve_date'];
        
        // ตรวจสอบว่าวันที่ไม่ใช่ค่าว่าง
        if (!empty($dateStr)) {
            // สร้าง DateTime object เพื่อความแม่นยำในการ format
            $date = new \DateTime($dateStr);
            return $date->format('Y-m-d');
        }
    }
    
    return ''; // คืนค่าว่างถ้าไม่พบข้อมูล
}

    public function VehicleTypeName()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_type'])
            ->andWhere(['code' => $this->data_json['vehicle_type']])
            ->one();
        return $model ? $model->title : '-';
    }

    /**
     * สร้างลำดับการอนุมัติให้ครบทุกระดับ
     *
     * @return Approve|null ขั้นแรกที่สร้างใหม่ เพื่อส่งการแจ้งเตือนหลัง transaction commit
     */
    public function createApprove()
    {
        $director = SiteHelper::viewDirector();
        $steps = [
            1 => ['emp_id' => $this->leader_id, 'title' => 'เห็นชอบ', 'label' => 'เห็นชอบ', 'status' => 'Pending'],
            2 => ['emp_id' => $this->leader_group_id, 'title' => 'เห็นชอบ', 'label' => 'เห็นชอบ', 'status' => 'None'],
            3 => ['emp_id' => null, 'title' => 'ตรวจสอบ', 'label' => 'ผ่าน', 'status' => 'None'],
            4 => ['emp_id' => $director['id'] ?? null, 'title' => 'อนุมัติ', 'label' => 'อนุมัติ', 'status' => 'None'],
        ];

        $newPendingApprove = null;
        foreach ($steps as $level => $step) {
            $existing = Approve::findOne([
                'from_id' => $this->id,
                'level' => $level,
                'name' => 'development',
            ]);
            if ($existing) {
                continue;
            }

            $approve = new Approve([
                'from_id' => $this->id,
                'name' => 'development',
                'emp_id' => $step['emp_id'],
                'title' => $step['title'],
                'data_json' => ['label' => $step['label']],
                'level' => $level,
                'status' => $step['status'],
            ]);
            if (!$approve->save(false)) {
                throw new \RuntimeException('ไม่สามารถสร้างลำดับการอนุมัติระดับ ' . $level);
            }

            if ($level === 1) {
                $newPendingApprove = $approve;
            }
        }

        if (!$newPendingApprove) {
            return null;
        }

        return $newPendingApprove;
    }

    public function notifyPendingApprove(Approve $pendingApprove): void
    {
        $toUserId = trim((string) ($pendingApprove->employee?->user?->line_id ?? ''));
        $msg = 'ขออนุมัติ';
        $msg .= "\n" . 'หัวข้อ : ' . $this->topic;
        $msg .= "\n" . 'วันที่ : ' . ThaiDateHelper::formatThaiDate($this->date_start, 'long', 'short');
        $msg .= "\n" . 'ถึงวันที่ : ' . ThaiDateHelper::formatThaiDate($this->date_end, 'long', 'short');
        $msg .= "\n" . 'ผู้ขอ : ' . ($this->createdByEmp?->fullname ?? '-');
        if ($toUserId !== '') {
            try {
                LineMsg::sendMsg($toUserId, $msg);
            } catch (\Throwable $th) {
                Yii::error('development approve line notify fail: ' . $th->getMessage(), __METHOD__);
            }
        }

        try {
            (new DevelopmentTelegramService())->notifyPendingApprove($this, $pendingApprove);
        } catch (\Throwable $th) {
            Yii::error('development approve telegram notify fail: ' . $th->getMessage(), __METHOD__);
        }
    }

    /**
     * แจ้งเตือนหัวหน้าของสมาชิกคณะเดินทางทุกคนผ่าน Telegram
     * - dedupe ตาม telegram_id เพื่อกัน push ซ้ำ
     * - ข้ามหัวหน้าที่ได้รับ "ขออนุมัติ" จาก createApprove() ไปแล้ว
     *
     * @param array    $memberEmpIds         รหัสพนักงานของสมาชิกในคณะเดินทาง (รวมผู้ขอ)
     * @param int|null $excludeLeaderEmpId   รหัสหัวหน้าที่จะข้าม เช่น $this->leader_id
     * @return array รายการ telegram_id ที่ส่งสำเร็จ
     */
    public function notifyMembersLeaders(array $memberEmpIds, $excludeLeaderEmpId = null)
    {
        $sentChatIds = [];

        $memberEmpIds = array_values(array_unique(array_filter(array_map('intval', $memberEmpIds))));
        if (empty($memberEmpIds)) {
            return $sentChatIds;
        }

        $leaderToMembers = [];
        foreach ($memberEmpIds as $empId) {
            try {
                $emp = Employees::findOne($empId);
                if (!$emp) {
                    continue;
                }
                $leaderInfo = $emp->leaderUser();
                $leaderEmpId = (int) ($leaderInfo['leader1'] ?? 0);
                if (!$leaderEmpId) {
                    continue;
                }
                if ($excludeLeaderEmpId && $leaderEmpId === (int) $excludeLeaderEmpId) {
                    continue;
                }
                $leaderToMembers[$leaderEmpId][] = $emp->fullname;
            } catch (\Throwable $th) {
                continue;
            }
        }

        if (empty($leaderToMembers)) {
            return $sentChatIds;
        }

        $opt = [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        $esc = function ($s) {
            return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };
        $requester = $this->createdByEmp;

        foreach ($leaderToMembers as $leaderEmpId => $memberNames) {
            try {
                $leaderEmp = Employees::findOne($leaderEmpId);
                $chatId = trim((string) ($leaderEmp?->user?->telegram_id ?? ''));
                if ($chatId === '' || in_array($chatId, $sentChatIds, true)) {
                    continue;
                }

                $names = implode(', ', array_values(array_unique($memberNames)));
                $msg  = '📣 <b>แจ้งให้ทราบ</b> ผู้ใต้บังคับบัญชามีกำหนดเดินทาง';
                $msg .= "\n" . '📝 <b>หัวข้อ:</b> ' . $esc($this->topic);
                $msg .= "\n" . '📅 <b>วันที่:</b> ' . $esc(ThaiDateHelper::formatThaiDate($this->date_start, 'long', 'short'));
                $msg .= "\n" . '➡️ <b>ถึงวันที่:</b> ' . $esc(ThaiDateHelper::formatThaiDate($this->date_end, 'long', 'short'));
                $msg .= "\n" . '👤 <b>ผู้ขอ:</b> ' . $esc($requester?->fullname ?? '-');
                $msg .= "\n" . '👥 <b>สมาชิกในสังกัด:</b> ' . $esc($names);

                Yii::$app->telegram->sendDirectMessage($chatId, $msg, $opt);
                $sentChatIds[] = $chatId;
            } catch (\Throwable $th) {
                Yii::error('notifyMembersLeaders telegram fail (emp ' . $leaderEmpId . '): ' . $th->getMessage(), __METHOD__);
                continue;
            }
        }

        return $sentChatIds;
    }

    // ผู้ขอบริการ

    //แสดงวันเวลาที่แสดง
    public function viewCreated()
    {
        try {
            $datetime = explode(' ', $this->created_at);
            $date = ThaiDateHelper::formatThaiDate($datetime[0]);
            $time =  substr($datetime[1], 0, 5) . '.น';
            return [
                'full' => $date . ' ' . $time,
                'date' => $date,
                'time' => $time
            ];
        } catch (\Throwable $th) {
            return [
                'full' => '',
                'date' => '',
                'time' => ''
            ];
        }
    }



    //  ภาพทีมคณะกรรมการ (ยกเว้นผู้ขอ)
    public function StackMember()
    {
        $data = '';
        $data .= '<div class="avatar-stack d-flex flex-wrap gap-2 align-items-center">';
        foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->andWhere(['<>', 'emp_id', $this->emp_id])->with('emp')->all() as $key => $item) {
            $emp = $item->emp;
            if ($emp) {
                $data .= Html::a(Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']), ['/me/development-detail/update', 'id' => $item->id, 'title' => '<i class="bi bi-person-circle"></i> คณะเดินทาง'], ['class' => 'open-modal', 'data' => [
                    'size' => 'modal-md',
                    'bs-toggle' => 'tooltip',
                    'bs-placement' => 'top',
                    'bs-title' => $emp->fullname()
                ]]);
            }
        }
        $data .= '</div>';
        return $data;
    }

    /** แสดงรายชื่อคณะเดินทาง (สำหรับใช้ใน view) — ยกเว้นผู้ขอ ใช้ชื่อจาก emp หรือ label/emp_id */
    public function memberText()
    {
        $data = [];
        foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->andWhere(['<>', 'emp_id', $this->emp_id])->with('emp')->orderBy(['id' => SORT_ASC])->all() as $item) {
            $emp = $item->emp;
            $name = $emp ? $emp->fullname() : (trim((string)($item->data_json['label'] ?? '')) ?: ((string)$item->emp_id ?: '-'));
            $data[] = $name;
        }
        return [
            'data' => $data,
            'count' => count($data),
            'text' => implode(', ', $data)
        ];
    }

    // วันที่เอกสาร
    public function showDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end, 'long', 'short');
    }

    // วันที่ออกเดินทาง
    public function showVehicleDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->vehicle_date_start, $this->vehicle_date_end, 'long', 'short');
    }

    public function ListVehicleType()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_type'])
            ->andWhere(['!=', 'code', 'ambulance'])
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function ListThaiYear()
    {
        $model = self::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    // public function listStatus()
    // {
    //     return [
    //         'Pending' => 'รอเห็นชอบ',
    //         'Checking' => 'รอตรวจสอบ',
    //         'Pass' => 'ตรวจสอบผ่าน',
    //         'Approve' => 'ผอ.อนุมัติ',
    //         'Reject' => 'ไม่อนุมัติ',
    //         'Cancel' => 'ยกเลิก',
    //     ];
    // }

    //ใช้สำหรับ filter status (ใช้ร่วมกันกับ leave)
    public function listStatus()
    {
        return ArrayHelper::map(
            Categorise::find()
                ->where(['name' => 'leave_status'])
                ->orderBy(new \yii\db\Expression('CAST(sort AS UNSIGNED) ASC'))
                ->all(),
            'code',
            'title'
        );
    }


    // รายชื่อคณะเดินทาง
    public function listMember()
    {
        return DevelopmentDetail::find()->where(['development_id' => $this->id, 'name' => 'member'])->all();
    }
    /** รายชื่อคณะเดินทาง (ยกเว้นผู้สร้างใบ) — ใช้ในที่อื่นถ้าต้องการไม่รวมผู้ขอ */
    public function listMemberPrint()
    {
        return DevelopmentDetail::find()
            ->where(['development_id' => $this->id, 'name' => 'member'])
            ->andWhere(['<>', 'emp_id', $this->emp_id])
            ->with('emp')
            ->all();
    }

    /** รายชื่อคณะเดินทาง (ยกเว้นผู้ขอ) — ใช้สำหรับพิมพ์ PDF และที่อื่น */
    public function listMemberForPdf()
    {
        return DevelopmentDetail::find()
            ->where(['development_id' => $this->id, 'name' => 'member'])
            ->andWhere(['<>', 'emp_id', $this->emp_id])
            ->with('emp')
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }


    //  ภาพทีมผูตรวจสอบ
    public function stackChecker()
    {
        // try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (Approve::find()->where(['from_id' => $this->id, 'name' => 'development'])->andWhere(['not in', 'status', ['None', 'Pending']])->orderBy(['level' => SORT_DESC])->all() as $key => $item) {
            try {
                $data .= Html::img('@web/img/loading.gif', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload' . ($item->status == 'Reject' ? ' border-danger' : null),
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item->employee->showAvatar()
                    ]
                ]);
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
        $data .= '</div>';
        return $data;
    }

 public function stackCheckerDev()
{
    $data = '<ul class="list-unstyled">';

    $approves = Approve::find()
        ->where(['from_id' => $this->id, 'name' => 'development'])
        ->andWhere(['not in', 'status', ['None', 'Pending']])
        ->orderBy(['level' => SORT_DESC])
        ->all();

    foreach ($approves as $key => $item) {
        try {
            $fullname = $item->employee->fullname ?? 'ไม่พบชื่อพนักงาน';
            $status = $item->status ?? '-';
            $level = $item->level ?? '-';
            $data .= "<li> {$level}: {$fullname} ({$status})</li>";
        } catch (\Throwable $th) {
            $data .= "<li class='text-danger'>เกิดข้อผิดพลาดในรายการที่ {$key}</li>";
        }
    }

    $data .= '</ul>';
    return $data;
}


    

    // การตอบรับเป็นวิทยากร
    public function viewResponseStatus()
    {
        switch ($this->response_status) {
            case 'Accept':
                return [
                    'title' => 'ตอบรับ',
                    'color' => 'success',
                    'view' => '<span class="badge bg-success text-white"><i class="fa-solid fa-circle-check"></i> ตอบรับ</span>'
                ];
                break;
            case 'Reject':
                return [
                    'title' => 'ไม่ตอบรับ',
                    'color' => 'danger',
                    'view' => '<span class="badge bg-danger">ไม่ตอบรับ</span>'
                ];
                break;
            case 'None':
                return [
                    'title' => 'ยังไม่ตอบรับ',
                    'color' => 'warning',
                    'view' => '<span class="badge bg-warning"><i class="fa-regular fa-hourglass-half"></i> ยังไม่ตอบรับ</span>'
                ];
                break;
            default:
                return [
                    'title' => 'ยังไม่ตอบรับ',
                    'color' => 'warning',
                    'view' => '<span class="badge bg-warning"><i class="fa-regular fa-hourglass-half"></i> ยังไม่ตอบรับ</span>'
                ];
        }
    }

    public function viewStatus()
    {
        return $this->getStatus($this->status);
    }

    public function getStatus($status)
    {
        $dateStart = AppHelper::convertToGregorian($this->date_start);
        $dateEnd = AppHelper::convertToGregorian($this->date_end);
        $title = '';
        $color = '';
        $view = '';
        $count = self::find()
            // ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->andFilterWhere(['status' => $status])
            ->andFilterWhere(['>=', 'date_start', $dateStart])
            ->andFilterWhere(['<=', 'date_end', $dateEnd])
            ->count();
        $total = self::find()->count();
        $data = AppHelper::viewStatus($status);
        $percent = $total > 0 ? ($count / $total * 100) : 0;

        return [
            'total' => $total,
            'count' => $count,
            'percent' => $percent,
            'title' => $data['title'],
            'color' => $data['color'],
            'view' => $data['view']
        ];
    }
}
