<?php
namespace app\modules\iacRisk\services;
use app\modules\iacRisk\models\Pk4;
use app\modules\iacRisk\models\RiskReport;
use app\modules\iacRisk\models\RiskReportItem;
use Yii;

class RiskReportService
{
    public function create(int $hospitalId,int $fiscalYearId,int $periodId,int $orgUnitId): RiskReport
    {
        $latest=RiskReport::find()->where(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'reporting_period_id'=>$periodId,'org_unit_id'=>$orgUnitId])->orderBy(['revision_no'=>SORT_DESC])->one();if($latest&&!in_array($latest->status,[RiskReport::STATUS_APPROVED],true))return $latest;
        $rows=(new Pk5Service())->rows($hospitalId,$fiscalYearId,$orgUnitId);$errors=(new Pk5Service())->errors($rows);if(!$rows)throw new \DomainException('ยังไม่มีข้อมูลความเสี่ยงสำหรับสร้างรายงาน');if($errors)throw new \DomainException(implode(' ',$errors));
        $pk4=Pk4::find()->with('signer')->where(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'org_unit_id'=>$orgUnitId])->one();$signature=null;if($pk4?->signature_type==='canvas')$signature=$pk4->signature_data;elseif($pk4?->signature_type==='system'){$path=$pk4->signer?->signature();if($path&&is_file($path)){$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($path)?:'image/png';$signature='data:'.$mime.';base64,'.base64_encode(file_get_contents($path));}}
        $tx=Yii::$app->db->beginTransaction();try{$now=date('Y-m-d H:i:s');$uid=(int)Yii::$app->user->id;$report=new RiskReport(['ref'=>Yii::$app->security->generateRandomString(24),'hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'reporting_period_id'=>$periodId,'org_unit_id'=>$orgUnitId,'revision_no'=>$latest?(int)$latest->revision_no+1:1,'status'=>RiskReport::STATUS_DRAFT,'signer_name'=>$pk4?->signer_name,'signer_position'=>$pk4?->signer_position,'signature_data'=>$signature,'created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid]);$report->save(false);$seq=10;foreach($rows as $row){$item=new RiskReportItem(['risk_report_id'=>$report->id,'risk_register_id'=>$row->id,'sequence'=>$seq,'mission_objective'=>$row->mission_objective,'risk_name'=>$row->risk_name,'existing_control'=>$row->existing_control,'control_assessment'=>Pk5Service::adequacyLabel($row->adequacy),'residual_risk'=>$row->residual_risk,'improvement_plan'=>$row->improvement_plan,'responsible_person'=>$row->responsible_person,'created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid]);$item->save(false);$seq+=10;}$tx->commit();return $report;}catch(\Throwable $e){$tx->rollBack();throw $e;}
    }
}
