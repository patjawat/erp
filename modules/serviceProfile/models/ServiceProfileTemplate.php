<?php

namespace app\modules\serviceProfile\models;

use app\modules\hr\models\Organization;
use Yii;
use yii\db\ActiveRecord;

class ServiceProfileTemplate extends ActiveRecord
{
    public $org_unit_id;
    public const OWNER_DEPARTMENT = 'department';
    public const OWNER_COORDINATOR_TEAM = 'coordinator_team';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETIRED = 'retired';

    public static function tableName()
    {
        return '{{%service_profile_template}}';
    }

    public function rules()
    {
        return [
            [['owner_id', 'name', 'revision_no', 'effective_fiscal_year'], 'required'],
            [['owner_id', 'org_unit_id', 'revision_no', 'effective_fiscal_year', 'parent_template_id', 'created_by', 'updated_by'], 'integer'],
            [['is_active'], 'boolean'],
            [['description'], 'string'],
            [['owner_type'], 'in', 'range' => [self::OWNER_DEPARTMENT, self::OWNER_COORDINATOR_TEAM]],
            [['lifecycle_status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_RETIRED]],
            [['owner_type', 'lifecycle_status'], 'string', 'max' => 30],
            [['name', 'owner_name_snapshot'], 'string', 'max' => 255],
            [['owner_type'], 'default', 'value' => self::OWNER_DEPARTMENT],
            [['revision_no'], 'default', 'value' => 1],
            [['lifecycle_status'], 'default', 'value' => self::STATUS_DRAFT],
            [['is_active'], 'default', 'value' => 0],
            [['created_at', 'updated_at'], 'safe'],
            [['owner_id', 'revision_no'], 'unique', 'targetAttribute' => ['owner_type', 'owner_id', 'revision_no'], 'message' => 'หน่วยงานนี้มี Template Revision ดังกล่าวแล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'owner_id' => 'หน่วยงาน', 'org_unit_id' => 'หน่วยงาน / ทีมประสาน',
            'name' => 'ชื่อ Template',
            'revision_no' => 'Revision',
            'effective_fiscal_year' => 'เริ่มใช้ปีงบประมาณ',
            'description' => 'คำอธิบาย',
            'lifecycle_status' => 'สถานะ',
        ];
    }

    public function getSections()
    {
        return $this->hasMany(ServiceProfileTemplateSection::class, ['template_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getOwner()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_id']);
    }

    public function getParentTemplate()
    {
        return $this->hasOne(self::class, ['id' => 'parent_template_id']);
    }

    public static function statusLabels(): array
    {
        return [self::STATUS_DRAFT => 'ฉบับร่าง', self::STATUS_ACTIVE => 'ใช้งานอยู่', self::STATUS_RETIRED => 'สิ้นสุดแล้ว'];
    }

    public function beforeValidate()
    {
        if ($this->org_unit_id && $this->isNewRecord) {
            try {
                $resolved = (new \app\modules\serviceProfile\services\OwnerDirectoryService())->resolveOwner((int) $this->org_unit_id, (int) $this->effective_fiscal_year);
                $this->owner_type = $resolved['owner_type'];
                $this->owner_id = $resolved['owner_id'];
                $this->owner_name_snapshot = $resolved['unit']->name;
            } catch (\DomainException $e) {
                $this->addError('org_unit_id', $e->getMessage());
            }
        } elseif ($this->owner_type === self::OWNER_DEPARTMENT && $this->owner_id) {
            $owner = Organization::findOne($this->owner_id);
            if ($owner) {
                $this->owner_name_snapshot = $owner->name;
            }
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        if ($insert) {
            $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
