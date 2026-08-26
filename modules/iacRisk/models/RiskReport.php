<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class RiskReport extends ActiveRecord
{
    public const STATUS_DRAFT='draft';public const STATUS_SUBMITTED='submitted';public const STATUS_APPROVED='approved';public const STATUS_RETURNED='returned';
    public static function tableName(): string{return '{{%iac_risk_report}}';}
    public static function statusLabels(): array{return [self::STATUS_DRAFT=>'ฉบับร่าง',self::STATUS_SUBMITTED=>'รอหัวหน้าหน่วยงานรับรอง',self::STATUS_APPROVED=>'รับรองแล้ว',self::STATUS_RETURNED=>'ส่งกลับแก้ไข'];}
    public function getItems(){return $this->hasMany(RiskReportItem::class,['risk_report_id'=>'id'])->orderBy(['sequence'=>SORT_ASC]);}
    public function getOrgUnit(){return $this->hasOne(\app\modules\settings\models\OrgUnit::class,['id'=>'org_unit_id']);}
    public function getPeriod(){return $this->hasOne(ReportingPeriod::class,['id'=>'reporting_period_id']);}
}
