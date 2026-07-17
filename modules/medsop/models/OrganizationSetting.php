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
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }
}
