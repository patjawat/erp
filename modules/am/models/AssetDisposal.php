<?php

namespace app\modules\am\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/**
 * Asset disposal request header.
 */
class AssetDisposal extends \yii\db\ActiveRecord
{
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_DONE = 'done';

    public static function tableName()
    {
        return '{{%asset_disposals}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'responsible_emp_id'], 'required'],
            [['seq_no', 'fiscal_year', 'department', 'responsible_emp_id', 'created_by', 'updated_by'], 'integer'],
            [['disposal_date', 'summary_note', 'created_at', 'updated_at'], 'safe'],
            [['summary_note'], 'string'],
            [['disposal_no'], 'string', 'max' => 255],
            [['disposal_method'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => [self::STATUS_PENDING_APPROVAL, self::STATUS_APPROVED, self::STATUS_DONE]],
            [['status'], 'default', 'value' => self::STATUS_PENDING_APPROVAL],
            [['disposal_no'], 'unique'],
            [['fiscal_year', 'seq_no'], 'unique', 'targetAttribute' => ['fiscal_year', 'seq_no']],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if (($this->disposal_no === null || $this->disposal_no === '') && $this->fiscal_year) {
            $this->disposal_no = 'TEMP';
        }

        return true;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'disposal_no' => 'เลขที่จำหน่าย',
            'seq_no' => 'ลำดับ',
            'fiscal_year' => 'ปีงบประมาณ',
            'department' => 'หน่วยงาน',
            'disposal_date' => 'วันที่',
            'disposal_method' => 'วิธีจำหน่าย',
            'responsible_emp_id' => 'ผู้รับผิดชอบ',
            'summary_note' => 'หมายเหตุ',
            'status' => 'สถานะ',
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

    public function getDisposalItems()
    {
        return $this->hasMany(AssetDisposalItem::class, ['disposal_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getDepartmentRef()
    {
        return $this->hasOne(Organization::class, ['id' => 'department']);
    }

    public function getResponsibleEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'responsible_emp_id']);
    }

    public function getResponsibleLabel()
    {
        if ($this->responsibleEmp !== null) {
            return $this->responsibleEmp->fullname ?? trim(($this->responsibleEmp->fname ?? '') . ' ' . ($this->responsibleEmp->lname ?? '')) ?: (string) $this->responsible_emp_id;
        }
        return $this->responsible_emp_id ?: '-';
    }

    public static function statusList()
    {
        return [
            self::STATUS_PENDING_APPROVAL => 'รออนุมัติ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_DONE => 'ดำเนินการแล้ว',
        ];
    }

    public function getStatusLabel()
    {
        $list = self::statusList();
        return $list[$this->status] ?? $this->status;
    }
}
