<?php

namespace app\modules\iacRisk\models;

use yii\db\ActiveRecord;

class Csa extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft'; public const STATUS_AUTHOR_CONFIRMED = 'author_confirmed';
    public const STATUS_HEAD_PENDING = 'head_pending'; public const STATUS_HEAD_APPROVED = 'head_approved';
    public const STATUS_RETURNED = 'returned'; public const STATUS_COORDINATOR_REVISED = 'coordinator_revised';
    public static function tableName(): string { return '{{%iac_csa}}'; }
    public static function statusLabels(): array { return [self::STATUS_DRAFT=>'ฉบับร่าง',self::STATUS_AUTHOR_CONFIRMED=>'ผู้จัดทำยืนยันแล้ว',self::STATUS_HEAD_PENDING=>'รอหัวหน้าหน่วยงาน',self::STATUS_HEAD_APPROVED=>'หัวหน้ารับรองแล้ว',self::STATUS_RETURNED=>'ส่งกลับแก้ไข',self::STATUS_COORDINATOR_REVISED=>'ทีมประสานแก้ไขแล้ว']; }
    public function getProcessVersion() { return $this->hasOne(ServiceProcessVersion::class, ['id'=>'process_version_id']); }
    public function getSteps() { return $this->hasMany(CsaStep::class, ['csa_id'=>'id'])->orderBy(['sequence'=>SORT_ASC]); }
}
