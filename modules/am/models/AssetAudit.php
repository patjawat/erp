<?php

namespace app\modules\am\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Organization;
use app\modules\hr\models\Employees;

/**
 * Annual asset audit header.
 *
 * @property int $id
 * @property string $audit_no
 * @property int $seq_no
 * @property int $fiscal_year
 * @property int|null $department
 * @property string|null $emp_id
 * @property string|null $audit_date
 * @property string|null $summary_note
 * @property string $status draft|active|closed
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class AssetAudit extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    private ?array $_progressSummary = null;

    public static function tableName()
    {
        return '{{%asset_audits}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'emp_id'], 'required'],
            [['seq_no', 'fiscal_year', 'department', 'created_by', 'updated_by'], 'integer'],
            [['emp_id'], 'string', 'max' => 255],
            [['audit_date', 'summary_note', 'created_at', 'updated_at'], 'safe'],
            [['summary_note'], 'string'],
            [['audit_no'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['audit_no'], 'unique'],
            [['fiscal_year', 'seq_no'], 'unique', 'targetAttribute' => ['fiscal_year', 'seq_no']],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if (($this->audit_no === null || $this->audit_no === '') && $this->fiscal_year) {
            $this->audit_no = 'TEMP';
        }

        return true;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_no' => 'เลขที่ตรวจนับ',
            'seq_no' => 'ลำดับ',
            'fiscal_year' => 'ปีงบประมาณ',
            'department' => 'หน่วยงาน',
            'emp_id' => 'ผู้ตรวจนับ',
            'audit_date' => 'วันที่ตรวจนับ',
            'summary_note' => 'หมายเหตุรวม',
            'status' => 'สถานะ',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
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

    public function getAuditItems()
    {
        return $this->hasMany(AssetAuditItem::class, ['audit_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getDepartmentRef()
    {
        return $this->hasOne(Organization::class, ['id' => 'department']);
    }

    public function getAuditorEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getAuditorLabel()
    {
        if ($this->auditorEmp !== null) {
            return $this->auditorEmp->fullname ?? trim(($this->auditorEmp->fname ?? '') . ' ' . ($this->auditorEmp->lname ?? '')) ?: (string) $this->emp_id;
        }
        return $this->emp_id ?: '-';
    }

    public static function statusList()
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_CLOSED => 'ปิดแล้ว',
        ];
    }

    public function getStatusLabel()
    {
        $list = self::statusList();
        return $list[$this->status] ?? $this->status;
    }

    /**
     * สรุป KPI การตรวจนับ: ตรวจแล้ว / คงเหลือ / เปอร์เซ็นต์ และแยกตามประเภททรัพย์สิน
     */
    public function getProgressSummary(): array
    {
        if ($this->_progressSummary !== null) {
            return $this->_progressSummary;
        }

        $auditedAssetIds = [];
        $auditedItemsByType = [];
        foreach ($this->auditItems as $item) {
            if (!empty($item->asset_id)) {
                $auditedAssetIds[(int) $item->asset_id] = true;
            }

            $asset = $item->asset;
            $typeLabel = trim((string) ($asset && $asset->assetType ? $asset->assetType->title : ($asset ? $asset->AssetTypeName() : '')));
            if ($typeLabel === '' && $asset) {
                $typeLabel = trim((string) ($asset->asset_kind ?? ''));
            }
            if ($typeLabel === '') {
                $typeLabel = 'ไม่ระบุประเภท';
            }
            if (!isset($auditedItemsByType[$typeLabel])) {
                $auditedItemsByType[$typeLabel] = [
                    'type' => $typeLabel,
                    'total' => 0,
                    'checked' => 0,
                    'remaining' => 0,
                    'percent' => 0.0,
                ];
            }
            $auditedItemsByType[$typeLabel]['checked']++;
        }

        if (empty($this->department)) {
            $types = array_values($auditedItemsByType);
            usort($types, static function (array $a, array $b): int {
                return $b['checked'] <=> $a['checked'] ?: strcmp($a['type'], $b['type']);
            });

            $this->_progressSummary = [
                'scopeResolved' => false,
                'total' => 0,
                'checked' => 0,
                'remaining' => 0,
                'percent' => 0.0,
                'types' => $types,
            ];

            return $this->_progressSummary;
        }

        $scopeQuery = Asset::find()
            ->alias('a')
            ->with(['assetType'])
            ->where(['a.deleted_at' => null])
            ->andWhere(['a.department' => $this->department]);

        $scopeAssets = $scopeQuery
            ->orderBy(['a.asset_type_id' => SORT_ASC, 'a.code' => SORT_ASC, 'a.id' => SORT_ASC])
            ->all();

        $total = count($scopeAssets);
        $checked = 0;
        $types = [];

        foreach ($scopeAssets as $asset) {
            $typeLabel = trim((string) ($asset->assetType->title ?? $asset->AssetTypeName() ?? $asset->asset_kind ?? 'ไม่ระบุประเภท'));
            if ($typeLabel === '') {
                $typeLabel = 'ไม่ระบุประเภท';
            }

            if (!isset($types[$typeLabel])) {
                $types[$typeLabel] = [
                    'type' => $typeLabel,
                    'total' => 0,
                    'checked' => 0,
                    'remaining' => 0,
                    'percent' => 0.0,
                ];
            }

            $types[$typeLabel]['total']++;
            if (isset($auditedAssetIds[(int) $asset->id])) {
                $checked++;
                $types[$typeLabel]['checked']++;
            } else {
                $types[$typeLabel]['remaining']++;
            }
        }

        foreach ($types as &$row) {
            $row['percent'] = $row['total'] > 0 ? round(($row['checked'] / $row['total']) * 100, 1) : 0.0;
        }
        unset($row);

        usort($types, static function (array $a, array $b): int {
            return $b['total'] <=> $a['total'] ?: strcmp($a['type'], $b['type']);
        });

        $this->_progressSummary = [
            'scopeResolved' => !empty($this->department),
            'total' => $total,
            'checked' => $checked,
            'remaining' => max(0, $total - $checked),
            'percent' => $total > 0 ? round(($checked / $total) * 100, 1) : 0.0,
            'types' => $types,
        ];

        return $this->_progressSummary;
    }
}
