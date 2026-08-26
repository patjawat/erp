<?php

namespace app\modules\serviceProfile\services;

use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileAuthor;
use app\modules\serviceProfile\models\ServiceProfileQualityReviewer;
use app\modules\serviceProfile\models\ServiceProfileSectionComment;

class ReadinessService
{
    public function inspect(ServiceProfile $profile): array
    {
        $missing = [];
        foreach($profile->sections as $section) if($section->is_required&&!$section->isComplete()) $missing[]=$section->title;
        $coordinator = $profile->getAuthors()->where(['role'=>ServiceProfileAuthor::ROLE_COORDINATOR])->exists();
        $lead = ServiceProfileQualityReviewer::find()->with('employee')->where([
            'owner_type'=>$profile->owner_type,'owner_id'=>$profile->owner_id,'active'=>1,'is_lead'=>1,
        ])->one();
        $reviewerReady = $lead&&$lead->employee&&$lead->employee->user_id;
        $directorReady = (new DirectorResolver())->resolve() !== null;
        $head = (new OwnerDirectoryService())->headEmployee($profile->owner_type, (int) $profile->owner_id, (int) $profile->fiscal_year);
        $headReady = $head?->user_id !== null;
        $openComments=(int)ServiceProfileSectionComment::find()->where(['service_profile_id'=>$profile->id,'status'=>ServiceProfileSectionComment::STATUS_OPEN])->count();
        $checks = [
            ['key'=>'sections','label'=>'หัวข้อบังคับ','ready'=>$missing===[],'detail'=>$missing===[]?'กรอกครบทุกหัวข้อ':'ยังขาด '.count($missing).' หัวข้อ'],
            ['key'=>'coordinator','label'=>'ผู้ประสานหลัก','ready'=>$coordinator,'detail'=>$coordinator?'กำหนดแล้ว':'ยังไม่ได้กำหนด'],
            ['key'=>'reviewer','label'=>'ผู้แทนคุณภาพหลัก','ready'=>(bool)$reviewerReady,'detail'=>$reviewerReady?'พร้อมรับเอกสาร':'ยังไม่ได้กำหนดหรือไม่มีบัญชีผู้ใช้'],
            ['key'=>'director','label'=>'ผู้อำนวยการ','ready'=>$directorReady,'detail'=>$directorReady?'พร้อมรับเอกสาร':'ยังไม่ได้กำหนดผู้อำนวยการหรือยังไม่มีบัญชีผู้ใช้'],
            ['key'=>'head','label'=>'หัวหน้าหน่วยงาน','ready'=>$headReady,'detail'=>$headReady?'พร้อมรับทราบ':'ยังไม่ได้กำหนดหรือไม่มีบัญชีผู้ใช้'],
            ['key'=>'comments','label'=>'ความคิดเห็นรายหัวข้อ','ready'=>$openComments===0,'detail'=>$openComments===0?'ไม่มีรายการค้าง':'ยังรอแก้ไข '.$openComments.' รายการ'],
        ];
        return ['ready'=>!in_array(false,array_column($checks,'ready'),true),'checks'=>$checks,'missing_sections'=>$missing];
    }

}
