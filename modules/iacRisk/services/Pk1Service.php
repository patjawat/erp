<?php
namespace app\modules\iacRisk\services;
use app\components\SiteHelper;
use app\modules\iacRisk\models\Hospital;
use app\modules\iacRisk\models\Pk1;
use Yii;

class Pk1Service
{
    public function create(int $hospitalId,int $fiscalYearId,int $fiscalYear): Pk1
    {
        $existing=Pk1::findOne(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId]);if($existing)return $existing;
        $hospital=Hospital::findOne($hospitalId);$hospitalName=$hospital?->name?:'หน่วยงาน';$director=SiteHelper::viewDirector();$uid=(int)Yii::$app->user->id;$now=date('Y-m-d H:i:s');
        $model=new Pk1(['ref'=>Yii::$app->security->generateRandomString(24),'hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscalYearId,'fiscal_year'=>$fiscalYear,'status'=>Pk1::STATUS_DRAFT,'recipient'=>'นายแพทย์สาธารณสุขจังหวัดเลย','assessment_text'=>$hospitalName.' ได้ประเมินผลการควบคุมภายในของหน่วยงาน สำหรับปีสิ้นสุดวันที่ 30 กันยายน '.$fiscalYear.' ด้วยวิธีการที่หน่วยงานกำหนด ซึ่งเป็นไปตามหลักเกณฑ์กระทรวงการคลังว่าด้วยมาตรฐานและหลักเกณฑ์ปฏิบัติการควบคุมภายในสำหรับหน่วยงานของรัฐ พ.ศ. 2561','conclusion_text'=>'จากผลการประเมินดังกล่าว '.$hospitalName.' เห็นว่า การควบคุมภายในของหน่วยงานมีความเพียงพอ ปฏิบัติตามอย่างต่อเนื่อง และเป็นไปตามหลักเกณฑ์กระทรวงการคลังว่าด้วยมาตรฐานและหลักเกณฑ์ปฏิบัติการควบคุมภายในสำหรับหน่วยงานของรัฐ พ.ศ. 2561 ภายใต้การกำกับดูแลของผู้บริหาร','signer_emp_id'=>(int)($director['id']?:0)?:null,'signer_name'=>$director['fullname']?:null,'signer_position'=>$director['position_name']?:'ผู้อำนวยการโรงพยาบาล','signature_type'=>'system','created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid]);$model->save(false);return $model;
    }
}
