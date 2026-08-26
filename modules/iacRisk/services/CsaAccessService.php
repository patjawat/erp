<?php

namespace app\modules\iacRisk\services;

use app\components\UserHelper;
use app\modules\iacRisk\models\Csa;
use app\modules\serviceProfile\services\AccessService as ProfileAccessService;
use app\modules\settings\models\OrgUnit;
use Yii;

class CsaAccessService
{
    public function canEdit(Csa $csa): bool
    {
        if (!in_array($csa->status,[Csa::STATUS_DRAFT,Csa::STATUS_RETURNED],true)) return false;
        $profile=$csa->processVersion?->profile;
        return $profile && ((new ProfileAccessService())->canEdit($profile)||Yii::$app->user->can('iacRiskCoordinate')||Yii::$app->user->can('iacRiskAdmin'));
    }
    public function canConfirm(Csa $csa): bool { return $this->canEdit($csa); }
    public function canSendHead(Csa $csa): bool
    {
        if($csa->status!==Csa::STATUS_AUTHOR_CONFIRMED)return false;
        $profile=$csa->processVersion?->profile;
        return $profile && ((new ProfileAccessService())->canEdit($profile)||Yii::$app->user->can('iacRiskAdmin'));
    }
    public function canHeadAct(Csa $csa): bool
    {
        if($csa->status!==Csa::STATUS_HEAD_PENDING)return false;
        if(Yii::$app->user->can('iacRiskUnitApprove')||Yii::$app->user->can('iacRiskAdmin'))return true;
        $employee=UserHelper::GetEmployee();$unit=OrgUnit::findOne($csa->org_unit_id);
        return $employee&&$unit&&(int)$unit->leader_emp_id===(int)$employee->id;
    }
}
