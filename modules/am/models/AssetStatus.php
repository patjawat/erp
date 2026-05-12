<?php

namespace app\modules\am\models;

use Yii;

/**
 * This is the model class for table "asset_status".
 *
 * @property string $id
 * @property string $name
 * @property int|null $sort_order
 * @property int $is_active
 *
 * @property Asset[] $assets
 */
class AssetStatus extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asset_status';
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
            [['name'], 'string', 'max' => 100],
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
            'name' => 'Name',
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
        return $this->hasMany(Asset::class, ['asset_status' => 'id']);
    }

}
