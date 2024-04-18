<?php

namespace app\models;

use app\models\Districts;
use Yii;

/**
 * This is the model class for table "provinces".
 *
 * @property int $id
 * @property string $code
 * @property string $name_th
 * @property string $name_en
 * @property int $geography_id
 */
class Province extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'provinces';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name_th', 'name_en'], 'required'],
            [['geography_id'], 'integer'],
            [['code'], 'string', 'max' => 2],
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
            'geography_id' => 'Geography ID',
        ];
    }

    public function getDistricts()
    {
        return $this->hasMany(District::className(), ['province_id' => 'id']);
    }


}
