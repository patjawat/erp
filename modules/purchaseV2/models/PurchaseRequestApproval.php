<?php

namespace app\modules\purchaseV2\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Employees;

class PurchaseRequestApproval extends \yii\db\ActiveRecord
{
    public const STEP_WORKFLOW = 'workflow';
    public const STEP_COMMITTEE = 'committee';
    public const STEP_COMMITTEE_DETAIL = 'committee_detail';

    public const STATUS_NONE = 'None';
    public const STATUS_WAITING = self::STATUS_NONE;
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Pass';
    public const STATUS_REJECTED = 'Reject';
    public const STATUS_INFO = 'Info';

    public static function tableName()
    {
        return 'purchase_request_approval';
    }

    public function rules()
    {
        return [
            [['request_id', 'step_no', 'approver_emp_id', 'approver_user_id', 'legacy_approve_id', 'created_by', 'updated_by'], 'integer'],
            [['step_type', 'role_name', 'approver_name', 'approver_position', 'status', 'comment', 'action_at', 'data_json', 'created_at', 'updated_at'], 'safe'],
            [['step_type'], 'string', 'max' => 50],
            [['role_name'], 'string', 'max' => 100],
            [['approver_name', 'approver_position'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        if (is_string($this->data_json)) {
            $decoded = json_decode($this->data_json, true);
            $this->data_json = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($this->data_json)) {
            $this->data_json = [];
        }

        $this->status = static::normalizeStatus($this->status);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->status = static::normalizeStatus($this->status);

        if (is_array($this->data_json)) {
            $this->data_json = json_encode($this->data_json, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($this->status) && in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED], true) && empty($this->action_at)) {
            $this->action_at = date('Y-m-d H:i:s');
        }

        return true;
    }

    public function getRequest()
    {
        return $this->hasOne(PurchaseRequest::class, ['id' => 'request_id']);
    }

    public function getApproverEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'approver_emp_id']);
    }

    public function statusMeta(): array
    {
        $status = static::normalizeStatus($this->status);

        return self::statusOptions()[$status] ?? [
            'label' => 'ไม่ระบุ',
            'color' => 'secondary',
            'icon' => 'circle-question',
        ];
    }

    public function statusBadge(): string
    {
        $meta = $this->statusMeta();
        return Html::tag(
            'span',
            '<i data-lucide="' . Html::encode($meta['icon']) . '" class="me-1"></i>' . Html::encode($meta['label']),
            ['class' => 'badge rounded-pill bg-' . $meta['color'] . ' bg-opacity-10 text-' . $meta['color'] . ' border border-' . $meta['color'] . '-subtle fw-semibold px-3 py-2']
        );
    }

    public function viewApproveDate(): ?string
    {
        $stamp = $this->action_at;
        if (empty($stamp) && !empty($this->data_json['approve_date'])) {
            $stamp = $this->data_json['approve_date'];
        }

        if (empty($stamp)) {
            return null;
        }

        try {
            $time = explode(' ', (string) $stamp)[1] ?? '';
            return Yii::$app->thaiFormatter->asDate($stamp, 'medium') . ($time !== '' ? ' ' . $time : '');
        } catch (\Throwable $th) {
            return (string) $stamp;
        }
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NONE => ['label' => 'รอดำเนินการ', 'color' => 'secondary', 'icon' => 'clock-rotate-left'],
            self::STATUS_PENDING => ['label' => 'รออนุมัติ', 'color' => 'warning', 'icon' => 'hourglass'],
            self::STATUS_APPROVED => ['label' => 'อนุมัติ', 'color' => 'success', 'icon' => 'badge-check'],
            self::STATUS_REJECTED => ['label' => 'ไม่อนุมัติ', 'color' => 'danger', 'icon' => 'x-circle'],
            self::STATUS_INFO => ['label' => 'ข้อมูล', 'color' => 'info', 'icon' => 'info'],
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        $status = trim((string) $status);
        if ($status === '') {
            return self::STATUS_NONE;
        }

        return match (strtolower($status)) {
            'none', 'waiting' => self::STATUS_NONE,
            'pending' => self::STATUS_PENDING,
            'pass', 'approved' => self::STATUS_APPROVED,
            'reject', 'rejected' => self::STATUS_REJECTED,
            'info' => self::STATUS_INFO,
            default => $status,
        };
    }

    public static function noneStatusValues(): array
    {
        return [self::STATUS_NONE, 'none', 'waiting'];
    }

    public static function pendingStatusValues(): array
    {
        return [self::STATUS_PENDING, 'pending'];
    }

    public static function approvedStatusValues(): array
    {
        return [self::STATUS_APPROVED, 'pass', 'approved'];
    }

    public static function rejectedStatusValues(): array
    {
        return [self::STATUS_REJECTED, 'reject', 'rejected'];
    }

    public static function infoStatusValues(): array
    {
        return [self::STATUS_INFO, 'info'];
    }
}
