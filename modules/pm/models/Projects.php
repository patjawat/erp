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

    /** โครงการใช้งบประมาณ ส่วนแผนงาน/กิจกรรมอาจไม่ใช้งบ */
    public const WORK_PROJECT = 'project';
    public const WORK_ACTIVITY = 'activity';

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

    public static function workTypeList(): array
    {
        return [
            self::WORK_PROJECT => 'โครงการ',
            self::WORK_ACTIVITY => 'แผนงาน/กิจกรรม',
        ];
    }

    public function isActivity(): bool { return $this->work_type === self::WORK_ACTIVITY; }
    public function workTypeLabel(): string { return self::workTypeList()[$this->work_type] ?? self::workTypeList()[self::WORK_PROJECT]; }

    /** โครงการในแผน = ผูกกลยุทธ์ไว้ · โครงการนอกแผน = ไม่ได้ผูก */
    public static function strategyTypeList(): array
    {
        return [
            self::STRATEGY_IN => 'ในแผนยุทธศาสตร์',
            self::STRATEGY_OUT => 'นอกแผนยุทธศาสตร์',
        ];
    }

    public function isInStrategy(): bool { return !empty($this->tactic_id); }

    /**
     * ยึดการผูกกลยุทธ์จริงเป็นหลัก ไม่ใช่คอลัมน์ strategy_type
     * เพราะการลบกลยุทธ์ทำให้ tactic_id ถูกล้างที่ฐานข้อมูลโดยไม่ผ่าน beforeSave
     * คอลัมน์จึงอาจค้างเป็น in ทั้งที่ไม่ได้สังกัดกลยุทธ์ใดแล้ว
     */
    public function strategyTypeLabel(): string
    {
        return self::strategyTypeList()[$this->isInStrategy() ? self::STRATEGY_IN : self::STRATEGY_OUT];
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

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->syncOrgUnit();
        // ประเภทยุทธศาสตร์ยึดจากการผูกกลยุทธ์ ไม่ให้ตั้งค่าขัดกันเองได้
        $this->strategy_type = $this->isInStrategy() ? self::STRATEGY_IN : self::STRATEGY_OUT;
        // ออกรหัสให้เสมอเมื่อยังไม่มี ครอบคลุมการสร้างจากหน้าแผนยุทธศาสตร์ด้วย
        if ($insert && trim((string) $this->code) === '') {
            $this->code = self::generateCode(
                $this->org_unit_id ? (int) $this->org_unit_id : null,
                $this->thai_year ? (int) $this->thai_year : null,
                (string) ($this->work_type ?: self::WORK_PROJECT)
            );
        } elseif (!$insert) {
            $this->renumberOnTypeChange();
        }
        return true;
    }

    /**
     * เปลี่ยนชนิดงานขณะยังเป็นฉบับร่าง ให้ออกรหัสใหม่ตามซีรีส์ใหม่
     *
     * ทำเฉพาะฉบับร่างและเฉพาะรหัสที่ระบบออกให้เอง เพราะเมื่อเสนอขออนุมัติแล้ว
     * รหัสถูกอ้างในเอกสารภายนอก การเปลี่ยนจะทำให้อ้างอิงเดิมเสีย
     */
    private function renumberOnTypeChange(): void
    {
        $previous = $this->getOldAttribute('work_type');
        if ($previous === null || $previous === $this->work_type || $this->status !== self::STATUS_DRAFT) {
            return;
        }
        $oldPattern = $previous === self::WORK_ACTIVITY
            ? PmSetting::value(PmSetting::ACTIVITY_CODE_PATTERN, 'A-{org}-{yy}{sequence}')
            : PmSetting::value(PmSetting::CODE_PATTERN, 'P-{org}-{yy}{sequence}');
        $prefix = strstr($oldPattern, '{', true);
        // รหัสที่ผู้ใช้พิมพ์เองไม่ขึ้นต้นด้วยซีรีส์เดิม จึงไม่ควรไปแตะ
        if ($prefix === false || $prefix === '' || !str_starts_with((string) $this->code, $prefix)) {
            return;
        }
        $this->code = self::generateCode(
            $this->org_unit_id ? (int) $this->org_unit_id : null,
            $this->thai_year ? (int) $this->thai_year : null,
            (string) $this->work_type
        );
    }

    /**
     * ผูกทะเบียนหน่วยงานกับผังโครงสร้างแบบสองทาง
     * ฟอร์มใหม่เลือกจากทะเบียน → เติม department_id กลับเมื่อเป็นหน่วยในผัง (ทีมประสานเป็น null)
     * ข้อมูลเดิมที่มีแต่ department_id → เติม org_unit_id ของปีเดียวกันให้
     */
    public function syncOrgUnit(): void
    {
        if (empty($this->thai_year)) {
            return;
        }
        if (!empty($this->org_unit_id)) {
            $unit = (new \yii\db\Query())->select(['source', 'ref_id'])->from('org_unit')
                ->where(['id' => (int) $this->org_unit_id])->one();
            // ทีมประสาน/หน่วยนอกผังไม่มี ref_id — ต้องล้าง department_id ไม่ให้ค้างของเดิม
            $this->department_id = ($unit && $unit['source'] === \app\modules\settings\models\OrgUnit::SOURCE_STRUCTURE && $unit['ref_id'])
                ? (int) $unit['ref_id'] : null;
            return;
        }
        if (!empty($this->department_id)) {
            $this->org_unit_id = (new \yii\db\Query())->select('id')->from('org_unit')
                ->where(['thai_year' => (int) $this->thai_year, 'source' => \app\modules\settings\models\OrgUnit::SOURCE_STRUCTURE, 'ref_id' => (int) $this->department_id])
                ->scalar() ?: null;
        }
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['thai_year', 'department_id', 'org_unit_id', 'tactic_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['org_unit_id'], 'required', 'when' => static fn ($m) => empty($m->department_id), 'enableClientValidation' => false, 'message' => 'กรุณาเลือกหน่วยงาน'],
            [['budget_total'], 'number'],
            [['rationale', 'target_group', 'method', 'lecturer', 'evaluation', 'expected_result', 'budget_detail', 'data_json'], 'safe'],
            [['start_date', 'end_date', 'dead_line_date', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'location', 'duration_text', 'budget_source'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['strategy_type'], 'string', 'max' => 20],
            [['work_type'], 'in', 'range' => array_keys(self::workTypeList())],
            [['work_type'], 'default', 'value' => self::WORK_PROJECT],
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
            'org_unit_id' => 'หน่วยงาน/ทีมเจ้าของโครงการ',
            'tactic_id' => 'กลยุทธ์ที่รองรับ',
            'work_type' => 'ชนิดงาน',
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

    /** กลยุทธ์ที่โครงการนี้รองรับ — มีค่าเฉพาะโครงการในแผนยุทธศาสตร์ */
    public function getTactic()
    {
        return $this->hasOne(StrategyTactic::class, ['id' => 'tactic_id']);
    }

    /** หน่วยงานในทะเบียนกลาง (org_unit) — รองรับทีมประสานที่ไม่มีในผังบุคลากร */
    public function getOrgUnit()
    {
        return $this->hasOne(\app\modules\settings\models\OrgUnit::class, ['id' => 'org_unit_id']);
    }

    /** ชื่อหน่วยงานโหนดเดียว (แบบสั้น) */
    public function departmentName(): string
    {
        return $this->orgUnit->name ?? $this->department->name ?? '-';
    }

    /**
     * ชื่อหน่วยงานสำหรับแสดงผล
     * หน่วยในผังแสดงเป็นลำดับชั้น เช่น "กลุ่มอำนวยการ › กลุ่มงานบริหารทั่วไป › งานพัสดุ"
     * ส่วนทีมประสาน/หน่วยนอกผังไม่มีลำดับชั้น จึงแสดงชื่อจากทะเบียนตรง ๆ
     */
    public function departmentPath(string $sep = ' › '): string
    {
        if ($this->department) {
            return $this->department->pathLabel($sep);
        }
        return $this->orgUnit->name ?? '-';
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
     * อักษรย่อของหน่วยงานจากทะเบียนกลาง (org_unit.code) ซึ่งครอบคลุมทั้งหน่วยในผังและทีมประสาน
     * ถ้ายังไม่กำหนดอักษรย่อ จะ fallback เป็น ORG{id}
     */
    public static function orgCode(?int $orgUnitId): string
    {
        if (!$orgUnitId) {
            return 'ORG';
        }
        $code = (new \yii\db\Query())->select('code')->from('org_unit')->where(['id' => (int) $orgUnitId])->scalar();
        return $code ?: 'ORG' . $orgUnitId;
    }

    /**
     * สร้างรหัสอัตโนมัติตามรูปแบบใน pm_setting — โครงการและแผนงาน/กิจกรรมแยกซีรีส์กัน
     * token: {org} อักษรย่อหน่วยงาน · {year} พ.ศ.เต็ม · {yy} พ.ศ.2หลัก · {sequence} ลำดับรัน 4 หลัก
     */
    public static function generateCode(?int $orgUnitId, ?int $thaiYear, string $workType = self::WORK_PROJECT): string
    {
        $thaiYear = $thaiYear ?: (int) \app\modules\plan\components\PlanHelper::currentPlanYear();
        $pattern = $workType === self::WORK_ACTIVITY
            ? PmSetting::value(PmSetting::ACTIVITY_CODE_PATTERN, 'A-{org}-{yy}{sequence}')
            : PmSetting::value(PmSetting::CODE_PATTERN, 'P-{org}-{yy}{sequence}');

        // ลำดับรันแยกตามชนิดงาน+หน่วยงาน+ปี (รวมที่ลบแบบ soft) เพื่อไม่ใช้เลขซ้ำ
        $seq = (int) self::find()
            ->where(['thai_year' => $thaiYear, 'work_type' => $workType])
            ->andWhere($orgUnitId ? ['org_unit_id' => $orgUnitId] : ['org_unit_id' => null])
            ->count() + 1;

        return strtr($pattern, [
            '{org}' => self::orgCode($orgUnitId),
            '{year}' => (string) $thaiYear,
            '{yy}' => substr((string) $thaiYear, -2),
            '{sequence}' => str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
        ]);
    }
}
