<?php

namespace app\modules\jobdescription\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * หัวข้อใน JD พนักงาน (แก้ไข/เพิ่มได้)
 *
 * @property int $id
 * @property int $jd_employee_id
 * @property string $title
 * @property string|null $content
 * @property int $sort_order
 * @property JdEmployee $jdEmployee
 */
class JdEmployeeSection extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_employee_section}}';
    }

    public function rules()
    {
        return [
            [['jd_employee_id', 'title'], 'required'],
            [['jd_employee_id', 'sort_order'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['sort_order'], 'default', 'value' => 0],
            [['jd_employee_id'], 'exist', 'targetClass' => JdEmployee::class, 'targetAttribute' => ['jd_employee_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jd_employee_id' => 'JD พนักงาน',
            'title' => 'หัวข้อ',
            'content' => 'เนื้อหา',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getJdEmployee()
    {
        return $this->hasOne(JdEmployee::class, ['id' => 'jd_employee_id']);
    }
}
