<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "hrd_department".
 *
 * @property int $HR_DEPARTMENT_ID รหัสกลุ่มงาน
 * @property string|null $HR_DEPARTMENT_NAME ชื่อกุล่มงาน
 * @property string|null $BOOK_NUM เลขที่หนังสือกลุ่มงาน
 * @property string|null $ACTIVE สถานะ
 * @property string|null $BOOK_HR_ID สารบรรณกลุ่มงาน
 * @property string|null $LEADER_HR_ID หัวหน้ากลุ่มงาน
 * @property string|null $PHONE_IN เบอร์โทรภายใน
 * @property string|null $updated_at
 * @property string|null $created_at
 */
class Department extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hrd_department';
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
            [['ACTIVE'], 'string'],
            [['updated_at', 'created_at'], 'safe'],
            [['HR_DEPARTMENT_NAME'], 'string', 'max' => 100],
            [['BOOK_NUM'], 'string', 'max' => 255],
            [['BOOK_HR_ID', 'LEADER_HR_ID'], 'string', 'max' => 10],
            [['PHONE_IN'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HR_DEPARTMENT_ID' => 'รหัสกลุ่มงาน',
            'HR_DEPARTMENT_NAME' => 'ชื่อกุล่มงาน',
            'BOOK_NUM' => 'เลขที่หนังสือกลุ่มงาน',
            'ACTIVE' => 'สถานะ',
            'BOOK_HR_ID' => 'สารบรรณกลุ่มงาน',
            'LEADER_HR_ID' => 'หัวหน้ากลุ่มงาน',
            'PHONE_IN' => 'เบอร์โทรภายใน',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }
}
