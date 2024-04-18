<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "hrd_department_sub_sub".
 *
 * @property int $HR_DEPARTMENT_SUB_SUB_ID
 * @property string|null $HR_DEPARTMENT_SUB_SUB_NAME
 * @property string|null $BOOK_NUM
 * @property string|null $LEADER_HR_ID
 * @property string|null $ACTIVE
 * @property string|null $HR_DEPARTMENT_SUB_ID
 * @property string|null $DEP_CODE
 * @property string|null $PHONE_IN เบอร์โทรภายใน
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property string|null $LINE_TOKEN
 */
class DepartmentSub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hrd_department_sub_sub';
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
            [['HR_DEPARTMENT_SUB_SUB_NAME', 'BOOK_NUM', 'LINE_TOKEN'], 'string', 'max' => 255],
            [['LEADER_HR_ID', 'HR_DEPARTMENT_SUB_ID', 'DEP_CODE'], 'string', 'max' => 10],
            [['PHONE_IN'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HR_DEPARTMENT_SUB_SUB_ID' => 'Hr Department Sub Sub ID',
            'HR_DEPARTMENT_SUB_SUB_NAME' => 'Hr Department Sub Sub Name',
            'BOOK_NUM' => 'Book Num',
            'LEADER_HR_ID' => 'Leader Hr ID',
            'ACTIVE' => 'Active',
            'HR_DEPARTMENT_SUB_ID' => 'Hr Department Sub ID',
            'DEP_CODE' => 'Dep Code',
            'PHONE_IN' => 'Phone In',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'LINE_TOKEN' => 'Line Token',
        ];
    }
}
