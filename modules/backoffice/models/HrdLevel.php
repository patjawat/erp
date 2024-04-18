<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "hrd_level".
 *
 * @property int $HR_LEVEL_ID
 * @property string|null $HR_LEVEL_NAME
 * @property string|null $updated_at
 * @property string|null $created_at
 */
class HrdLevel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hrd_level';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at', 'created_at'], 'safe'],
            [['HR_LEVEL_NAME'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HR_LEVEL_ID' => 'Hr Level ID',
            'HR_LEVEL_NAME' => 'Hr Level Name',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }
}
