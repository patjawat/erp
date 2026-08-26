<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\Csa;
use app\modules\iacRisk\models\RiskRegister;
use Yii;

class RiskRegisterService
{
    public function syncApprovedCsa(Csa $csa): int
    {
        if(!in_array($csa->status,[Csa::STATUS_HEAD_APPROVED,Csa::STATUS_COORDINATOR_REVISED],true))return 0;
        if(!$csa->isRelationPopulated('processVersion'))$csa->populateRelation('processVersion',$csa->getProcessVersion()->one());
        if(!$csa->isRelationPopulated('steps'))$csa->populateRelation('steps',$csa->getSteps()->with(['risks.controls','risks.plans','risks.assessment'])->all());
        $count=0;$now=date('Y-m-d H:i:s');$uid=Yii::$app->user->isGuest?null:(int)Yii::$app->user->id;
        foreach($csa->steps as $step)foreach($step->risks as $risk){
            $row=RiskRegister::findOne(['csa_risk_id'=>$risk->id])?:new RiskRegister(['ref'=>Yii::$app->security->generateRandomString(24),'source_type'=>RiskRegister::SOURCE_CSA,'csa_id'=>$csa->id,'csa_risk_id'=>$risk->id,'created_at'=>$now,'created_by'=>$uid]);
            $row->hospital_id=$csa->hospital_id;$row->fiscal_year_id=$csa->fiscal_year_id;$row->fiscal_year=$csa->fiscal_year;$row->org_unit_id=$csa->org_unit_id;
            $process=$csa->processVersion;$row->mission_objective=trim(($process?->name?:'').($process?->objective?"\nวัตถุประสงค์: ".$process->objective:''))?:null;$row->risk_name=$risk->name;$row->cause=$risk->cause;$row->impact=$risk->impact;$row->likelihood_score=$risk->likelihood_score;$row->impact_score=$risk->impact_score;$row->adequacy=$risk->adequacy;$row->residual_risk=$risk->residual_risk;$row->existing_control=implode("\n",array_filter(array_map(static fn($control)=>trim((string)$control->description),$risk->controls)));$row->improvement_plan=implode("\n",array_filter(array_map(static fn($plan)=>trim((string)$plan->action),$risk->plans)));$row->responsible_person=implode("\n",array_filter(array_map(static fn($plan)=>trim((string)$plan->responsible).($plan->due_date?' ('.$plan->due_date.')':''),$risk->plans)));$row->status=RiskRegister::STATUS_ACTIVE;$row->updated_at=$now;$row->updated_by=$uid;
            if(!$row->save())throw new \RuntimeException(implode(' ',$row->getFirstErrors()));$count++;
        }
        return $count;
    }

    public function syncScope(int $hospitalId,int $fiscalYearId,?int $orgUnitId=null): int
    {
        $query=Csa::find()->with(['processVersion','steps.risks.controls','steps.risks.plans','steps.risks.assessment'])->where(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'status'=>[Csa::STATUS_HEAD_APPROVED,Csa::STATUS_COORDINATOR_REVISED]]);
        if($orgUnitId)$query->andWhere(['org_unit_id'=>$orgUnitId]);$count=0;
        foreach($query->all() as $csa)$count+=$this->syncApprovedCsa($csa);return $count;
    }
}
