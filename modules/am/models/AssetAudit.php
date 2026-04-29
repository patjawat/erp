<?php

namespace app\modules\am\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Organization;

/**
 * Annual asset audit header.
 *
 * @property int $id
 * @property string $audit_no
 * @property int $seq_no
 * @property int $fiscal_year
 * @property int|null $department
 * @property string|null $audit_date
 * @property string|null $auditors
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

    public static function tableName()
    {
        return '{{%asset_audits}}';
    }

    public function rules()
    {
        return [
            [['audit_no', 'fiscal_year'], 'required'],
            [['seq_no', 'fiscal_year', 'department', 'created_by', 'updated_by'], 'integer'],
            [['audit_date', 'auditors', 'summary_note', 'created_at', 'updated_at'], 'safe'],
            [['auditors', 'summary_note'], 'string'],
            [['audit_no'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['audit_no'], 'unique'],
            [['fiscal_year', 'seq_no'], 'unique', 'targetAttribute' => ['fiscal_year', 'seq_no']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_no' => 'เลขที่ตรวจนับ',
            'seq_no' => 'ลำดับ',
            'fiscal_year' => 'ปีงบประมาณ',
            'department' => 'หน่วยงาน',
            'audit_date' => 'วันที่ตรวจนับ',
            'auditors' => 'ผู้ตรวจนับ',
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
}
