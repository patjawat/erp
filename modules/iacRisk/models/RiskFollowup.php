<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class RiskFollowup extends ActiveRecord
{
    public const DONE_ON_TIME='done_on_time';public const DONE_LATE='done_late';public const NOT_STARTED='not_started';public const IN_PROGRESS='in_progress';
    public static function tableName(): string{return '{{%iac_risk_followup}}';}
    public static function statusLabels(): array{return [self::DONE_ON_TIME=>'☆ ดำเนินการแล้ว เสร็จตามกำหนด',self::DONE_LATE=>'/ ดำเนินการแล้ว เสร็จล่าช้ากว่ากำหนด',self::NOT_STARTED=>'X ยังไม่ดำเนินการ',self::IN_PROGRESS=>'O อยู่ระหว่างดำเนินการ'];}
    public static function statusSymbols(): array{return [self::DONE_ON_TIME=>'☆',self::DONE_LATE=>'/',self::NOT_STARTED=>'X',self::IN_PROGRESS=>'O'];}
    public function rules(): array{return [[['hospital_id','fiscal_year_id','reporting_period_id','org_unit_id','sequence','improvement_plan','status_code'],'required'],[['hospital_id','fiscal_year_id','reporting_period_id','org_unit_id','risk_register_id','sequence','created_by','updated_by'],'integer'],[['mission_objective','existing_control','residual_risk','improvement_plan','responsible_person','followup_method','result_summary','comment'],'string'],[['status_code'],'in','range'=>array_keys(self::statusLabels())],[['created_at','updated_at'],'safe']];}
    public function getOrgUnit(){return $this->hasOne(\app\modules\settings\models\OrgUnit::class,['id'=>'org_unit_id']);}
    public function getPeriod(){return $this->hasOne(ReportingPeriod::class,['id'=>'reporting_period_id']);}
}
