<?php

namespace app\modules\complaint\models;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\db\ActiveQuery;

/**
 * เรื่องร้องเรียน (ตารางหลัก) — รวมข้อมูลขั้น 1 แจ้งเรื่อง / 2 รับเรื่อง / 3 ประเมิน / 5 ปิดเคส
 * ไว้ในแถวเดียว (workflow 1:1) ส่วนขั้น 4 ดำเนินงานเป็นตารางลูก complaint_action (1:หลาย)
 *
 * @property int         $id
 * @property string      $complaint_no
 * @property string      $tracking_code
 * @property int         $fiscal_year
 * @property string      $status
 * @property string|null $complaint_date
 * @property string|null $complaint_time
 * @property int|null    $channel_id
 * @property int|null    $type_id
 * @property string      $title
 * @property string|null $detail
 * @property string|null $reporter_name
 * @property string|null $reporter_phone
 * @property int|null    $reporter_relation_id
 * @property int         $is_anonymous
 * @property int|null    $assigned_unit_id
 * @property int|null    $assigned_to
 * @property string|null $intake_date
 * @property int|null    $intake_by
 * @property string|null $intake_checklist
 * @property string|null $intake_address
 * @property string|null $intake_impact
 * @property int|null    $patient_right_id
 * @property string|null $intake_need
 * @property string|null $intake_note
 * @property string|null $assess_date
 * @property int|null    $assess_by
 * @property int|null    $severity_level
 * @property string|null $risk_level
 * @property int         $need_rca
 * @property string|null $assess_decision
 * @property string|null $assess_note
 * @property int|null    $reject_reason_id
 * @property string|null $respond_due
 * @property string|null $review_due
 * @property string|null $reply_due
 * @property string|null $close_due
 * @property string|null $close_date
 * @property int|null    $close_by
 * @property string|null $lesson_learned
 * @property string|null $remedy
 * @property string|null $close_note
 */
class Complaint extends ComplaintActiveRecord
{
    public const STATUS_REPORTED = 'reported';
    public const STATUS_INTAKE = 'intake';
    public const STATUS_ASSESSED = 'assessed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REJECTED = 'rejected';

    public const DECISION_ACCEPT = 'accept';
    public const DECISION_REFER = 'refer';
    public const DECISION_REJECT = 'reject';

    /**
     * ลำดับขั้น workflow (ใช้เทียบความคืบหน้า/เปิดปิดปุ่ม)
     * rejected เป็นสถานะปลายทางเช่นเดียวกับ closed
     */
    public const STATUS_STEP = [
        self::STATUS_REPORTED => 1,
        self::STATUS_INTAKE => 2,
        self::STATUS_ASSESSED => 3,
        self::STATUS_IN_PROGRESS => 4,
        self::STATUS_CLOSED => 5,
        self::STATUS_REJECTED => 5,
    ];

    /**
     * SLA (จำนวนวัน) ตามระดับความรุนแรง — ค่าปลายกรอบจากตารางในเอกสารสรุป (ข้อ 8)
     * [respond, review, reply, remedy, close] ; null = ไม่กำหนด
     */
    public const SLA = [
        1 => ['respond' => 3, 'review' => 3, 'reply' => 3, 'remedy' => null, 'close' => 5],
        2 => ['respond' => 3, 'review' => 14, 'reply' => 14, 'remedy' => null, 'close' => 15],
        3 => ['respond' => 1, 'review' => 14, 'reply' => 14, 'remedy' => 15, 'close' => 30],
        4 => ['respond' => 1, 'review' => 14, 'reply' => 14, 'remedy' => 15, 'close' => 60],
    ];

    public static function tableName(): string
    {
        return '{{%complaint}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'fiscal_year'], 'required'],
            [['fiscal_year', 'channel_id', 'type_id', 'reporter_relation_id', 'assigned_to',
                'intake_by', 'patient_right_id', 'assess_by', 'severity_level', 'need_rca',
                'reject_reason_id', 'close_by', 'is_anonymous'], 'integer'],
            [['assigned_unit_id'], 'integer'],
            [['detail', 'intake_checklist', 'intake_impact', 'intake_need', 'intake_note',
                'assess_note', 'lesson_learned', 'remedy', 'close_note'], 'string'],
            [['complaint_date', 'complaint_time', 'intake_date', 'assess_date', 'close_date',
                'respond_due', 'review_due', 'reply_due', 'close_due'], 'safe'],
            [['severity_level'], 'in', 'range' => [1, 2, 3, 4], 'skipOnEmpty' => true],
            [['assess_decision'], 'in', 'range' => [self::DECISION_ACCEPT, self::DECISION_REFER, self::DECISION_REJECT], 'skipOnEmpty' => true],
            [['title'], 'string', 'max' => 500],
            [['reporter_name', 'intake_address'], 'string', 'max' => 500],
            [['reporter_phone'], 'string', 'max' => 64],
            [['risk_level'], 'string', 'max' => 32],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'complaint_no' => 'เลขที่เรื่อง',
            'tracking_code' => 'รหัสติดตาม',
            'fiscal_year' => 'ปีงบประมาณ',
            'status' => 'สถานะ',
            'complaint_date' => 'วันที่ร้องเรียน',
            'complaint_time' => 'เวลา',
            'channel_id' => 'ช่องทาง',
            'type_id' => 'ประเภท',
            'title' => 'ชื่อเรื่องโดยย่อ',
            'detail' => 'รายละเอียด',
            'reporter_name' => 'ชื่อผู้ร้อง',
            'reporter_phone' => 'เบอร์ติดต่อ',
            'reporter_relation_id' => 'ความสัมพันธ์',
            'is_anonymous' => 'ไม่ประสงค์ออกนาม',
            'assigned_unit_id' => 'หน่วยงานรับผิดชอบ',
            'assigned_to' => 'ผู้รับผิดชอบ',
            'intake_date' => 'วันที่รับเรื่อง',
            'intake_address' => 'ที่อยู่ผู้ร้อง',
            'intake_impact' => 'ผลกระทบ',
            'patient_right_id' => 'สิทธิผู้ป่วยที่เกี่ยวข้อง',
            'intake_need' => 'ความต้องการของผู้ร้อง',
            'intake_note' => 'บันทึกเพิ่มเติม',
            'assess_date' => 'วันที่ประเมิน',
            'severity_level' => 'ระดับความรุนแรง',
            'risk_level' => 'ระดับความเสี่ยง',
            'need_rca' => 'ต้องทำ RCA',
            'assess_decision' => 'ผลการพิจารณา',
            'assess_note' => 'บันทึกการประเมิน',
            'reject_reason_id' => 'เหตุผลไม่รับพิจารณา',
            'close_date' => 'วันที่ปิดเคส',
            'lesson_learned' => 'ถอดบทเรียน',
            'remedy' => 'การเยียวยา',
            'close_note' => 'ข้อเสนอแนะ/บันทึกปิดเคส',
        ];
    }

    // --- Labels -----------------------------------------------------------

    /** @return array<string,string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_REPORTED => 'แจ้งเรื่อง',
            self::STATUS_INTAKE => 'รับเรื่องแล้ว',
            self::STATUS_ASSESSED => 'ประเมินแล้ว',
            self::STATUS_IN_PROGRESS => 'กำลังดำเนินงาน',
            self::STATUS_CLOSED => 'ปิดเคส',
            self::STATUS_REJECTED => 'ไม่รับพิจารณา',
        ];
    }

    /** สี badge (bootstrap) ต่อสถานะ */
    public static function statusColor(string $status): string
    {
        return [
            self::STATUS_REPORTED => 'secondary',
            self::STATUS_INTAKE => 'info',
            self::STATUS_ASSESSED => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_CLOSED => 'success',
            self::STATUS_REJECTED => 'dark',
        ][$status] ?? 'secondary';
    }

    /** @return array<int,string> */
    public static function severityLabels(): array
    {
        return [
            1 => 'ระดับ 1 (เล็กน้อย)',
            2 => 'ระดับ 2 (ปานกลาง)',
            3 => 'ระดับ 3 (รุนแรง)',
            4 => 'ระดับ 4 (รุนแรงมาก)',
        ];
    }

    /** @return array<string,string> */
    public static function decisionLabels(): array
    {
        return [
            self::DECISION_ACCEPT => 'รับไว้พิจารณา',
            self::DECISION_REFER => 'ส่งต่อ',
            self::DECISION_REJECT => 'ไม่รับไว้พิจารณา',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function step(): int
    {
        return self::STATUS_STEP[$this->status] ?? 1;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_REJECTED], true);
    }

    // --- Relations --------------------------------------------------------

    public function getChannel(): ActiveQuery
    {
        return $this->hasOne(ComplaintMaster::class, ['id' => 'channel_id']);
    }

    public function getType(): ActiveQuery
    {
        return $this->hasOne(ComplaintMaster::class, ['id' => 'type_id']);
    }

    public function getReporterRelation(): ActiveQuery
    {
        return $this->hasOne(ComplaintMaster::class, ['id' => 'reporter_relation_id']);
    }

    public function getPatientRight(): ActiveQuery
    {
        return $this->hasOne(ComplaintMaster::class, ['id' => 'patient_right_id']);
    }

    public function getRejectReason(): ActiveQuery
    {
        return $this->hasOne(ComplaintMaster::class, ['id' => 'reject_reason_id']);
    }

    public function getAssignedUnit(): ActiveQuery
    {
        return $this->hasOne(Organization::class, ['id' => 'assigned_unit_id']);
    }

    public function getAssignee(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'assigned_to']);
    }

    public function getActions(): ActiveQuery
    {
        return $this->hasMany(ComplaintAction::class, ['complaint_id' => 'id'])
            ->orderBy(['action_date' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getAttachments(): ActiveQuery
    {
        return $this->hasMany(ComplaintAttachment::class, ['complaint_id' => 'id'])
            ->orderBy(['category' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getSurveys(): ActiveQuery
    {
        return $this->hasMany(ComplaintSurvey::class, ['complaint_id' => 'id']);
    }

    public function getLogs(): ActiveQuery
    {
        return $this->hasMany(ComplaintLog::class, ['complaint_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
}
