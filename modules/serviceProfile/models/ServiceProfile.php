<?php

namespace app\modules\serviceProfile\models;

use Yii;
use app\modules\filemanager\components\FileManagerHelper;
use yii\db\ActiveRecord;

class ServiceProfile extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_REVIEW_PENDING = 'review_pending';
    public const STATUS_APPROVAL_PENDING = 'approval_pending';
    public const STATUS_ACKNOWLEDGEMENT_PENDING = 'acknowledgement_pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_RETIRED = 'retired';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName()
    {
        return '{{%service_profile}}';
    }

    public function rules()
    {
        return [
            [['owner_type', 'owner_id', 'owner_name_snapshot', 'fiscal_year', 'revision_no'], 'required'],
            [['owner_id', 'fiscal_year', 'revision_no', 'template_id', 'template_revision_snapshot', 'supersedes_id', 'published_by', 'created_by', 'updated_by'], 'integer'],
            [['effective_from', 'effective_to', 'submitted_at', 'published_at', 'created_at', 'updated_at'], 'safe'],
            [['owner_type', 'status'], 'string', 'max' => 30],
            [['owner_name_snapshot'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง', self::STATUS_REVIEW_PENDING => 'รอผู้แทนคุณภาพเห็นชอบ',
            self::STATUS_APPROVAL_PENDING => 'รอผู้อำนวยการอนุมัติ', self::STATUS_ACKNOWLEDGEMENT_PENDING => 'รอหัวหน้าหน่วยงานรับทราบ',
            self::STATUS_ACTIVE => 'ฉบับปัจจุบัน', self::STATUS_RETURNED => 'ส่งกลับแก้ไข',
            self::STATUS_RETIRED => 'สิ้นสุดแล้ว', self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public function getSections() { return $this->hasMany(ServiceProfileSection::class, ['service_profile_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]); }
    public function getAuthors() { return $this->hasMany(ServiceProfileAuthor::class, ['service_profile_id' => 'id'])->with('employee'); }
    public function getApprovals() { return $this->hasMany(ServiceProfileApproval::class, ['service_profile_id' => 'id'])->with('employee'); }
    public function getReviews() { return $this->hasMany(ServiceProfileReview::class, ['service_profile_id' => 'id'])->with('reviewer'); }
    public function getSectionComments() { return $this->hasMany(ServiceProfileSectionComment::class, ['service_profile_id'=>'id'])->with(['section','reviewer'])->orderBy(['created_at'=>SORT_DESC,'id'=>SORT_DESC]); }
    public function getActivities() { return $this->hasMany(ServiceProfileActivity::class, ['service_profile_id' => 'id'])->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]); }
    public function getTemplate() { return $this->hasOne(ServiceProfileTemplate::class, ['id' => 'template_id']); }
    public function getSupersedes() { return $this->hasOne(self::class, ['id' => 'supersedes_id']); }

    public function fileUpload(bool $viewOnly = false): string
    {
        return FileManagerHelper::FileUpload($this->ref, 'service-profile', $viewOnly);
    }

    public static function findCurrent(string $ownerType, int $ownerId): ?self
    {
        return self::find()->where(['owner_type' => $ownerType, 'owner_id' => $ownerId, 'status' => self::STATUS_ACTIVE])
            ->orderBy(['effective_from' => SORT_DESC, 'fiscal_year' => SORT_DESC, 'revision_no' => SORT_DESC])->one();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;
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
