<?php
namespace app\modules\iacRisk\services;
use app\modules\iacRisk\models\Pk4;use app\modules\iacRisk\models\Pk4Item;use Yii;
class Pk4Service
{
    public function create(int $hospitalId,int $fiscalYearId,int $fiscalYear,int $orgUnitId): Pk4
    {
        $existing=Pk4::findOne(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'org_unit_id'=>$orgUnitId]);if($existing)return $existing;$tx=Yii::$app->db->beginTransaction();try{$now=date('Y-m-d H:i:s');$uid=(int)Yii::$app->user->id;$unit=\app\modules\settings\models\OrgUnit::findOne($orgUnitId);$signer=$unit?\app\modules\hr\models\Employees::findOne($unit->leader_emp_id):null;$model=new Pk4(['ref'=>Yii::$app->security->generateRandomString(24),'hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'fiscal_year'=>$fiscalYear,'org_unit_id'=>$orgUnitId,'status'=>Pk4::STATUS_DRAFT,'signer_emp_id'=>$signer?->id,'signer_name'=>$signer?->fullname(),'signer_position'=>$signer?->positionName(),'created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid]);$model->save(false);$seq=10;foreach(Pk4Item::components() as $code=>$name){$row=new Pk4Item(['pk4_id'=>$model->id,'component_code'=>$code,'sequence'=>$seq,'component_name'=>$name,'created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid]);$row->save(false);$seq+=10;}$tx->commit();return $model;}catch(\Throwable $e){$tx->rollBack();throw $e;}
    }
}
