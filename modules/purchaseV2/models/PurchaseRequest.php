<?php

namespace app\modules\purchaseV2\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

class PurchaseRequest extends \yii\db\ActiveRecord
{
    public const STATUS_DRAFT = 0;
    public const STATUS_PENDING_APPROVAL = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_ORDERED = 3;
    public const STATUS_RECEIVED = 4;
    public const STATUS_STOCKED = 5;
    public const STATUS_COMPLETED = 6;
    public const STATUS_CANCELLED = 7;

    public const TYPE_PLANNED = 'planned';
    public const TYPE_UNPLANNED = 'unplanned';

    public const VAT_NONE = 'NONE';
    public const VAT_IN = 'IN';
    public const VAT_EX = 'EX';

    public $q;
    public $date_start;
    public $date_end;
    public $save_action;

    public static function tableName()
    {
        return 'purchase_request';
    }

    public function rules()
    {
        return [
            [['request_no', 'request_date', 'request_title', 'requester_emp_id', 'department_id'], 'required'],
            [['request_type'], 'default', 'value' => self::TYPE_PLANNED],
            [['vat_type'], 'default', 'value' => self::VAT_NONE],
            [['requester_emp_id', 'department_id', 'budget_year', 'status', 'current_approval_level', 'legacy_order_id', 'legacy_status', 'migrated_by', 'created_by', 'updated_by'], 'integer'],
            [['budget_amount', 'subtotal_amount', 'discount_amount', 'vat_amount', 'grand_total'], 'number'],
            [['summary', 'data_json', 'submitted_at', 'approved_at', 'ordered_at', 'received_at', 'stocked_at', 'completed_at', 'cancelled_at', 'migrated_at', 'created_at', 'updated_at', 'date_start', 'date_end', 'q', 'save_action', 'vendor_name', 'vendor_id', 'legacy_ref', 'migrated_from', 'request_no', 'pr_number', 'pq_number', 'po_number', 'gr_number', 'budget_type_code'], 'safe'],
            [['ref', 'request_title'], 'string', 'max' => 255],
            [['request_type'], 'in', 'range' => array_keys(self::requestTypeOptions())],
            [['vat_type'], 'in', 'range' => array_keys(self::vatTypeOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['ref', 'request_no'], 'string', 'max' => 255],
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

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'request_no' => 'เลขที่คำขอ',
            'request_date' => 'วันที่คำขอ',
            'request_type' => 'ประเภทจัดซื้อ',
            'request_title' => 'เรื่อง',
            'summary' => 'รายละเอียด/ความจำเป็น',
            'requester_emp_id' => 'ผู้ขอ',
            'department_id' => 'หน่วยงาน',
            'budget_year' => 'ปีงบประมาณ',
            'budget_type_code' => 'ประเภทงบประมาณ',
            'budget_amount' => 'วงเงินงบประมาณ',
            'subtotal_amount' => 'ยอดก่อนส่วนลด',
            'discount_amount' => 'ส่วนลด',
            'vat_type' => 'VAT',
            'vat_amount' => 'VAT',
            'grand_total' => 'ยอดรวมสุทธิ',
            'vendor_id' => 'ผู้ขาย/ผู้รับจ้าง',
            'vendor_name' => 'ชื่อผู้ขาย/ผู้รับจ้าง',
            'status' => 'สถานะ',
            'current_approval_level' => 'ระดับอนุมัติ',
            'pr_number' => 'เลขที่ขอซื้อ',
            'pq_number' => 'เลขทะเบียนคุม',
            'po_number' => 'เลขที่สั่งซื้อ',
            'gr_number' => 'เลขที่ตรวจรับ',
            'submitted_at' => 'เวลาส่งอนุมัติ',
            'approved_at' => 'เวลาอนุมัติ',
            'ordered_at' => 'เวลาออกใบสั่งซื้อ',
            'received_at' => 'เวลาได้รับของ',
            'stocked_at' => 'เวลาเข้าคลัง',
            'completed_at' => 'เวลาปิดงาน',
            'cancelled_at' => 'เวลายกเลิก',
            'legacy_order_id' => 'Legacy Order ID',
            'legacy_ref' => 'Legacy Ref',
            'legacy_status' => 'Legacy Status',
            'migrated_from' => 'Migrated From',
            'migrated_at' => 'Migrated At',
            'migrated_by' => 'Migrated By',
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

        if (!empty($this->request_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->request_date)) {
            $this->request_date = AppHelper::convertToThai($this->request_date);
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (is_array($this->data_json)) {
            $this->data_json = json_encode($this->data_json, JSON_UNESCAPED_UNICODE);
        }

        $this->request_date = $this->normalizeDateForDb($this->request_date);

        if (empty($this->ref)) {
            $this->ref = static::generateRef();
        }

        if (empty($this->request_no)) {
            $this->request_no = static::generateRequestNo();
        }

        if (empty($this->budget_year)) {
            $this->budget_year = AppHelper::YearBudget($this->request_date ?: null);
        }

        if (trim((string) $this->vendor_id) !== '') {
            $vendorTitle = trim((string) ($this->vendor?->title ?? ''));
            if ($vendorTitle !== '') {
                $this->vendor_name = $vendorTitle;
            }
        }

        return true;
    }

    protected function normalizeDateForDb($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return AppHelper::convertToGregorian($value) ?: $value;
        }

        return $value;
    }

    public static function generateRef(): string
    {
        do {
            $ref = 'PRV2-' . date('YmdHis') . '-' . random_int(1000, 9999);
        } while (static::find()->where(['ref' => $ref])->exists());

        return $ref;
    }

    public static function generateRequestNo(): string
    {
        do {
            $requestNo = 'PRV2-' . date('Ymd') . '-' . random_int(1000, 9999);
        } while (static::find()->where(['request_no' => $requestNo])->exists());

        return $requestNo;
    }

    public function getRequester()
    {
        return $this->hasOne(Employees::class, ['id' => 'requester_emp_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Organization::class, ['id' => 'department_id']);
    }

    public function getVendor()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vendor_id'])
            ->andOnCondition(['name' => 'vendor']);
    }

    public function getItems()
    {
        return $this->hasMany(PurchaseRequestItem::class, ['request_id' => 'id'])->orderBy(['line_no' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getApprovals()
    {
        return $this->hasMany(PurchaseRequestApproval::class, ['request_id' => 'id'])->orderBy(['step_no' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getLogs()
    {
        return $this->hasMany(PurchaseRequestLog::class, ['request_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function requestTypeLabel(): string
    {
        return self::requestTypeOptions()[$this->request_type] ?? 'ไม่ระบุ';
    }

    public function vatTypeLabel(): string
    {
        return self::vatTypeOptions()[$this->vat_type] ?? 'ไม่ระบุ';
    }

    public function budgetTypeLabel(): string
    {
        try {
            return Categorise::findOne(['name' => 'budget_type', 'code' => $this->budget_type_code])?->title ?? '-';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    public function statusMeta(?int $status = null): array
    {
        $status = $status ?? (int) $this->status;
        $meta = self::statusOptions()[$status] ?? [
            'label' => 'ไม่ระบุ',
            'color' => 'secondary',
            'icon' => 'circle-question',
            'progress' => 0,
        ];

        return $meta;
    }

    public function viewStatus(): array
    {
        $meta = $this->statusMeta();

        return [
            'status_name' => $meta['label'],
            'color' => $meta['color'],
            'progress' => $meta['progress'],
            'icon' => $meta['icon'],
        ];
    }

    public function statusBadge(): string
    {
        $meta = $this->statusMeta();
        return Html::tag(
            'span',
            '<i data-lucide="' . Html::encode($meta['icon']) . '" class="me-1"></i>' . Html::encode($meta['label']),
            [
                'class' => 'badge rounded-pill bg-' . $meta['color'] . ' bg-opacity-10 text-' . $meta['color'] . ' border border-' . $meta['color'] . '-subtle fw-semibold px-3 py-2',
            ]
        );
    }

    public function getCurrentApproval()
    {
        return PurchaseRequestApproval::find()
            ->where(['request_id' => $this->id, 'status' => PurchaseRequestApproval::pendingStatusValues()])
            ->orderBy(['step_no' => SORT_ASC, 'id' => SORT_ASC])
            ->one();
    }

    public function requesterEmployee(): ?Employees
    {
        if (!empty($this->requester_emp_id)) {
            $employee = Employees::findOne($this->requester_emp_id);
            if ($employee) {
                return $employee;
            }
        }

        if (!empty($this->created_by)) {
            return Employees::find()->where(['user_id' => $this->created_by])->one();
        }

        return null;
    }

    public function requesterSummary(): array
    {
        $employee = $this->requesterEmployee();
        if (!$employee) {
            return [
                'avatar' => '',
                'fullname' => '-',
                'department' => '-',
                'position' => '-',
            ];
        }

        return [
            'avatar' => $employee->getAvatar(false, $this->request_no),
            'fullname' => $employee->fullname,
            'department' => $employee->departmentName(),
            'position' => $employee->positionName(),
        ];
    }

    public function departmentSummary(): array
    {
        $department = $this->department;
        if ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
            ];
        }

        return [
            'id' => null,
            'name' => '-',
        ];
    }

    public function vendorLabel(): string
    {
        $vendorTitle = trim((string) ($this->vendor?->title ?? ''));
        if ($vendorTitle !== '') {
            return $vendorTitle;
        }

        $vendorName = trim((string) ($this->vendor_name ?? ''));
        if ($vendorName !== '') {
            return $vendorName;
        }

        $vendorId = trim((string) ($this->vendor_id ?? ''));
        if ($vendorId !== '') {
            return $vendorId;
        }

        return '-';
    }

    public function totalItemsAmount(): float
    {
        $amount = 0.0;
        foreach ($this->items as $item) {
            $amount += (float) $item->amount;
        }
        return $amount;
    }

    public function approvalProgress(): int
    {
        $approvals = $this->approvals;
        $total = 0;
        $done = 0;
        foreach ($approvals as $approval) {
            if ($approval->step_type !== PurchaseRequestApproval::STEP_WORKFLOW) {
                continue;
            }
            $total++;
            if ($approval->status === PurchaseRequestApproval::STATUS_APPROVED) {
                $done++;
            }
        }
        if ($total === 0) {
            return $this->status === self::STATUS_CANCELLED ? 100 : 0;
        }
        return (int) min(100, round(($done / $total) * 100));
    }

    public function budgetUsagePercent(): int
    {
        if (empty($this->budget_amount) || (float) $this->budget_amount <= 0) {
            return 0;
        }

        return (int) min(100, round(((float) $this->grand_total / (float) $this->budget_amount) * 100));
    }

    public function canEdit(): bool
    {
        return in_array((int) $this->status, [self::STATUS_DRAFT, self::STATUS_CANCELLED], true);
    }

    public function canSubmit(): bool
    {
        return (int) $this->status === self::STATUS_DRAFT;
    }

    public function canCancel(): bool
    {
        return (int) $this->status !== self::STATUS_CANCELLED;
    }

    public function getDisplayReference(): string
    {
        return $this->request_no ?: $this->ref;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'แบบร่าง',
                'color' => 'secondary',
                'icon' => 'file-pen-line',
                'progress' => 0,
            ],
            self::STATUS_PENDING_APPROVAL => [
                'label' => 'รออนุมัติ',
                'color' => 'warning',
                'icon' => 'hourglass',
                'progress' => 15,
            ],
            self::STATUS_APPROVED => [
                'label' => 'อนุมัติแล้ว',
                'color' => 'primary',
                'icon' => 'badge-check',
                'progress' => 30,
            ],
            self::STATUS_ORDERED => [
                'label' => 'ออกใบสั่งซื้อ',
                'color' => 'info',
                'icon' => 'file-signature',
                'progress' => 50,
            ],
            self::STATUS_RECEIVED => [
                'label' => 'ตรวจรับแล้ว',
                'color' => 'info',
                'icon' => 'package-check',
                'progress' => 65,
            ],
            self::STATUS_STOCKED => [
                'label' => 'เข้าคลังแล้ว',
                'color' => 'success',
                'icon' => 'warehouse',
                'progress' => 80,
            ],
            self::STATUS_COMPLETED => [
                'label' => 'ปิดงาน',
                'color' => 'success',
                'icon' => 'circle-check-big',
                'progress' => 100,
            ],
            self::STATUS_CANCELLED => [
                'label' => 'ยกเลิก',
                'color' => 'danger',
                'icon' => 'ban',
                'progress' => 100,
            ],
        ];
    }

    public static function requestTypeOptions(): array
    {
        return [
            self::TYPE_PLANNED => 'ในแผน',
            self::TYPE_UNPLANNED => 'นอกแผน',
        ];
    }

    public static function vatTypeOptions(): array
    {
        return [
            self::VAT_NONE => 'ไม่มี VAT',
            self::VAT_IN => 'VAT ใน',
            self::VAT_EX => 'VAT นอก',
        ];
    }

    public static function listRequesters(): array
    {
        return ArrayHelper::map(
            Employees::find()->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->all(),
            'id',
            function ($emp) {
                return trim(($emp->prefix ?? '') . ($emp->fname ?? '') . ' ' . ($emp->lname ?? ''));
            }
        );
    }

    public static function listDepartments(): array
    {
        return ArrayHelper::map(
            Organization::find()->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])->all(),
            'id',
            'name'
        );
    }

    public static function listBudgetTypes(): array
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'budget_type'])->orderBy(['code' => SORT_ASC])->all(),
            'code',
            'title'
        );
    }

    public static function listVendors(): array
    {
        $vendors = Categorise::find()
            ->where(['name' => 'vendor'])
            ->orderBy(['title' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($vendors, 'code', static function ($vendor) {
            $code = trim((string) ($vendor->code ?? ''));
            $title = trim((string) ($vendor->title ?? ''));

            if ($code !== '' && $title !== '') {
                return $code . ' - ' . $title;
            }

            return $title !== '' ? $title : $code;
        });
    }

    public static function calculateTotals(array $items, float $discountAmount = 0.0, string $vatType = self::VAT_NONE): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $qty = isset($item['qty']) ? (float) $item['qty'] : 0;
            $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
            $subtotal += ($qty * $unitPrice);
        }

        $discountAmount = max(0, (float) $discountAmount);
        $base = max(0, $subtotal - $discountAmount);
        $vatAmount = 0.0;
        $grandTotal = $base;

        switch ($vatType) {
            case self::VAT_EX:
                $vatAmount = $base * 0.07;
                $grandTotal = $base + $vatAmount;
                break;
            case self::VAT_IN:
                $vatAmount = $base - ($base / 1.07);
                $grandTotal = $base;
                break;
            case self::VAT_NONE:
            default:
                $vatAmount = 0.0;
                $grandTotal = $base;
                break;
        }

        return [
            'subtotal_amount' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
