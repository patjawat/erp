<?php

namespace app\modules\medsop\models;

use yii\db\ActiveRecord;

class DocumentAudience extends ActiveRecord
{
    public const SCENARIO_REPLACE = 'replace';
    public const TYPE_ORGANIZATION = 'ORGANIZATION';
    public const TYPE_TEAM_GROUP = 'TEAM_GROUP';
    public const TYPE_EMPLOYEE = 'EMPLOYEE';

    public static function tableName()
    {
        return '{{%medsop_document_audience}}';
    }

    public function rules()
    {
        return [
            [['document_id', 'audience_type', 'audience_id'], 'required'],
            [['document_id', 'audience_id', 'audience_version_id', 'created_by'], 'integer'],
            [['include_children', 'required'], 'boolean'],
            [['created_at'], 'safe'],
            [['audience_type'], 'in', 'range' => self::typeOptions()],
            [['audience_version_id'], 'required', 'when' => static function (self $model): bool {
                return $model->audience_type === self::TYPE_TEAM_GROUP;
            }],
            [['document_id', 'audience_type', 'audience_id', 'audience_version_id'], 'unique', 'except' => self::SCENARIO_REPLACE],
        ];
    }

    public static function typeOptions(): array
    {
        return [self::TYPE_ORGANIZATION, self::TYPE_TEAM_GROUP, self::TYPE_EMPLOYEE];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REPLACE] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
    }

    public function beforeValidate()
    {
        if ($this->audience_type !== self::TYPE_TEAM_GROUP) {
            $this->audience_version_id = 0;
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if ($insert && !$this->created_at) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }
}
