<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\Csa;
use app\modules\iacRisk\models\FiscalYear;
use app\modules\iacRisk\models\Hospital;
use app\modules\iacRisk\models\ServiceProcessVersion;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use Yii;

class CsaService
{
    public function create(ServiceProcessVersion $version): Csa
    {
        $profile = $version->profile;
        if (!$profile) throw new \DomainException('ไม่พบ Service Profile ต้นทาง');
        if (!in_array($profile->status, [ServiceProfile::STATUS_DRAFT, ServiceProfile::STATUS_RETURNED], true)) {
            throw new \DomainException('เริ่ม CSA ได้เมื่อ Service Profile เป็นฉบับร่างหรือส่งกลับแก้ไข');
        }
        if ($version->review_status === ServiceProcessVersion::REVIEW_RETIRED) throw new \DomainException('กระบวนงานนี้ถูกยกเลิกใช้แล้ว');
        $hospitalId = (int) Hospital::find()->where(['active'=>1,'is_current'=>1])->select('id')->scalar();
        $fiscal = FiscalYear::findOne(['hospital_id'=>$hospitalId,'fiscal_year'=>$version->fiscal_year]);
        $unit = (new OwnerDirectoryService())->findOrgUnit($profile->owner_type, (int)$profile->owner_id, (int)$version->fiscal_year);
        if (!$hospitalId || !$fiscal || !$unit) throw new \DomainException('ตั้งค่าโรงพยาบาล ปีงบประมาณ หรือหน่วยงานไม่ครบ');
        $existing = Csa::findOne(['hospital_id'=>$hospitalId,'process_id'=>$version->process_id,'fiscal_year'=>$version->fiscal_year]);
        if ($existing) return $existing;
        $uid = !Yii::$app->has('user') || Yii::$app->user->isGuest ? null : (int)Yii::$app->user->id;
        $now = date('Y-m-d H:i:s');
        $model = new Csa([
            'ref'=>Yii::$app->security->generateRandomString(24),'hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscal->id,
            'org_unit_id'=>$unit->id,'service_profile_id'=>$profile->id,'process_id'=>$version->process_id,
            'process_version_id'=>$version->id,'fiscal_year'=>$version->fiscal_year,'revision_no'=>1,
            'process_name_snapshot'=>$version->name,'objective_snapshot'=>$version->objective,'status'=>Csa::STATUS_DRAFT,
            'created_at'=>$now,'updated_at'=>$now,'created_by'=>$uid,'updated_by'=>$uid,
        ]);
        if (!$model->save(false)) throw new \RuntimeException('ไม่สามารถสร้าง CSA ได้');
        (new ActivityService())->log(['hospital_id'=>$hospitalId,'fiscal_year_id'=>$fiscal->id,'org_unit_id'=>$unit->id,'entity_type'=>'csa','entity_id'=>$model->id,'action'=>'created','to_status'=>$model->status,'message'=>'เริ่มวิเคราะห์ CSA จากกระบวนงาน '.$version->name]);
        return $model;
    }
}
