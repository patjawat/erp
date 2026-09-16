<?php

namespace app\modules\complaint\models;

/**
 * ข้อมูลพื้นฐานตัวชี้วัด — จำนวน visit รายปีงบ (ตัวหาร CC01)
 *
 * @property int         $id
 * @property int         $fiscal_year
 * @property int         $visit_count
 * @property string|null $note
 */
class ComplaintIndicator extends ComplaintActiveRecord
{
    public static function tableName(): string
    {
        return '{{%complaint_indicator}}';
    }

    public function rules(): array
    {
        return [
            [['fiscal_year'], 'required'],
            [['fiscal_year', 'visit_count'], 'integer'],
            [['fiscal_year'], 'unique'],
            [['visit_count'], 'default', 'value' => 0],
            [['note'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'visit_count' => 'จำนวน visit',
            'note' => 'หมายเหตุ',
        ];
    }

    /** จำนวน visit ของปีงบ (0 ถ้ายังไม่กรอก) */
    public static function visitsFor(int $fiscalYear): int
    {
        return (int) (static::find()->select('visit_count')->where(['fiscal_year' => $fiscalYear])->scalar() ?: 0);
    }
}
