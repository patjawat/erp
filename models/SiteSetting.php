<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "site_setting".
 *
 * @property string|null $id
 * @property string|null $data_json
 */
class SiteSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json'], 'safe'],
            [['id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_json' => 'Data Json',
        ];
    }
}
