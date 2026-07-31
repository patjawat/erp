<?php

namespace app\modules\pm\models;

use yii\db\ActiveRecord;

/**
 * ผู้รับผิดชอบโครงการ (ข้อ 11)
 *
 * @property int $id
 * @property int $project_id
 * @property int $sort
 * @property string|null $role owner=ผู้รับผิดชอบ / director=ผู้บังคับบัญชา
 * @property int|null $emp_id
 * @property string|null $fullname
 * @property string|null $position
 * @property string|null $phone
 */
class ProjectResponsible extends ActiveRecord
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_DIRECTOR = 'director';

    public static function tableName()
    {
        return '{{%project_responsibles}}';
    }

    public function rules()
    {
        return [
            [['fullname'], 'required'],
            [['project_id', 'sort', 'emp_id'], 'integer'],
            [['role'], 'string', 'max' => 20],
            [['fullname', 'position'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 30],
            [['sort'], 'default', 'value' => 0],
            [['role'], 'default', 'value' => self::ROLE_OWNER],
        ];
    }

    public function attributeLabels()
    {
        return [
            'role' => 'ประเภท',
            'fullname' => 'ชื่อ-สกุล',
            'position' => 'ตำแหน่ง',
            'phone' => 'เบอร์โทรศัพท์',
        ];
    }

    public static function roleList(): array
    {
        return [
            self::ROLE_OWNER => 'ผู้รับผิดชอบงาน',
            self::ROLE_DIRECTOR => 'ผู้บังคับบัญชา (ผอ.รพ.สต./สสอ./ผอ.รพ./หน.กลุ่มงาน)',
        ];
    }
}
