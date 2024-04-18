<?php

namespace app\modules\warehouse\models;

use Yii;

/**
 * This is the model class for table "whdep".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $depsup_code รหัส
 * @property string|null $depsup_detail รหัส
 * @property string|null $depsup_type รหัส
 * @property string|null $depsup_store รหัส
 * @property string|null $depsup_unit รหัส
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Whdep extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'whdep';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'updated_at', 'created_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'depsup_code', 'depsup_detail', 'depsup_type', 'depsup_store'], 'string', 'max' => 255],
            [['depsup_unit'], 'string', 'max' => 20],
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
            'depsup_code' => 'รหัส',
            'depsup_detail' => 'รหัส',
            'depsup_type' => 'รหัส',
            'depsup_store' => 'รหัส',
            'depsup_unit' => 'รหัส',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }
}
