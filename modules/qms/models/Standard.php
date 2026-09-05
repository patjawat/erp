<?php

namespace app\modules\qms\models;

use app\modules\hr\models\Organization;

/**
 * มาตรฐาน (HA / ISO9001 / PDPA ...)
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $short_name
 * @property string|null $description
 * @property int|null $owner_unit_id
 * @property string|null $owner_label
 * @property string|null $icon
 * @property string|null $color
 * @property int $sort
 * @property int $is_active
 */
class Standard extends QmsActiveRecord
{
    public static function tableName(): string
    {
        return '{{%qms_standard}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['description'], 'string'],
            [['owner_unit_id', 'sort', 'is_active'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
            [['short_name', 'icon'], 'string', 'max' => 64],
            [['name', 'owner_label'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 32],
            [['is_active'], 'default', 'value' => 1],
            [['sort'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'รหัสมาตรฐาน',
            'name' => 'ชื่อมาตรฐาน',
            'short_name' => 'ชื่อย่อ',
            'description' => 'รายละเอียด',
            'owner_unit_id' => 'หน่วยงานเจ้าของ',
            'owner_label' => 'เจ้าของ (ข้อความ)',
            'icon' => 'ไอคอน',
            'color' => 'สี',
            'sort' => 'ลำดับ',
            'is_active' => 'ใช้งาน',
        ];
    }

    public function getOwnerUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getRequirements()
    {
        return $this->hasMany(Requirement::class, ['standard_id' => 'id']);
    }

    public function getCycles()
    {
        return $this->hasMany(Cycle::class, ['standard_id' => 'id']);
    }

    /** ชื่อเจ้าของสำหรับแสดงผล (unit ก่อน ตกไปที่ข้อความ) */
    public function getOwnerName(): string
    {
        if ($this->owner_unit_id && $this->ownerUnit) {
            return (string) ($this->ownerUnit->name ?? $this->owner_label ?? '');
        }
        return (string) ($this->owner_label ?? '');
    }
}
