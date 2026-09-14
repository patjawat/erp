<?php

namespace app\modules\km\models;

/**
 * ลิงก์หลักฐาน polymorphic — ผูกกิจกรรมไปหารายการที่มีอยู่แล้วในระบบอื่น
 * โดยไม่คัดลอกข้อมูลเข้ามาเก็บซ้ำ (หัวใจของ evidence hub)
 *
 * @property int $id
 * @property int $activity_id
 * @property string $item_type  task | kpi | risk | dms_doc | medsop
 * @property string $ref_id     id/ref ของรายการปลายทาง
 * @property string|null $ref_label
 * @property string|null $note
 * @property string $ref
 */
class KmActivityLink extends KmActiveRecord
{
    public const TYPE_TASK = 'task';
    public const TYPE_KPI = 'kpi';
    public const TYPE_RISK = 'risk';
    public const TYPE_DMS = 'dms_doc';
    public const TYPE_MEDSOP = 'medsop';

    public static function typeLabels(): array
    {
        return [
            self::TYPE_TASK => 'งานมอบหมาย',
            self::TYPE_KPI => 'ตัวชี้วัด KPI',
            self::TYPE_RISK => 'ความเสี่ยง',
            self::TYPE_DMS => 'เอกสาร (สารบรรณ)',
            self::TYPE_MEDSOP => 'SOP/WI',
        ];
    }

    public static function tableName(): string
    {
        return '{{%km_activity_link}}';
    }

    public function rules(): array
    {
        return [
            [['activity_id', 'item_type', 'ref_id'], 'required'],
            [['activity_id'], 'integer'],
            [['item_type'], 'in', 'range' => array_keys(self::typeLabels())],
            [['ref_id'], 'string', 'max' => 64],
            [['ref_label'], 'string', 'max' => 500],
            [['note'], 'string', 'max' => 255],
            [['activity_id'], 'exist', 'targetClass' => KmActivity::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'item_type' => 'ประเภทหลักฐาน',
            'ref_label' => 'รายการ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->item_type] ?? $this->item_type;
    }

    public function getActivity()
    {
        return $this->hasOne(KmActivity::class, ['id' => 'activity_id']);
    }
}
