<?php

namespace app\modules\iacRisk\models;

use app\modules\serviceProfile\models\ServiceProfile;
use yii\db\ActiveRecord;

class ServiceProcessVersion extends ActiveRecord
{
    public const REVIEW_PENDING = 'pending';
    public const REVIEW_RETAINED = 'retained';
    public const REVIEW_MODIFIED = 'modified';
    public const REVIEW_NEW = 'new';
    public const REVIEW_RETIRED = 'retired';

    public static function tableName(): string { return '{{%iac_service_process_version}}'; }
    public static function reviewLabels(): array
    {
        return [self::REVIEW_PENDING => 'รอทบทวน', self::REVIEW_RETAINED => 'ใช้ต่อโดยไม่เปลี่ยนแปลง', self::REVIEW_MODIFIED => 'ปรับปรุงแล้ว', self::REVIEW_NEW => 'เพิ่มใหม่', self::REVIEW_RETIRED => 'ยกเลิกใช้'];
    }
    public function rules(): array
    {
        return [
            [['process_id', 'service_profile_id', 'service_profile_section_id', 'fiscal_year', 'revision_no', 'sequence', 'name'], 'required'],
            [['process_id', 'service_profile_id', 'service_profile_section_id', 'fiscal_year', 'revision_no', 'sequence', 'reviewed_by'], 'integer'],
            [['objective', 'review_note'], 'string'], [['reviewed_at', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 500], [['source_item_ref', 'ref'], 'string', 'max' => 64],
            [['review_status'], 'in', 'range' => array_keys(self::reviewLabels())],
        ];
    }
    public function getProcess() { return $this->hasOne(ServiceProcess::class, ['id' => 'process_id']); }
    public function getProfile() { return $this->hasOne(ServiceProfile::class, ['id' => 'service_profile_id']); }
}
