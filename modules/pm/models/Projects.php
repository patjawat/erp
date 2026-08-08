<?php

namespace app\modules\pm\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\hr\models\Organization;
use app\modules\hr\models\Employees;

/**
 * ตาราง "projects" — แบบเสนอโครงการ
 *
 * @property int $id
 * @property string|null $code เลขที่โครงการ
 * @property string $name ชื่อโครงการ
 * @property int|null $thai_year ปีงบประมาณ (พ.ศ.)
 * @property int|null $department_id หน่วยงานเจ้าของโครงการ (tree.id)
 * @property string|null $strategy_type ใน/นอกยุทธศาสตร์
 * @property string|null $rationale หลักการและเหตุผล
 * @property string|null $target_group กลุ่มเป้าหมาย
 * @property string|null $method วิธีดำเนินการ
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $dead_line_date
 * @property string|null $duration_text
 * @property string|null $location สถานที่
 * @property string|null $lecturer วิทยากร
 * @property string|null $evaluation การประเมินผล
 * @property string|null $expected_result ผลที่คาดว่าจะได้รับ
 * @property float $budget_total งบประมาณรวม
 * @property string|null $budget_source แหล่งงบประมาณ
 * @property string|null $budget_detail รายละเอียดงบประมาณ
 * @property string $status
 * @property array|null $data_json
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property ProjectObjective[] $objectives
 * @property ProjectIndicator[] $indicators
 * @property ProjectResponsible[] $responsibles
 * @property Organization $department
 */
class Projects extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PROPOSED = 'proposed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_DONE = 'done';

    public const STRATEGY_IN = 'in';
    public const STRATEGY_OUT = 'out';

    public static function tableName()
    {
        return '{{%projects}}';
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_PROPOSED => 'เสนอขออนุมัติ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_REJECTED => 'ไม่อนุมัติ',
            self::STATUS_DONE => 'ดำเนินการเสร็จสิ้น',
        ];
    }

    /** ฟิลด์ข้อความยาวที่จัดเป็นข้อ/หัวข้อย่อยได้ — กรอง HTML ก่อนบันทึกเสมอ */
    public const RICH_TEXT_ATTRIBUTES = ['rationale', 'target_group', 'method', 'lecturer', 'evaluation', 'expected_result', 'budget_detail'];

    public function beforeValidate()
    {
        foreach (self::RICH_TEXT_ATTRIBUTES as $attribute) {
            if ($this->$attribute !== null && $this->$attribute !== '') {
                $this->$attribute = \app\components\RichText::sanitize((string) $this->$attribute) ?: null;
            }
        }
        return parent::beforeValidate();
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['thai_year', 'department_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['budget_total'], 'number'],
            [['rationale', 'target_group', 'method', 'lecturer', 'evaluation', 'expected_result', 'budget_detail', 'data_json'], 'safe'],
            [['start_date', 'end_date', 'dead_line_date', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'location', 'duration_text', 'budget_source'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['strategy_type'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['budget_total'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'รหัสโครงการ',
            'code' => 'เลขที่โครงการ',
            'name' => 'ชื่อโครงการ',
            'thai_year' => 'ปีงบประมาณ',
            'department_id' => 'หน่วยงานเจ้าของโครงการ',
            'strategy_type' => 'ยุทธศาสตร์',
            'rationale' => '1. หลักการและเหตุผล',
            'target_group' => '4. กลุ่มเป้าหมาย',
            'method' => '5. วิธีดำเนินการ (งานและกิจกรรม)',
            'start_date' => 'วันที่เริ่ม',
            'end_date' => 'วันที่สิ้นสุด',
            'dead_line_date' => 'วันครบกำหนด',
            'duration_text' => '6. ระยะเวลาการดำเนินการ',
            'location' => '7. สถานที่ดำเนินโครงการ',
            'lecturer' => '8. วิทยากร',
            'evaluation' => '9. การประเมินผลโครงการ',
            'expected_result' => '10. ผลที่คาดว่าจะได้รับ',
            'budget_total' => '12. งบประมาณ (บาท)',
            'budget_source' => 'แหล่งงบประมาณ',
            'budget_detail' => 'รายละเอียดงบประมาณ',
            'status' => 'สถานะ',
        ];
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::class => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
            \yii\behaviors\BlameableBehavior::class => [
                'class' => \yii\behaviors\BlameableBehavior::class,
            ],
        ];
    }

    public function getObjectives()
    {
        return $this->hasMany(ProjectObjective::class, ['project_id' => 'id'])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getIndicators()
    {
        return $this->hasMany(ProjectIndicator::class, ['project_id' => 'id'])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getResponsibles()
    {
        return $this->hasMany(ProjectResponsible::class, ['project_id' => 'id'])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getDepartment()
    {
        return $this->hasOne(Organization::class, ['id' => 'department_id']);
    }

    /** ชื่อหน่วยงานโหนดเดียว (แบบสั้น) */
    public function departmentName(): string
    {
        return $this->department->name ?? '-';
    }

    /** ชื่อหน่วยงานตามลำดับชั้นในผังองค์กร เช่น "กลุ่มอำนวยการ › กลุ่มงานบริหารทั่วไป › งานพัสดุ" */
    public function departmentPath(string $sep = ' › '): string
    {
        return $this->department ? $this->department->pathLabel($sep) : '-';
    }

    public function statusLabel(): string
    {
        $status = $this->status ?: self::STATUS_DRAFT;
        return self::statusList()[$status] ?? (string) $status;
    }

    public function statusBadgeClass(): string
    {
        return [
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_PROPOSED => 'bg-warning text-dark',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_DONE => 'bg-primary',
        ][$this->status] ?? 'bg-secondary';
    }

    public function creatorEmployee()
    {
        if (!$this->created_by) {
            return null;
        }
        return Employees::findOne(['user_id' => $this->created_by]);
    }

    /**
     * รหัสย่อของหน่วยงาน (จาก medsop_organization_setting.code ซึ่งเป็นรหัสหน่วยงานกลาง)
     * ถ้ายังไม่กำหนด จะ fallback เป็น ORG{id}
     */
    public static function orgCode(?int $departmentId): string
    {
        if (!$departmentId) {
            return 'ORG';
        }
        $setting = \app\modules\medsop\models\OrganizationSetting::findOne($departmentId);
        return ($setting && $setting->code) ? $setting->code : 'ORG' . $departmentId;
    }

    /**
     * สร้างรหัสโครงการอัตโนมัติตามรูปแบบใน pm_setting.code_pattern
     * token: {org} รหัสย่อหน่วยงาน · {year} พ.ศ.เต็ม · {yy} พ.ศ.2หลัก · {sequence} ลำดับรัน 4 หลัก (ต่อหน่วยงาน+ปี)
     */
    public static function generateCode(?int $departmentId, ?int $thaiYear): string
    {
        $thaiYear = $thaiYear ?: (int) (date('Y') + 543);
        $pattern = PmSetting::value(PmSetting::CODE_PATTERN, 'P-{org}-{yy}{sequence}');

        // ลำดับรัน = จำนวนโครงการของหน่วยงาน+ปีนี้ (รวมที่ลบแบบ soft) + 1 เพื่อไม่ใช้เลขซ้ำ
        $seq = (int) self::find()
            ->where(['thai_year' => $thaiYear])
            ->andWhere($departmentId ? ['department_id' => $departmentId] : ['department_id' => null])
            ->count() + 1;

        return strtr($pattern, [
            '{org}' => self::orgCode($departmentId),
            '{year}' => (string) $thaiYear,
            '{yy}' => substr((string) $thaiYear, -2),
            '{sequence}' => str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
        ]);
    }
}
