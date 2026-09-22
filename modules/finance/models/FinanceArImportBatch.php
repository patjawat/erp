<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ชุดนำเข้าลูกหนี้จาก HIS/ระบบเคลม
 *
 * @property int $id
 * @property int|null $ar_fund_id
 * @property int|null $fiscal_year
 * @property int|null $period_month
 * @property string|null $source_label
 * @property string|null $file_name
 * @property int $row_count
 * @property string $total_amount
 * @property string|null $note
 */
class FinanceArImportBatch extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%finance_ar_import_batch}}';
    }

    public function rules()
    {
        return [
            [['ar_fund_id', 'fiscal_year', 'period_month', 'row_count', 'created_by'], 'integer'],
            [['total_amount'], 'number'],
            [['note'], 'string'],
            [['source_label', 'file_name'], 'string', 'max' => 255],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->created_at = $this->created_at ?: date('Y-m-d H:i:s');
            $this->created_by = $this->created_by ?: (Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null);
        }
        return true;
    }
}
