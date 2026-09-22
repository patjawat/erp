<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * สิทธิ/กองทุนลูกหนี้ค่ารักษา (UC/สปส/ข้าราชการ/พรบ/ชำระเอง ...)
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $settle_days
 * @property int $sort_order
 * @property int $is_active
 * @property string|null $note
 */
class FinanceArFund extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_ar_fund}}';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['settle_days', 'sort_order', 'is_active'], 'integer'],
            [['note'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัสสิทธิ',
            'name' => 'ชื่อสิทธิ',
            'settle_days' => 'ระยะรับเงินคาดหมาย (วัน)',
            'sort_order' => 'ลำดับ',
            'is_active' => 'ใช้งาน',
            'note' => 'หมายเหตุ',
        ];
    }

    /** [id => name] ของสิทธิที่ใช้งาน */
    public static function activeList(): array
    {
        return self::find()->where(['is_active' => 1])
            ->select('name')->indexBy('id')
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->column();
    }

    /** [code => id] ใช้ตอน import แปลงรหัสสิทธิเป็น id */
    public static function codeMap(): array
    {
        return self::find()->select('id')->indexBy('code')->column();
    }
}
