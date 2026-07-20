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
            [['organization_id', 'coordinator_employee_id', 'coordinator_team_group_id', 'created_by', 'updated_by'], 'integer'],
            [['active'], 'boolean'],
            [['code'], 'string', 'max' => 20],
            [['coordinator_team'], 'string', 'max' => 255],
            [['document_categories'], 'string'],
            [['code'], 'match', 'pattern' => '/^[A-Z0-9_-]+$/', 'message' => 'ใช้อักษรอังกฤษตัวพิมพ์ใหญ่ ตัวเลข ขีดกลาง หรือขีดล่างเท่านั้น'],
            [['code'], 'validateUniqueCode'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    public function validateUniqueCode($attribute): void
    {
        if ($this->hasErrors($attribute) || $this->$attribute === null || $this->$attribute === '') {
            return;
        }

        $conflict = self::find()
            ->with('organization')
            ->where([$attribute => $this->$attribute])
            ->andWhere(['<>', 'organization_id', (int) $this->organization_id])
            ->one();

        if ($conflict === null) {
            $teamConflict = TeamSetting::find()
                ->with('teamGroup')
                ->where([$attribute => $this->$attribute])
                ->one();
            if ($teamConflict === null) {
                return;
            }
            $teamName = $teamConflict->teamGroup
                ? $teamConflict->teamGroup->title
                : 'รหัสทีม ' . $teamConflict->team_group_id;
            $this->addError(
                $attribute,
                sprintf('อักษรย่อ "%s" ถูกใช้โดยทีมประสาน "%s" แล้ว กรุณาใช้อักษรย่ออื่น', $this->$attribute, $teamName)
            );
            return;
        }

        $organizationName = $conflict->organization
            ? $conflict->organization->name
            : 'รหัสหน่วยงาน ' . $conflict->organization_id;
        $this->addError(
            $attribute,
            sprintf('อักษรย่อ "%s" ถูกใช้โดยหน่วยงาน "%s" แล้ว กรุณาใช้อักษรย่ออื่น', $this->$attribute, $organizationName)
        );
    }
}
