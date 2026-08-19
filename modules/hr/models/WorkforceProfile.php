<?php

namespace app\modules\hr\models;

use app\models\Categorise;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * ตัวขับเคลื่อนของโรงพยาบาลที่สูตรกรอบอัตรากำลังต้องใช้ — แยกรายปี
 *
 * เตียง ประชากร จำนวนรถ เปลี่ยนได้ทุกปี กรอบของปีเก่าจึงต้องอ่านจากค่าของปีนั้น
 * ไม่ใช่ค่าปัจจุบัน ไม่งั้นเอกสารย้อนหลังจะเปลี่ยนตัวเลขเอง
 *
 * @property int $id
 * @property int $thai_year
 * @property string|null $level_code
 * @property int|null $bed_total
 * @property int|null $active_bed
 * @property int|null $ward_count
 * @property int|null $catchment_population
 * @property int|null $vehicle_count
 * @property string|null $office_area_sqm
 * @property string|null $garden_rai
 * @property int|null $security_post
 * @property string|null $laundry_kg_per_day
 */
class WorkforceProfile extends \yii\db\ActiveRecord
{
    /** สถานะรอบจัดทำกรอบของปี — 1 ปี = 1 รอบ */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CLOSED = 'closed';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'ร่าง',
        self::STATUS_SUBMITTED => 'ส่งให้ผู้อำนวยการพิจารณา',
        self::STATUS_APPROVED => 'อนุมัติแล้ว',
        self::STATUS_CLOSED => 'ปิดรอบ',
    ];

    /** ตัวขับเคลื่อนทั้งหมด: attribute => [ป้าย, หน่วย, ใช้กับสายงานอะไร] */
    public const DRIVERS = [
        'bed_total' => ['จำนวนเตียงทั้งหมด', 'เตียง', 'หาช่วงเกณฑ์ของสายสนับสนุน'],
        'active_bed' => ['Active bed', 'เตียง', 'นักโภชนาการ 1 คน : 50 เตียง'],
        'ward_count' => ['จำนวนหอผู้ป่วย', 'หอ', 'พยาบาลวิชาชีพ (หอละ 30 เตียง) · พนักงานทำความสะอาด 1 หอ : 4 คน'],
        'catchment_population' => ['ประชากรที่รับผิดชอบ (CUP)', 'คน', 'นวก.สาธารณสุข 1 : 1,250 · ทันตสาธารณสุข 1 : 7,500'],
        'vehicle_count' => ['รถที่ใช้งาน', 'คัน', 'พนักงานขับรถยนต์ ร้อยละ 70 ของจำนวนรถ'],
        'office_area_sqm' => ['พื้นที่สำนักงาน', 'ตร.ม.', 'พนักงานทำความสะอาด 800 ตร.ม. : 1 คน'],
        'garden_rai' => ['พื้นที่สวน', 'ไร่', 'พนักงานเกษตรพื้นฐาน 3 ไร่ : 1 คน'],
        'security_post' => ['จุดรักษาความปลอดภัย', 'จุด', 'รปภ. 1 จุด : 1 คน : 1 เวร 8 ชม.'],
        'laundry_kg_per_day' => ['ผ้าสะอาดต่อวัน', 'กก./วัน', 'พนักงานซักฟอก 150 กก. : 1 คน'],
    ];

    public static function tableName()
    {
        return '{{%workforce_profile}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['thai_year'], 'required'],
            [['thai_year', 'bed_total', 'active_bed', 'ward_count', 'catchment_population', 'vehicle_count', 'security_post'], 'integer', 'min' => 0],
            [['office_area_sqm', 'garden_rai', 'laundry_kg_per_day'], 'number', 'min' => 0],
            [['level_code'], 'string', 'max' => 10],
            [['level_code'], 'in', 'range' => array_keys(self::levelOptions()), 'skipOnEmpty' => true],
            [['note', 'approval_note'], 'string'],
            [['status'], 'in', 'range' => array_keys(self::STATUS_LABELS)],
            [['submitted_by', 'approved_by'], 'integer'],
            [['submitted_at', 'approved_at'], 'safe'],
            [['data_json'], 'safe'],
            [['thai_year'], 'unique', 'message' => 'ปีนี้มีข้อมูลอยู่แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        $labels = [
            'thai_year' => 'ปีงบประมาณ',
            'level_code' => 'ระดับโรงพยาบาล',
            'note' => 'หมายเหตุ',
        ];

        foreach (self::DRIVERS as $attribute => [$label]) {
            $labels[$attribute] = $label;
        }

        return $labels;
    }

    /** ระดับโรงพยาบาลจากชุดข้อมูลกลาง — เพิ่ม/แก้ได้จากหน้าตั้งค่า ไม่ fix ในโค้ด */
    public static function levelOptions(): array
    {
        $rows = Categorise::find()
            ->where(['name' => 'hospital_level', 'active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        return ArrayHelper::map($rows, 'code', 'title');
    }

    /** โปรไฟล์ของปี — สร้างแถวเปล่าให้ถ้ายังไม่มี เพื่อให้ฟอร์มมีตัวให้ผูก */
    public static function forYear(int $thaiYear): self
    {
        $model = static::findOne(['thai_year' => $thaiYear]);

        if ($model === null) {
            $model = new static(['thai_year' => $thaiYear]);
            $previous = static::find()
                ->where(['<', 'thai_year', $thaiYear])
                ->orderBy(['thai_year' => SORT_DESC])
                ->one();

            // ปีใหม่ตั้งต้นด้วยค่าปีก่อน — ส่วนใหญ่ไม่เปลี่ยน แก้เฉพาะที่ต่าง
            if ($previous !== null) {
                $model->level_code = $previous->level_code;
                foreach (array_keys(self::DRIVERS) as $attribute) {
                    $model->$attribute = $previous->$attribute;
                }
            }
        }

        return $model;
    }

    /** ตัวขับเคลื่อนที่ยังไม่ได้กรอก — ใช้เตือนว่ากรอบสายไหนยังคำนวณไม่ได้ */
    public function missingDrivers(): array
    {
        $missing = [];
        foreach (self::DRIVERS as $attribute => [$label, $unit, $usedBy]) {
            if ($this->$attribute === null || $this->$attribute === '') {
                $missing[$attribute] = $label;
            }
        }

        return $missing;
    }

    public function isComplete(): bool
    {
        return $this->level_code !== null && $this->level_code !== '' && $this->missingDrivers() === [];
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) $this->status;
    }

    /**
     * รอบที่อนุมัติหรือปิดแล้ว ห้ามแก้ตัวเลขอีก
     *
     * เอกสารที่ส่ง สสจ. ไปแล้วต้องตรงกับที่ระบบแสดงเสมอ ไม่งั้นอ้างอิงย้อนหลังไม่ได้
     */
    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_CLOSED], true);
    }

    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_DRAFT && !$this->isNewRecord;
    }

    public function canApprove(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}
