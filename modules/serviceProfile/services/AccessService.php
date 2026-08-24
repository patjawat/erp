<?php

namespace app\modules\serviceProfile\services;

use app\components\UserHelper;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileApproval;
use app\modules\serviceProfile\models\ServiceProfileAuthor;
use app\modules\serviceProfile\models\ServiceProfileQualityReviewer;
use Yii;

class AccessService
{
    public function employee()
    {
        return UserHelper::GetEmployee();
    }

    public function canView(ServiceProfile $profile): bool
    {
        if (Yii::$app->user->can('serviceProfileAdmin')) return true;
        $employee = $this->employee();
        if (!$employee) return false;
        if ($profile->owner_type === 'department' && (int) $employee->department === (int) $profile->owner_id) return true;
        return ServiceProfileAuthor::find()->where(['service_profile_id' => $profile->id, 'employee_id' => $employee->id])->exists()
            || ServiceProfileQualityReviewer::find()->where(['owner_type' => $profile->owner_type, 'owner_id' => $profile->owner_id, 'employee_id' => $employee->id, 'active' => 1])->exists()
            || ServiceProfileApproval::find()->where(['service_profile_id' => $profile->id, 'employee_id' => $employee->id])->exists();
    }

    public function canEdit(ServiceProfile $profile): bool
    {
        if (!in_array($profile->status, [ServiceProfile::STATUS_DRAFT, ServiceProfile::STATUS_RETURNED], true)) return false;
        if (Yii::$app->user->can('serviceProfileAdmin')) return true;
        $employee = $this->employee();
        return $employee && ServiceProfileAuthor::find()->where(['service_profile_id' => $profile->id, 'employee_id' => $employee->id])->exists();
    }

    public function canSubmit(ServiceProfile $profile): bool
    {
        if (Yii::$app->user->can('serviceProfileAdmin')) return true;
        $employee = $this->employee();
        return $employee && ServiceProfileAuthor::find()->where([
            'service_profile_id' => $profile->id, 'employee_id' => $employee->id,
            'role' => ServiceProfileAuthor::ROLE_COORDINATOR,
        ])->exists();
    }

    public function canReview(ServiceProfile $profile): bool
    {
        $employee = $this->employee();
        return $employee && $profile->status === ServiceProfile::STATUS_REVIEW_PENDING
            && ServiceProfileQualityReviewer::find()->where([
                'owner_type' => $profile->owner_type, 'owner_id' => $profile->owner_id,
                'employee_id' => $employee->id, 'active' => 1,
            ])->exists();
    }

    public function isLeadReviewer(ServiceProfile $profile): bool
    {
        $employee = $this->employee();
        return $employee && ServiceProfileQualityReviewer::find()->where([
            'owner_type' => $profile->owner_type, 'owner_id' => $profile->owner_id,
            'employee_id' => $employee->id, 'active' => 1, 'is_lead' => 1,
        ])->exists();
    }

    public function canActStage(ServiceProfile $profile, string $stage): bool
    {
        $employee = $this->employee();
        return $employee && ServiceProfileApproval::find()->where([
            'service_profile_id' => $profile->id, 'stage' => $stage,
            'employee_id' => $employee->id, 'status' => ServiceProfileApproval::STATUS_PENDING,
        ])->exists();
    }
}
