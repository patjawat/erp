<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "bidding".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property int|null $emp_id
 * @property string|null $name
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Bidding extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bidding';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'updated_at', 'created_at'], 'safe'],
            [['ref', 'code', 'name'], 'string', 'max' => 255],
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
            'code' => 'Code',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
