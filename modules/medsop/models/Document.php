<?php

namespace app\modules\medsop\models;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use Yii;
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

    /**
     * URL รูปปกจากระบบ filemanager (cover_image เก็บ uploads.id)
     * คืน null เมื่อไม่มีรูปปก เพื่อให้ผู้เรียก fallback ไปใช้รูปจากขั้นตอนได้
     */
    public function getCoverUrl(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        return FileManagerHelper::getImg((int) $this->cover_image);
    }

    public function rules()
    {
        return [
            [['document_no', 'title', 'document_type', 'organization_id', 'category', 'keywords', 'announcement_status', 'objective'], 'required'],
            [['organization_id', 'current_revision', 'created_emp_id', 'created_by', 'updated_by', 'published_by', 'submitted_by', 'deleted_by'], 'integer'],
            [['objective', 'scope', 'keywords', 'related_links'], 'string'],
            [['review_note'], 'string', 'max' => 500],
            [['published_at', 'submitted_at', 'review_date', 'deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['document_no'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
            [['category'], 'string', 'max' => 100],
            [['announcement_status'], 'string', 'max' => 50],
            [['cover_image'], 'string', 'max' => 500],
            [['ref'], 'string', 'max' => 50],
            [['document_type'], 'in', 'range' => array_keys(MedSopSetting::documentTypes())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['category'], 'validateCategory'],
            [['announcement_status'], 'in', 'range' => array_keys(MedSopSetting::listValue(MedSopSetting::ANNOUNCEMENT_STATUSES, ['ACTIVE' => 'ประกาศใช้']))],
            [['related_links'], 'validateRelatedLinks'],
            [['related_links'], 'validateWiHasParentSop'],
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
        return MedSopSetting::documentTypes();
    }

    public function validateCategory($attribute): void
    {
        $categories = MedSopSetting::listValue(MedSopSetting::DOCUMENT_CATEGORIES, ['SOP', 'WI']);
        $setting = $this->organization_id ? OrganizationSetting::findOne((int) $this->organization_id) : null;
        if ($setting && $setting->document_categories) {
            $organizationCategories = json_decode((string) $setting->document_categories, true);
            if (is_array($organizationCategories) && $organizationCategories !== []) {
                $categories = $organizationCategories;
            }
        }
        if (!in_array((string) $this->$attribute, $categories, true)) {
            $this->addError($attribute, 'หมวดหมู่เอกสารไม่ตรงกับหน่วยงานที่เลือก');
        }
    }

    public static function getStatusBadgeConfigFor(string $status): array
    {
        $map = [
            self::STATUS_DRAFT => ['class' => 'badge text-bg-secondary rounded-pill', 'label' => 'ฉบับร่าง', 'icon' => 'file-pen-line'],
            self::STATUS_PENDING => ['class' => 'badge text-bg-warning rounded-pill', 'label' => 'รออนุมัติ', 'icon' => 'clock'],
            self::STATUS_PUBLISHED => ['class' => 'badge text-bg-success rounded-pill', 'label' => 'เผยแพร่แล้ว', 'icon' => 'circle-check'],
            self::STATUS_REJECTED => ['class' => 'badge text-bg-danger rounded-pill', 'label' => 'ส่งกลับแก้ไข', 'icon' => 'circle-x'],
            self::STATUS_ARCHIVED => ['class' => 'badge text-bg-secondary rounded-pill', 'label' => 'เลิกใช้งาน', 'icon' => 'archive'],
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

    public function getRelatedLinkItems(): array
    {
        $links = json_decode((string) $this->related_links, true);
        return is_array($links) ? $links : [];
    }

    public function validateRelatedLinks($attribute): void
    {
        foreach ($this->getRelatedLinkItems() as $link) {
            $url = (string) ($link['url'] ?? '');
            if ((int) ($link['document_id'] ?? 0) > 0) {
                continue;
            }
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
                $this->addError($attribute, 'ลิงก์เอกสารที่เกี่ยวข้องต้องเป็น URL แบบ http หรือ https ที่ถูกต้อง');
                return;
            }
        }
    }

    public function validateWiHasParentSop($attribute): void
    {
        if ($this->document_type !== self::TYPE_WI || $this->hasErrors($attribute)) {
            return;
        }

        $documentIds = [];
        foreach ($this->getRelatedLinkItems() as $link) {
            $documentId = (int) ($link['document_id'] ?? 0);
            if ($documentId > 0 && $documentId !== (int) $this->id) {
                $documentIds[$documentId] = $documentId;
            }
        }

        $hasParentSop = $documentIds !== [] && self::find()
            ->where([
                'id' => array_values($documentIds),
                'document_type' => self::TYPE_SOP,
                'deleted_at' => null,
            ])
            ->exists();

        if (!$hasParentSop) {
            $this->addError($attribute, 'เอกสาร WI ต้องเลือก SOP หลักที่สร้างไว้แล้วอย่างน้อย 1 ฉบับ');
        }
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
            // token ต่อ record สำหรับเก็บไฟล์ในระบบ filemanager (fileupload/<ref>/)
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
