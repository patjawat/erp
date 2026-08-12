<?php

namespace app\modules\roster\models;

use yii\helpers\ArrayHelper;

/**
 * ประเภทเวร — ความหมายกลางที่ใช้ร่วมทั้ง รพ. (ช/บ/ด/ควบ)
 *
 * ไม่เก็บเวลาเข้า-ออกที่นี่ เพราะแต่ละหน่วยงานใช้เวลาไม่เท่ากัน — ดู UnitShift
 * อัตราค่าตอบแทน (เฟส 4) จะผูกกับ shift_type ไม่ใช่เวลา จึงต้องมีตัวตนกลางแบบนี้
 *
 * @property int         $id
 * @property string|null $ref
 * @property string      $code
 * @property string      $short_name
 * @property string      $title
 * @property int         $is_night
 * @property int         $is_ot
 * @property int         $is_extra
 * @property string|null $color
 * @property int         $sort_order
 * @property int         $active
 */
class ShiftType extends RosterActiveRecord
{
    public static function tableName()
    {
        return '{{%roster_shift_type}}';
    }

    public function rules()
    {
        return [
            [['code', 'short_name', 'title'], 'required'],
            [['is_night', 'is_ot', 'is_extra', 'sort_order', 'active', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 20],
            [['short_name'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 100],
            [['ref'], 'string', 'max' => 255],
            [['color'], 'in', 'range' => self::COLORS, 'message' => 'เลือกสีจากรายการที่กำหนด'],
            [['code'], 'unique', 'message' => 'รหัสนี้ถูกใช้แล้ว'],
            [['active'], 'default', 'value' => 1],
            [['is_night', 'is_ot', 'is_extra', 'sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัส',
            'short_name' => 'อักษรย่อ',
            'title' => 'ชื่อเวร',
            'is_night' => 'เป็นเวรดึก',
            'is_ot' => 'นอกเวลาราชการ',
            'is_extra' => 'เป็นเวรเสริม/ควบ',
            'color' => 'สีในตาราง',
            'sort_order' => 'ลำดับ',
            'active' => 'ใช้งาน',
        ];
    }

    /** @return self[] เรียงตามลำดับที่กำหนด ใช้เป็นหัวคอลัมน์ของกริด */
    public static function activeList(): array
    {
        return static::find()->where(['active' => 1])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
    }

    public static function mapById(): array
    {
        return ArrayHelper::index(static::activeList(), 'id');
    }

    /**
     * สีที่เลือกได้ — ชื่อสี Bootstrap เท่านั้น ไม่ใช่ hex
     * เพื่อให้ bg-*-subtle / text-*-emphasis เปลี่ยนตามธีมสว่าง-มืดเองโดยไม่ต้องเขียน CSS แยก
     */
    public const COLORS = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];

    public static function colorLabels(): array
    {
        return [
            'warning' => 'เหลือง', 'info' => 'ฟ้า', 'primary' => 'น้ำเงิน', 'danger' => 'แดง',
            'success' => 'เขียว', 'secondary' => 'เทา', 'dark' => 'เข้ม',
        ];
    }

    public function colorOrDefault(): string
    {
        return in_array((string) $this->color, self::COLORS, true) ? (string) $this->color : 'secondary';
    }

    /** class สำหรับช่องเวรในกริดและ badge — theme-aware ทั้งคู่ */
    public function cellClass(): string
    {
        $color = $this->colorOrDefault();
        return "bg-{$color}-subtle text-{$color}-emphasis";
    }

    /**
     * hex สำหรับ Excel เท่านั้น — ไฟล์ Excel ไม่มีธีม จึงต้องระบุค่าสีตรงๆ
     * ค่าเหล่านี้คือโทน subtle ของ Bootstrap 5.3 ในธีมสว่าง
     */
    public function excelFill(): string
    {
        $map = [
            'primary' => 'CFE2FF', 'secondary' => 'E2E3E5', 'success' => 'D1E7DD',
            'danger' => 'F8D7DA', 'warning' => 'FFF3CD', 'info' => 'CFF4FC', 'dark' => 'CED4DA',
        ];
        return $map[$this->colorOrDefault()] ?? 'E9ECEF';
    }
}
