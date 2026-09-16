<?php

namespace app\modules\complaint\models;

/**
 * ตัวเลือกกลาง (แทน Master_Data sheet เดิม)
 *
 * group: channel | type | patient_right | relationship | action_kind | reject_reason
 *
 * @property int         $id
 * @property string      $group
 * @property string      $name
 * @property string|null $code
 * @property int         $sort
 * @property int         $is_active
 */
class ComplaintMaster extends ComplaintActiveRecord
{
    /** กลุ่มตัวเลือกที่รองรับ (label ไทยสำหรับหน้าตั้งค่า) */
    public const GROUPS = [
        'channel' => 'ช่องทางการร้องเรียน',
        'type' => 'ประเภทเรื่องร้องเรียน',
        'patient_right' => 'สิทธิผู้ป่วย',
        'relationship' => 'ความสัมพันธ์ผู้ร้อง',
        'action_kind' => 'ประเภทการดำเนินงาน',
        'reject_reason' => 'เหตุผลไม่รับพิจารณา',
    ];

    public static function tableName(): string
    {
        return '{{%complaint_master}}';
    }

    public function rules(): array
    {
        return [
            [['group', 'name'], 'required'],
            [['group'], 'in', 'range' => array_keys(self::GROUPS)],
            [['sort', 'is_active'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 64],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'group' => 'กลุ่ม',
            'name' => 'ชื่อรายการ',
            'code' => 'รหัส',
            'sort' => 'ลำดับ',
            'is_active' => 'ใช้งาน',
        ];
    }

    /**
     * map id=>name ของกลุ่มที่ระบุ (เฉพาะที่ใช้งาน) สำหรับ dropdown
     *
     * @return array<int,string>
     */
    public static function options(string $group): array
    {
        return self::find()
            ->select('name')
            ->where(['group' => $group, 'is_active' => 1])
            ->indexBy('id')
            ->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC])
            ->column();
    }
}
