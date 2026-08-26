<?php

namespace app\modules\serviceProfile\services;

use app\modules\hr\models\Employees;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileApproval;
use app\modules\serviceProfile\models\ServiceProfileAuthor;
use app\modules\serviceProfile\models\ServiceProfileQualityReviewer;

class InboxService
{
    public function actionRequiredProfileIds(Employees $employee): array
    {
        $ids = ServiceProfileApproval::find()->select('service_profile_id')->where([
            'employee_id'=>$employee->id,'status'=>ServiceProfileApproval::STATUS_PENDING,
        ])->column();
        if ((new DirectorResolver())->isConfiguredDirector($employee)) {
            $legacyDirectorIds = ServiceProfileApproval::find()->alias('approval')
                ->select('approval.service_profile_id')
                ->innerJoin(['profile' => ServiceProfile::tableName()], 'profile.id = approval.service_profile_id')
                ->where([
                    'approval.stage' => ServiceProfileApproval::STAGE_DIRECTOR,
                    'approval.status' => ServiceProfileApproval::STATUS_PENDING,
                    'profile.status' => ServiceProfile::STATUS_APPROVAL_PENDING,
                ])->column();
            $ids = array_merge($ids, $legacyDirectorIds);
        }
        $ownerIds = ServiceProfileQualityReviewer::find()->select('owner_id')->where([
            'owner_type'=>'department','employee_id'=>$employee->id,'active'=>1,
        ])->column();
        if($ownerIds) $ids=array_merge($ids,ServiceProfile::find()->select('id')->where([
            'owner_type'=>'department','owner_id'=>array_map('intval',$ownerIds),'status'=>ServiceProfile::STATUS_REVIEW_PENDING,
        ])->column());
        $coordinatorIds=ServiceProfileAuthor::find()->select('service_profile_id')->where([
            'employee_id'=>$employee->id,'role'=>ServiceProfileAuthor::ROLE_COORDINATOR,
        ])->column();
        if($coordinatorIds) $ids=array_merge($ids,ServiceProfile::find()->select('id')->where([
            'id'=>array_map('intval',$coordinatorIds),'status'=>ServiceProfile::STATUS_RETURNED,
        ])->column());
        return array_values(array_unique(array_map('intval',$ids)));
    }

    public function count(Employees $employee): int
    {
        return count($this->actionRequiredProfileIds($employee));
    }
}
