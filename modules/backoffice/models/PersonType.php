<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "hrd_person_type".
 *
 * @property int $HR_PERSON_TYPE_ID
 * @property string|null $HR_PERSON_TYPE_NAME
 * @property string|null $HR_LEAVE04_CON เงื่อนไขการลาพักผ่อน
 * @property string|null $HR_LEAVE04_CMD หมายเหตุ
 * @property int|null $HR_LEAVE04_DAY จำนวนลาพักผ่อนต่อปี
 * @property string|null $updated_at
 * @property string|null $created_at
 */
class PersonType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hrd_person_type';
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
            [['HR_LEAVE04_CON'], 'string'],
            [['HR_LEAVE04_DAY'], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['HR_PERSON_TYPE_NAME'], 'string', 'max' => 100],
            [['HR_LEAVE04_CMD'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HR_PERSON_TYPE_ID' => 'Hr Person Type ID',
            'HR_PERSON_TYPE_NAME' => 'Hr Person Type Name',
            'HR_LEAVE04_CON' => 'เงื่อนไขการลาพักผ่อน',
            'HR_LEAVE04_CMD' => 'หมายเหตุ',
            'HR_LEAVE04_DAY' => 'จำนวนลาพักผ่อนต่อปี',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }
}
