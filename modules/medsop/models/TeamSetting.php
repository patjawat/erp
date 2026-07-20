<?php

namespace app\modules\medsop\models;

use app\modules\hr\models\TeamGroup;
use yii\db\ActiveRecord;

class TeamSetting extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%medsop_team_setting}}';
    }

    public static function primaryKey()
    {
        return ['team_group_id'];
    }

    public function rules()
    {
        return [
            [['team_group_id'], 'required'],
            [['team_group_id', 'leader_employee_id', 'created_by', 'updated_by'], 'integer'],
            [['active'], 'boolean'],
            [['code'], 'string', 'max' => 20],
            [['document_categories'], 'string'],
            [['code'], 'match', 'pattern' => '/^[A-Z0-9_-]+$/', 'message' => 'ใช้อักษรอังกฤษตัวพิมพ์ใหญ่ ตัวเลข ขีดกลาง หรือขีดล่างเท่านั้น'],
            [['code'], 'validateUniqueCode'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function validateUniqueCode($attribute): void
    {
        if ($this->hasErrors($attribute) || $this->$attribute === null || $this->$attribute === '') {
            return;
        }

        $teamConflict = self::find()
            ->with('teamGroup')
            ->where([$attribute => $this->$attribute])
            ->andWhere(['<>', 'team_group_id', (int) $this->team_group_id])
            ->one();
        if ($teamConflict !== null) {
            $name = $teamConflict->teamGroup ? $teamConflict->teamGroup->title : 'รหัสทีม ' . $teamConflict->team_group_id;
            $this->addError($attribute, sprintf('อักษรย่อ "%s" ถูกใช้โดยทีมประสาน "%s" แล้ว', $this->$attribute, $name));
            return;
        }

        $organizationConflict = OrganizationSetting::find()
            ->with('organization')
            ->where([$attribute => $this->$attribute])
            ->one();
        if ($organizationConflict !== null) {
            $name = $organizationConflict->organization ? $organizationConflict->organization->name : 'รหัสหน่วยงาน ' . $organizationConflict->organization_id;
            $this->addError($attribute, sprintf('อักษรย่อ "%s" ถูกใช้โดยหน่วยงาน "%s" แล้ว', $this->$attribute, $name));
        }
    }

    public function getTeamGroup()
    {
        return $this->hasOne(TeamGroup::class, ['id' => 'team_group_id']);
    }
}
