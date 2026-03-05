<?php

namespace app\modules\jd\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\db\ActiveRecord;

/**
 * JD ของแต่ละพนักงาน (โหลดจาก template ได้ แก้ไข/เพิ่มได้)
 *
 * @property int $id
 * @property int $emp_id
 * @property int|null $template_id template ที่โหลดมา
 * @property string $created_at
 * @property string|null $updated_at
 * @property JdEmployeeSection[] $sections
 * @property Employees $employee
 * @property JdTemplate|null $template
 */
class JdEmployee extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_employee}}';
    }

    public function rules()
    {
        return [
            [['emp_id'], 'required'],
            [['emp_id', 'template_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['template_id'], 'exist', 'targetClass' => JdTemplate::class, 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'template_id' => 'Template ที่ใช้',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getSections()
    {
        return $this->hasMany(JdEmployeeSection::class, ['jd_employee_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getTemplate()
    {
        return $this->hasOne(JdTemplate::class, ['id' => 'template_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            if ($insert) {
                $this->created_at = $now;
            }
            $this->updated_at = $now;
            return true;
        }
        return false;
    }
}
