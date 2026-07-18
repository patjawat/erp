<?php

namespace app\modules\medsop\models;

use app\modules\hr\models\Organization;
use yii\db\ActiveRecord;

class OrganizationSetting extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%medsop_organization_setting}}';
    }

    public static function primaryKey()
    {
        return ['organization_id'];
    }

    public function rules()
    {
        return [
            [['organization_id'], 'required'],
            [['organization_id', 'created_by', 'updated_by'], 'integer'],
            [['active'], 'boolean'],
            [['code'], 'string', 'max' => 20],
            [['coordinator_team'], 'string', 'max' => 255],
            [['code'], 'match', 'pattern' => '/^[A-Z0-9_-]+$/', 'message' => 'ใช้อักษรอังกฤษตัวพิมพ์ใหญ่ ตัวเลข ขีดกลาง หรือขีดล่างเท่านั้น'],
            [['code'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }
}
