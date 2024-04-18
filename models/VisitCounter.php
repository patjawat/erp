<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "visit_counter".
 *
 * @property int $id
 * @property int|null $counter
 * @property string|null $ip
 * @property string|null $vdate
 * @property string|null $device
 * @property string|null $device_name
 * @property int|null $user_id
 * @property string $created_at
 * @property string|null $updated_at
 */
class VisitCounter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visit_counter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['counter', 'user_id'], 'integer'],
            [['vdate', 'created_at', 'updated_at'], 'safe'],
            [['ip', 'device', 'device_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'counter' => 'Counter',
            'ip' => 'Ip',
            'vdate' => 'Vdate',
            'device' => 'Device',
            'device_name' => 'Device Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
