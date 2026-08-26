<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class CsaRisk extends ActiveRecord
{
    public const ADEQUACY_NOT_ASSESSED='not_assessed'; public const ADEQUACY_ADEQUATE='adequate'; public const ADEQUACY_INADEQUATE='inadequate';
    public static function tableName(): string { return '{{%iac_csa_risk}}'; }
    public static function adequacyLabels(): array { return [self::ADEQUACY_NOT_ASSESSED=>'ยังไม่ประเมิน',self::ADEQUACY_ADEQUATE=>'เพียงพอ/ควบคุมได้ดี',self::ADEQUACY_INADEQUATE=>'ไม่เพียงพอ/ต้องปรับปรุง']; }
    public function getControls() { return $this->hasMany(RiskControl::class, ['risk_id'=>'id'])->orderBy(['sequence'=>SORT_ASC]); }
    public function getAssessment() { return $this->hasOne(ControlAssessment::class, ['risk_id'=>'id']); }
    public function getPlans() { return $this->hasMany(ImprovementPlan::class, ['risk_id'=>'id'])->orderBy(['due_date'=>SORT_ASC]); }
}
