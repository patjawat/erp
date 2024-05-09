<?php

namespace app\modules\helpdesk\models;

use Yii;

/**
 * This is the model class for table "asset_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property string|null $date_start
 * @property string|null $date_end
 * @property string|null $name
 * @property int|null $user_id
 * @property int|null $emp_id
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Repair extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asset_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_start', 'date_end', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['user_id', 'emp_id', 'created_by', 'updated_by'], 'integer'],
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
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'name' => 'Name',
            'user_id' => 'User ID',
            'emp_id' => 'Emp ID',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
