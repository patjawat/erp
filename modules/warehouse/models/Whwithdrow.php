<?php

namespace app\modules\warehouse\models;

use Yii;

/**
 * This is the model class for table "whwithdrow".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $withdrow_code รหัส
 * @property string|null $withdrow_date วันที่เบิก
 * @property string|null $withdrow_store คลังที่ต้องการเบิก
 * @property string|null $withdrow_dep หน่วยงานที่ขอเบิก
 * @property string|null $withdrow_hr เจ้าหน้าที่เบิก
 * @property string|null $withdrow_pay วันที่จ่าย
 * @property string|null $withdrow_status สถานะ
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Whwithdrow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'whwithdrow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['withdrow_date', 'withdrow_pay', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'withdrow_code', 'withdrow_store', 'withdrow_dep', 'withdrow_hr'], 'string', 'max' => 255],
            [['withdrow_status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'withdrow_code' => 'รหัส',
            'withdrow_date' => 'วันที่เบิก',
            'withdrow_store' => 'คลังที่ต้องการเบิก',
            'withdrow_dep' => 'หน่วยงานที่ขอเบิก',
            'withdrow_hr' => 'เจ้าหน้าที่เบิก',
            'withdrow_pay' => 'วันที่จ่าย',
            'withdrow_status' => 'สถานะ',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }
}
