<?php

namespace app\modules\usermanager\models;

use Yii;

/**
 * This is the model class for table "session".
 *
 * @property string $id
 * @property int $expire
 * @property resource $data
 * @property int $user_id
 */
class Session extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['expire', 'user_id'], 'integer'],
            [['data'], 'string'],
            [['ip_address','login_time'], 'safe'],
            [['id'], 'string', 'max' => 40],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expire' => 'Expire',
            'data' => 'Data',
            'user_id' => 'User ID',
        ];
    }
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
