<?php

namespace app\modules\warehouse\models;

use Yii;

/**
 * This is the model class for table "warehouses".
 *
 * @property int $warehouse_id รหัสคลังสินค้า
 * @property string $warehouse_name ชื่อคลังสินค้า
 * @property string $warehouse_code รหัสคลัง เช่น รหัส รพ. รพสต.
 * @property string $warehouse_type ประเภทการเคลื่อนไหว (MAIN = คลังหลัก, SUB = ตลังย่อย, BRANCH = สาขา รพสต.)
 * @property int $is_main เป็นคลังหลักหรือไม่ (true = คลังหลัก, false = คลังย่อย)
 */
class Warehouse extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warehouses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_name', 'warehouse_type'], 'required'],
            [['warehouse_type'], 'string'],
            [['is_main'], 'integer'],
            [['warehouse_name', 'warehouse_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'warehouse_id' => 'Warehouse ID',
            'warehouse_name' => 'Warehouse Name',
            'warehouse_code' => 'Warehouse Code',
            'warehouse_type' => 'Warehouse Type',
            'is_main' => 'Is Main',
        ];
    }
}
