<?php
namespace app\modules\iacRisk\services;
use app\components\UserHelper;
use app\modules\iacRisk\models\RiskReport;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use app\modules\settings\models\OrgUnit;
use Yii;
class RiskReportAccessService
{
    public function canPrepare(RiskReport $report,int $fiscalYear): bool
    {
        $access=new AccessService();if($access->canScopeAllUnits())return true;$employee=$access->employee();$unit=(new OwnerDirectoryService())->orgUnitForDepartment($employee?->department?(int)$employee->department:null,$fiscalYear);return $unit&&(int)$unit->id===(int)$report->org_unit_id;
    }
    public function canApprove(RiskReport $report): bool
    {
        if(Yii::$app->user->can('iacRiskUnitApprove')||Yii::$app->user->can('iacRiskAdmin'))return true;$employee=UserHelper::GetEmployee();$unit=OrgUnit::findOne($report->org_unit_id);return $employee&&$unit&&(int)$unit->leader_emp_id===(int)$employee->id;
    }
}
