<?php

namespace app\modules\qms\models;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/**
 * ข้อกำหนดของมาตรฐาน (แม่แบบ ใช้ซ้ำทุกปี, เป็นชั้นด้วย parent_id)
 *
 * @property int $id
 * @property int $standard_id
 * @property int|null $parent_id
 * @property string|null $code
 * @property string $title
 * @property string|null $detail
 * @property string|null $evidence_hint
 * @property int|null $default_assignee_unit_id
 * @property int|null $default_assignee_emp_id
 * @property int $sort
 * @property int $is_active
 */
class Requirement extends QmsActiveRecord
{
    public static function tableName(): string
    {
        return '{{%qms_requirement}}';
    }

    public function rules(): array
    {
        return [
            [['standard_id', 'title'], 'required'],
            // เว้นว่างจากฟอร์ม (dropdown prompt) → null กัน FK พัง / เก็บเป็น 0
            [['parent_id', 'default_assignee_unit_id', 'default_assignee_emp_id'], 'default', 'value' => null],
            [['standard_id', 'parent_id', 'default_assignee_unit_id', 'default_assignee_emp_id', 'sort', 'is_active'], 'integer'],
            [['detail'], 'string'],
            [['code'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 500],
            [['evidence_hint'], 'string', 'max' => 255],
            [['is_active'], 'default', 'value' => 1],
            [['sort'], 'default', 'value' => 0],
            [['standard_id'], 'exist', 'targetClass' => Standard::class, 'targetAttribute' => 'id'],
            [['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'standard_id' => 'มาตรฐาน',
            'parent_id' => 'ข้อแม่',
            'code' => 'เลขข้อ',
            'title' => 'ชื่อข้อกำหนด',
            'detail' => 'รายละเอียด',
            'evidence_hint' => 'ประเภทหลักฐานที่ต้องมี',
            'default_assignee_unit_id' => 'ผู้รับผิดชอบ (หน่วยงาน)',
            'default_assignee_emp_id' => 'ผู้รับผิดชอบ (บุคคล)',
            'sort' => 'ลำดับ',
            'is_active' => 'ใช้งาน',
        ];
    }

    public function getStandard()
    {
        return $this->hasOne(Standard::class, ['id' => 'standard_id']);
    }

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    public function getLinks()
    {
        return $this->hasMany(RequirementLink::class, ['requirement_id' => 'id']);
    }

    public function getDefaultAssigneeUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'default_assignee_unit_id']);
    }

    public function getDefaultAssigneeEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'default_assignee_emp_id']);
    }
}
