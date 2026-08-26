<?php

namespace app\modules\iacRisk\models;

use yii\db\ActiveRecord;

class RiskRegister extends ActiveRecord
{
    public const SOURCE_CSA = 'csa';
    public const SOURCE_MANUAL = 'manual';
    public const STATUS_ACTIVE = 'active';

    public static function tableName(): string { return '{{%iac_risk_register}}'; }

    public function rules(): array
    {
        return [
            [['hospital_id','fiscal_year_id','fiscal_year','org_unit_id','source_type','risk_name','status'],'required'],
            [['hospital_id','fiscal_year_id','fiscal_year','org_unit_id','csa_id','csa_risk_id','likelihood_score','impact_score','created_by','updated_by'],'integer'],
            [['cause','impact','residual_risk','mission_objective','existing_control','improvement_plan','responsible_person'],'string'],
            [['created_at','updated_at'],'safe'],
            [['risk_name'],'string','max'=>500],
            [['source_type'],'in','range'=>[self::SOURCE_CSA,self::SOURCE_MANUAL]],
            [['likelihood_score','impact_score'],'integer','min'=>1,'max'=>5,'skipOnEmpty'=>true],
            [['adequacy','status'],'string','max'=>30],
        ];
    }

    public function getCsa() { return $this->hasOne(Csa::class,['id'=>'csa_id']); }
    public function getCsaRisk() { return $this->hasOne(CsaRisk::class,['id'=>'csa_risk_id']); }
    public function getOrgUnit() { return $this->hasOne(\app\modules\settings\models\OrgUnit::class,['id'=>'org_unit_id']); }
    public function isFromCsa(): bool { return $this->source_type===self::SOURCE_CSA; }
}
