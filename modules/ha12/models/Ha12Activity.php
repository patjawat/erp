<?php

namespace app\modules\ha12\models;

/**
 * กิจกรรม HA12 — ทะเบียนกลาง 12 กิจกรรมทบทวน
 *
 * @property int $id
 * @property int $no          ลำดับกิจกรรม 1-12
 * @property string $name
 * @property string $form_type general|med|mrec|kpi
 * @property string|null $data_hint
 * @property int $sort
 * @property int $is_active
 * @property string $ref
 */
class Ha12Activity extends Ha12ActiveRecord
{
    public const FORM_GENERAL = 'general'; // ตารางทบทวนทั่วไป
    public const FORM_MED = 'med';         // ความคลาดเคลื่อนทางยา (กิจกรรม 7)
    public const FORM_MREC = 'mrec';       // ความสมบูรณ์เวชระเบียน (กิจกรรม 9)
    public const FORM_KPI = 'kpi';         // ติดตามตัวชี้วัด (กิจกรรม 12)

    public static function formTypeLabels(): array
    {
        return [
            self::FORM_GENERAL => 'ตารางทบทวนทั่วไป',
            self::FORM_MED => 'ความคลาดเคลื่อนทางยา',
            self::FORM_MREC => 'ความสมบูรณ์เวชระเบียน',
            self::FORM_KPI => 'ติดตามตัวชี้วัด',
        ];
    }

    public static function tableName(): string
    {
        return '{{%ha12_activity}}';
    }

    public function rules(): array
    {
        return [
            [['no', 'name'], 'required'],
            [['no', 'sort', 'is_active'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['data_hint'], 'string', 'max' => 500],
            [['form_type'], 'in', 'range' => array_keys(self::formTypeLabels())],
            [['form_type'], 'default', 'value' => self::FORM_GENERAL],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'no' => 'ลำดับ',
            'name' => 'ชื่อกิจกรรม',
            'form_type' => 'ชนิดฟอร์ม',
            'data_hint' => 'ข้อมูลหลัก',
            'is_active' => 'เปิดใช้งาน',
        ];
    }

    public function formTypeLabel(): string
    {
        return self::formTypeLabels()[$this->form_type] ?? $this->form_type;
    }

    /** เกณฑ์ประเมินของกิจกรรม (เวอร์ชันที่ใช้งานอยู่) เรียงตามระดับ */
    public function getCriteria()
    {
        return $this->hasMany(Ha12Criteria::class, ['activity_id' => 'id'])
            ->andWhere(['is_active' => 1])
            ->orderBy(['level' => SORT_ASC]);
    }

    /** รายการกิจกรรมที่เปิดใช้งาน เรียงตามลำดับ */
    public static function activeList(): array
    {
        return static::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'no' => SORT_ASC])
            ->all();
    }
}
