<?php

namespace app\modules\am\models;

use Yii;

/**
 * This is the model class for table "asset_condition".
 *
 * @property string $id good / fair / damaged / worn
 * @property string $name ชื่อภาษาไทยสำหรับแสดงผล
 * @property int $sort_order
 * @property int $is_active
 *
 * @property Asset[] $assets
 */
class AssetCondition extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asset_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['id', 'name'], 'required'],
            [['sort_order', 'is_active'], 'integer'],
            [['id'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 50],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'good / fair / damaged / worn',
            'name' => 'ชื่อภาษาไทยสำหรับแสดงผล',
            'sort_order' => 'Sort Order',
            'is_active' => 'Is Active',
        ];
    }

    /**
     * Gets query for [[Assets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(Asset::class, ['condition' => 'id']);
    }

}
