<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "amphures".
 *
 * @property int $id
 * @property string $code
 * @property string $name_th
 * @property string $name_en
 * @property int $province_id
 */
class Amphure extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'amphures';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name_th', 'name_en'], 'required'],
            [['province_id'], 'integer'],
            [['code'], 'string', 'max' => 4],
            [['name_th', 'name_en'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name_th' => 'Name Th',
            'name_en' => 'Name En',
            'province_id' => 'Province ID',
        ];
    }
}
