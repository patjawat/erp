<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\FiscalYear;
use app\modules\iacRisk\models\Hospital;
use app\modules\iacRisk\models\ReportingPeriod;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use Yii;

class ContextService
{
    public function resolve(): array
    {
        $access = new AccessService();
        $request = Yii::$app->request;
        $hospitalQuery = Hospital::find()->where(['active' => 1]);
        if (!$access->canScopeAllHospitals()) $hospitalQuery->andWhere(['is_current' => 1]);
        $hospitals = $hospitalQuery->orderBy(['is_current' => SORT_DESC, 'name' => SORT_ASC])->all();
        if (!$hospitals && !$access->canScopeAllHospitals()) $hospitals = Hospital::find()->where(['active' => 1])->orderBy(['id' => SORT_ASC])->limit(1)->all();
        $hospitalIds = array_map(static fn (Hospital $hospital) => (int) $hospital->id, $hospitals);
        $requestedHospital = (int) $request->get('hospital_id');
        $hospitalId = in_array($requestedHospital, $hospitalIds, true) ? $requestedHospital : ($hospitalIds[0] ?? 0);

        $years = $hospitalId ? FiscalYear::find()->where(['hospital_id' => $hospitalId])->orderBy(['fiscal_year' => SORT_DESC])->all() : [];
        $yearIds = array_map(static fn (FiscalYear $year) => (int) $year->id, $years);
        $requestedYear = (int) $request->get('fiscal_year_id');
        $fiscalYearId = in_array($requestedYear, $yearIds, true) ? $requestedYear : 0;
        if (!$fiscalYearId) foreach ($years as $year) if ($year->is_current) { $fiscalYearId = (int) $year->id; break; }
        if (!$fiscalYearId) $fiscalYearId = $yearIds[0] ?? 0;
        $fiscalYear = $fiscalYearId ? FiscalYear::findOne($fiscalYearId) : null;

        $periods = $fiscalYear ? $fiscalYear->periods : [];
        $periodIds = array_map(static fn (ReportingPeriod $period) => (int) $period->id, $periods);
        $requestedPeriod = (int) $request->get('period_id');
        $periodId = in_array($requestedPeriod, $periodIds, true) ? $requestedPeriod : ($periodIds[0] ?? 0);

        $units = [];
        $orgUnitId = 0;
        if ($fiscalYear) {
            $directory = new OwnerDirectoryService();
            if ($access->canScopeAllUnits()) {
                $units = $directory->ownerOptions((int) $fiscalYear->fiscal_year);
                $valid = [];
                foreach ($units as $group) foreach ($group as $id => $label) $valid[(int) $id] = $label;
                $requestedUnit = (int) $request->get('org_unit_id');
                $orgUnitId = isset($valid[$requestedUnit]) ? $requestedUnit : 0;
            } else {
                $employee = $access->employee();
                $unit = $directory->orgUnitForDepartment($employee?->department ? (int) $employee->department : null, (int) $fiscalYear->fiscal_year);
                if ($unit) {
                    $units = ['หน่วยงานของฉัน' => [(int) $unit->id => $unit->name]];
                    $orgUnitId = (int) $unit->id;
                }
            }
        }

        $canScopeAllUnits = $access->canScopeAllUnits();
        return compact('hospitals', 'hospitalId', 'years', 'fiscalYearId', 'fiscalYear', 'periods', 'periodId', 'units', 'orgUnitId', 'canScopeAllUnits');
    }

    public static function query(array $context): array
    {
        return array_filter([
            'hospital_id' => $context['hospitalId'] ?? null,
            'fiscal_year_id' => $context['fiscalYearId'] ?? null,
            'period_id' => $context['periodId'] ?? null,
            'org_unit_id' => $context['orgUnitId'] ?? null,
        ], static fn ($value) => (int) $value > 0);
    }
}
