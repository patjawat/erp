<?php

namespace app\modules\serviceProfile\services;

use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\hr\models\TeamGroupDetail;
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use app\modules\settings\models\OrgUnit;

/** Connects Service Profile to the fiscal-year organization registry. */
class OwnerDirectoryService
{
    public function ownerOptions(int $fiscalYear, ?int $keepId = null): array
    {
        $year = OrgUnit::yearWithData($fiscalYear);
        $typeLabels = Categorise::find()->select(['title', 'code'])
            ->where(['name' => 'org_unit_type'])->indexBy('code')->column();
        $query = OrgUnit::find()->where(['thai_year' => $year, 'active' => 1])
            ->orderBy(['unit_type' => SORT_ASC, 'sort' => SORT_ASC, 'name' => SORT_ASC]);
        if ($keepId) $query->orWhere(['id' => $keepId]);

        $groups = [];
        foreach ($query->all() as $unit) {
            $group = $typeLabels[$unit->unit_type] ?? ($unit->unit_type === OrgUnit::TYPE_TEAM ? 'ทีมประสาน' : 'หน่วยงาน');
            $groups[$group][(int) $unit->id] = $unit->name;
        }
        return $groups;
    }

    public function orgUnitForDepartment(?int $departmentId, int $fiscalYear): ?OrgUnit
    {
        if (!$departmentId) return null;
        $year = OrgUnit::yearWithData($fiscalYear);
        return OrgUnit::find()->where([
            'thai_year' => $year,
            'source' => OrgUnit::SOURCE_STRUCTURE,
            'ref_id' => $departmentId,
            'active' => 1,
        ])->one();
    }

    /** @return array{unit:OrgUnit,owner_type:string,owner_id:int} */
    public function resolveOwner(int $orgUnitId, int $fiscalYear): array
    {
        $unit = OrgUnit::findOne(['id' => $orgUnitId, 'active' => 1]);
        if (!$unit) throw new \DomainException('ไม่พบหน่วยงานในทะเบียนตั้งค่าหน่วยงาน');
        if ((int) $unit->thai_year !== OrgUnit::yearWithData($fiscalYear)) {
            throw new \DomainException('หน่วยงานที่เลือกไม่อยู่ในทะเบียนของปีงบประมาณนี้');
        }

        if ($unit->source === OrgUnit::SOURCE_STRUCTURE && (int) $unit->ref_id > 0) {
            return ['unit' => $unit, 'owner_type' => ServiceProfileTemplate::OWNER_DEPARTMENT, 'owner_id' => (int) $unit->ref_id];
        }
        $data = is_array($unit->data_json) ? $unit->data_json : [];
        $teamGroupId = (int) ($data['team_group_id'] ?? 0);
        if ($teamGroupId <= 0) {
            throw new \DomainException('ทีมประสานนี้ยังไม่ได้เชื่อมกับทะเบียนคณะทำงาน กรุณาตั้งค่าหน่วยงานให้สมบูรณ์');
        }
        return ['unit' => $unit, 'owner_type' => ServiceProfileTemplate::OWNER_COORDINATOR_TEAM, 'owner_id' => $teamGroupId];
    }

    public function findOrgUnit(string $ownerType, int $ownerId, int $fiscalYear): ?OrgUnit
    {
        $year = OrgUnit::yearWithData($fiscalYear);
        if ($ownerType === ServiceProfileTemplate::OWNER_DEPARTMENT) {
            return OrgUnit::findOne(['thai_year' => $year, 'source' => OrgUnit::SOURCE_STRUCTURE, 'ref_id' => $ownerId]);
        }
        foreach (OrgUnit::find()->where(['thai_year' => $year, 'source' => OrgUnit::SOURCE_MANUAL])->all() as $unit) {
            $data = is_array($unit->data_json) ? $unit->data_json : [];
            if ((int) ($data['team_group_id'] ?? 0) === $ownerId) return $unit;
        }
        return null;
    }

    /** Grouped choices: unit staff, committee/team members, then all remaining active staff. */
    public function employeeOptions(int $orgUnitId, int $fiscalYear): array
    {
        $unit = OrgUnit::findOne($orgUnitId);
        if (!$unit) return [];

        $unitIds = [];
        if ($unit->source === OrgUnit::SOURCE_STRUCTURE && $unit->ref_id) {
            $unitIds = Employees::find()->select('id')->where(['status' => Employees::STATUS_WORKING, 'department' => (int) $unit->ref_id])->column();
        }
        $data = is_array($unit->data_json) ? $unit->data_json : [];
        $teamGroupId = (int) ($data['team_group_id'] ?? 0);
        $committeeIds = [];
        if ($teamGroupId > 0) {
            $appointment = TeamGroupDetail::find()->where([
                'name' => 'appointment', 'category_id' => $teamGroupId, 'deleted_at' => null,
            ])->andWhere(['<=', 'thai_year', $fiscalYear])->orderBy(['thai_year' => SORT_DESC, 'id' => SORT_DESC])->one();
            if ($appointment) {
                $committeeIds = TeamGroupDetail::find()->select('emp_id')->where([
                    'name' => 'committee', 'category_id' => $appointment->id, 'deleted_at' => null,
                ])->andWhere(['not', ['emp_id' => null]])->column();
            }
        }
        if ($unit->leader_emp_id) $committeeIds[] = (int) $unit->leader_emp_id;

        $unitIds = array_values(array_unique(array_map('intval', $unitIds)));
        $committeeIds = array_values(array_diff(array_unique(array_map('intval', $committeeIds)), $unitIds));
        $employees = Employees::find()->where(['status' => Employees::STATUS_WORKING])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->indexBy('id')->all();
        $groups = [];
        $append = static function (array $ids, string $label) use (&$groups, $employees): void {
            foreach ($ids as $id) if (isset($employees[$id])) $groups[$label][$id] = $employees[$id]->fullname();
        };
        $append($unitIds, 'บุคลากรในหน่วยงาน');
        $append($committeeIds, 'สมาชิกคณะกรรมการ / ทีมประสาน');
        $used = array_flip(array_merge($unitIds, $committeeIds));
        foreach ($employees as $id => $employee) {
            if (!isset($used[(int) $id])) $groups['บุคลากรอื่นที่กำหนดเพิ่ม'][(int) $id] = $employee->fullname();
        }
        return $groups;
    }

    public function headEmployee(string $ownerType, int $ownerId, int $fiscalYear): ?Employees
    {
        $unit = $this->findOrgUnit($ownerType, $ownerId, $fiscalYear);
        if ($unit && (int) $unit->leader_emp_id > 0) {
            return Employees::findOne((int) $unit->leader_emp_id);
        }
        if ($ownerType === ServiceProfileTemplate::OWNER_DEPARTMENT) {
            $organization = \app\modules\hr\models\Organization::findOne($ownerId);
            return $organization?->leader;
        }
        return null;
    }
}
