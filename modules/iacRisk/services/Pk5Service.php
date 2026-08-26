<?php
namespace app\modules\iacRisk\services;
use app\modules\iacRisk\models\CsaRisk;
use app\modules\iacRisk\models\RiskRegister;

class Pk5Service
{
    public function rows(int $hospitalId,int $fiscalYearId,?int $orgUnitId=null): array
    {
        (new RiskRegisterService())->syncScope($hospitalId,$fiscalYearId,$orgUnitId);
        $query=RiskRegister::find()->with('orgUnit')->where(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'status'=>RiskRegister::STATUS_ACTIVE]);if($orgUnitId)$query->andWhere(['org_unit_id'=>$orgUnitId]);return $query->orderBy(['org_unit_id'=>SORT_ASC,'id'=>SORT_ASC])->all();
    }
    public function errors(array $rows): array
    {
        $errors=[];foreach($rows as $row){if($row->adequacy===CsaRisk::ADEQUACY_INADEQUATE&&!trim((string)$row->improvement_plan))$errors[]='ความเสี่ยง “'.$row->risk_name.'” ยังไม่มีวิธีการปรับปรุง/แก้ไข';}return $errors;
    }
    public static function adequacyLabel(?string $value): string{return CsaRisk::adequacyLabels()[$value]??'ยังไม่ประเมิน';}
}
