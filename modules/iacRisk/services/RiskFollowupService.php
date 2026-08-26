<?php
namespace app\modules\iacRisk\services;
use app\modules\iacRisk\models\RiskFollowup;
use app\modules\iacRisk\models\RiskRegister;
use Yii;
class RiskFollowupService
{
    public function createSnapshot(int $hospitalId,int $fiscalYearId,int $periodId,int $orgUnitId): int
    {
        if(RiskFollowup::find()->where(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'reporting_period_id'=>$periodId,'org_unit_id'=>$orgUnitId])->exists())return 0;
        (new RiskRegisterService())->syncScope($hospitalId,$fiscalYearId,$orgUnitId);$rows=RiskRegister::find()->where(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'org_unit_id'=>$orgUnitId,'status'=>RiskRegister::STATUS_ACTIVE])->andWhere(['not',['improvement_plan'=>[null,'']]])->orderBy(['id'=>SORT_ASC])->all();if(!$rows)throw new \DomainException('ไม่พบรายการ ปค.5 ที่มีแผนปรับปรุงของหน่วยงานนี้');
        $tx=Yii::$app->db->beginTransaction();try{$now=date('Y-m-d H:i:s');$uid=(int)Yii::$app->user->id;$seq=10;foreach($rows as $row){$item=new RiskFollowup(['ref'=>Yii::$app->security->generateRandomString(24),'hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'reporting_period_id'=>$periodId,'org_unit_id'=>$orgUnitId,'risk_register_id'=>$row->id,'sequence'=>$seq,'mission_objective'=>$row->mission_objective,'existing_control'=>$row->existing_control,'residual_risk'=>$row->residual_risk,'improvement_plan'=>$row->improvement_plan,'responsible_person'=>$row->responsible_person,'status_code'=>RiskFollowup::NOT_STARTED,'created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid]);$item->save(false);$seq+=10;}$tx->commit();return count($rows);}catch(\Throwable $e){$tx->rollBack();throw $e;}
    }
}
