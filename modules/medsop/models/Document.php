<?php

namespace app\modules\medsop\models;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\db\ActiveRecord;

class Document extends ActiveRecord
{
    public const TYPE_SOP = 'SOP';
    public const TYPE_WI = 'WI';
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PUBLISHED = 'PUBLISHED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_ARCHIVED = 'ARCHIVED';

    public static function tableName()
    {
        return '{{%medsop_document}}';
    }

    public function rules()
    {
        return [
            [['document_no', 'title', 'document_type', 'organization_id', 'objective'], 'required'],
            [['organization_id', 'current_revision', 'created_emp_id', 'created_by', 'updated_by', 'published_by', 'deleted_by'], 'integer'],
            [['objective', 'scope'], 'string'],
            [['published_at', 'deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['document_no'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
            [['document_type'], 'in', 'range' => [self::TYPE_SOP, self::TYPE_WI]],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['document_no'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'document_no' => 'เลขที่เอกสาร',
            'title' => 'ชื่อเอกสาร',
            'document_type' => 'ประเภทเอกสาร',
            'organization_id' => 'แผนก/ฝ่าย',
            'objective' => 'วัตถุประสงค์',
            'scope' => 'ขอบเขตการใช้งาน',
            'status' => 'สถานะ',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_PENDING => 'รออนุมัติ',
            self::STATUS_PUBLISHED => 'เผยแพร่แล้ว',
            self::STATUS_REJECTED => 'ส่งกลับแก้ไข',
            self::STATUS_ARCHIVED => 'เลิกใช้งาน',
        ];
    }

    public static function typeOptions(): array
    {
        return [self::TYPE_SOP => 'SOP', self::TYPE_WI => 'WI'];
    }

    public static function getStatusBadgeConfigFor(string $status): array
    {
        $map = [
            self::STATUS_DRAFT => ['class' => 'medsop-badge medsop-badge--neutral', 'label' => 'ฉบับร่าง', 'icon' => 'file-pen-line'],
            self::STATUS_PENDING => ['class' => 'medsop-badge medsop-badge--warning', 'label' => 'รออนุมัติ', 'icon' => 'clock'],
            self::STATUS_PUBLISHED => ['class' => 'medsop-badge medsop-badge--success', 'label' => 'เผยแพร่แล้ว', 'icon' => 'circle-check'],
            self::STATUS_REJECTED => ['class' => 'medsop-badge medsop-badge--danger', 'label' => 'ส่งกลับแก้ไข', 'icon' => 'circle-x'],
            self::STATUS_ARCHIVED => ['class' => 'medsop-badge medsop-badge--neutral', 'label' => 'เลิกใช้งาน', 'icon' => 'archive'],
        ];
        return $map[$status] ?? $map[self::STATUS_DRAFT];
    }

    public function getSteps()
    {
        return $this->hasMany(DocumentStep::class, ['document_id' => 'id'])->orderBy(['step_order' => SORT_ASC]);
    }

    public function getRevisions()
    {
        return $this->hasMany(DocumentRevision::class, ['document_id' => 'id'])->orderBy(['revision_no' => SORT_DESC]);
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(Employees::class, ['id' => 'created_emp_id']);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function beforeSave($insert)
    {
        $now = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
