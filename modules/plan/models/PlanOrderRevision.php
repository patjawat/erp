<?php

namespace app\modules\plan\models;

class PlanOrderRevision extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'plan_order_revision';
    }

    public function rules()
    {
        return [
            [['plan_order_id', 'cycle_no', 'version_type', 'status', 'plan_json', 'created_at'], 'required'],
            [['plan_order_id', 'cycle_no', 'created_by'], 'integer'],
            [['order_price', 'month_1', 'month_2', 'month_3', 'month_4', 'month_5', 'month_6', 'month_7', 'month_8', 'month_9', 'month_10', 'month_11', 'month_12'], 'number'],
            [['plan_json', 'items_json', 'created_at'], 'safe'],
            [['version_type'], 'string', 'max' => 30],
            [['status'], 'string', 'max' => 20],
        ];
    }
}
