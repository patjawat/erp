<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Immutable source snapshot waiting for finance review.
 *
 * @property int $id
 * @property string $ref
 * @property string $source_system
 * @property string $source_type
 * @property string $source_id
 * @property int $source_version
 * @property string|null $source_document_no
 * @property int|null $vendor_id
 * @property string|null $vendor_code_snapshot
 * @property string|null $vendor_name_snapshot
 * @property string|null $document_date
 * @property string|null $amount
 * @property string $status
 * @property array $payload_json
 * @property array|null $validation_json
 * @property string $received_at
 * @property string|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class FinanceInbox extends ActiveRecord
{
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_NEEDS_INFORMATION = 'needs_information';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return '{{%finance_inbox}}';
    }

    public function rules()
    {
        return [
            [['source_system', 'source_type', 'source_id', 'source_version', 'status', 'payload_json', 'received_at'], 'required'],
            [['source_version', 'vendor_id', 'reviewed_by', 'created_by', 'updated_by'], 'integer'],
            [['source_version'], 'integer', 'min' => 1],
            [['document_date'], 'date', 'format' => 'php:Y-m-d'],
            [['received_at', 'reviewed_at', 'created_at', 'updated_at'], 'safe'],
            [['amount'], 'number', 'min' => 0],
            [['payload_json', 'validation_json'], 'safe'],
            [['source_system', 'source_type'], 'string', 'max' => 50],
            [['source_id', 'source_document_no', 'vendor_code_snapshot'], 'string', 'max' => 100],
            [['vendor_name_snapshot'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_PENDING_REVIEW],
            [['source_version'], 'default', 'value' => 1],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->received_at = $this->received_at ?: $now;
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;

        return true;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING_REVIEW => 'รอตรวจสอบ',
            self::STATUS_NEEDS_INFORMATION => 'รอข้อมูลเพิ่มเติม',
            self::STATUS_ACCEPTED => 'รับรองแล้ว',
            self::STATUS_REJECTED => 'ไม่รับรายการ',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return [
            self::STATUS_PENDING_REVIEW => 'bg-warning-subtle text-warning-emphasis',
            self::STATUS_NEEDS_INFORMATION => 'bg-info-subtle text-info-emphasis',
            self::STATUS_ACCEPTED => 'bg-success-subtle text-success-emphasis',
            self::STATUS_REJECTED => 'bg-danger-subtle text-danger-emphasis',
        ][$status] ?? 'bg-secondary-subtle text-secondary-emphasis';
    }

    public function validationMessages(): array
    {
        $value = $this->validation_json;
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        return is_array($value) ? array_values($value) : [];
    }

    public function getReviews()
    {
        return $this->hasMany(FinanceInboxReview::class, ['finance_inbox_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getPayable()
    {
        return $this->hasOne(FinancePayable::class, ['finance_inbox_id' => 'id']);
    }
}
