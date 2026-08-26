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
        $access=new AccessService();if($access->canManageAllUnits())return true;if(!$access->canAuthor())return false;return $this->isSameUnit($report,$fiscalYear);
    }
    public function canApprove(RiskReport $report): bool
    {
        if(Yii::$app->user->can('iacRiskAdmin'))return true;$employee=UserHelper::GetEmployee();$unit=OrgUnit::findOne($report->org_unit_id);$year=(int)$report->period?->fiscalYear?->fiscal_year;return $employee&&$unit&&((int)$unit->leader_emp_id===(int)$employee->id||(Yii::$app->user->can('iacRiskUnitApprove')&&$this->isSameUnit($report,$year)));
    }
    private function isSameUnit(RiskReport $report,int $fiscalYear): bool{$employee=UserHelper::GetEmployee();if(!$employee||!$fiscalYear)return false;$unit=(new OwnerDirectoryService())->orgUnitForDepartment($employee->department?(int)$employee->department:null,$fiscalYear);return $unit&&(int)$unit->id===(int)$report->org_unit_id;}
}
