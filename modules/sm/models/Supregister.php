<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "sup_register".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $regisnumber เลขทะเบียนคุม
 * @property string|null $start_date วันที่
 * @property string|null $price มูลค่า
 * @property string|null $status สถานะ
 * @property string|null $dep_code หน่วยงาน
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Supregister extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sup_register';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['start_date', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'price', 'status', 'dep_code'], 'string', 'max' => 255],
            [['regisnumber'], 'string', 'max' => 50],
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
            'regisnumber' => 'เลขทะเบียนคุม',
            'start_date' => 'วันที่',
            'price' => 'มูลค่า',
            'status' => 'สถานะ',
            'dep_code' => 'หน่วยงาน',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }
}
