<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * บัญชีที่ใช้จ่ายเงินยืม — ช่อง “ยืมจากบัญชี” ในฟอร์ม และช่องแหล่งเงินในสัญญา แบบ 8500
 */
class FinanceLoanAccount extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%finance_loan_account}}';
    }

    public function rules()
    {
        return [
            [['account_no', 'name'], 'required'],
            [['account_no'], 'string', 'max' => 50],
            [['account_no'], 'unique'],
            [['name'], 'string', 'max' => 255],
            [['bank_name'], 'string', 'max' => 100],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => true],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'account_no' => 'เลขที่บัญชี',
            'name' => 'ชื่อบัญชี',
            'bank_name' => 'ธนาคาร',
            'is_active' => 'เปิดใช้งาน',
            'sort_order' => 'ลำดับ',
        ];
    }

    /** แสดงแบบเดียวกับที่ผู้ใช้เห็นในระบบเดิม คือเลขบัญชีตามด้วยชื่อในวงเล็บ */
    public function displayName(): string
    {
        return $this->account_no . ' (' . $this->name . ')';
    }

    /** @return array id => "เลขบัญชี (ชื่อ)" สำหรับ dropdown */
    public static function options(bool $activeOnly = true): array
    {
        $query = self::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        if ($activeOnly) {
            $query->where(['is_active' => true]);
        }
        return ArrayHelper::map($query->all(), 'id', fn(self $m) => $m->displayName());
    }
}
