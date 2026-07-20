<?php

namespace app\modules\medsop\services;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\hr\models\TeamGroupDetail;
use app\modules\medsop\models\DocumentAudience;

class AudienceResolverService
{
    /**
     * Resolve audience rules to employee ids without changing HR data.
     *
     * @param DocumentAudience[] $audiences
     * @return array<int,array{required:bool,sources:array<int,array<string,mixed>>}>
     */
    public function resolve(array $audiences): array
    {
        $resolved = [];
        foreach ($audiences as $audience) {
            foreach ($this->employeeIdsFor($audience) as $employeeId) {
                if (!isset($resolved[$employeeId])) {
                    $resolved[$employeeId] = ['required' => false, 'sources' => []];
                }
                $resolved[$employeeId]['required'] = $resolved[$employeeId]['required'] || (bool) $audience->required;
                $resolved[$employeeId]['sources'][] = [
                    'audience_id' => $audience->id === null ? null : (int) $audience->id,
                    'type' => $audience->audience_type,
                    'source_id' => (int) $audience->audience_id,
                    'source_version_id' => (int) $audience->audience_version_id ?: null,
                    'include_children' => (bool) $audience->include_children,
                ];
            }
        }

        ksort($resolved);
        return $resolved;
    }

    private function employeeIdsFor(DocumentAudience $audience): array
    {
        if ($audience->audience_type === DocumentAudience::TYPE_EMPLOYEE) {
            return Employees::find()
                ->select('id')
                ->where(['id' => (int) $audience->audience_id, 'status' => 1])
                ->column();
        }

        if ($audience->audience_type === DocumentAudience::TYPE_ORGANIZATION) {
            return $this->organizationEmployeeIds($audience);
        }

        if ($audience->audience_type === DocumentAudience::TYPE_TEAM_GROUP) {
            return $this->teamEmployeeIds($audience);
        }

        throw new \DomainException('Unsupported SOP/WI audience type.');
    }

    private function organizationEmployeeIds(DocumentAudience $audience): array
    {
        $organization = Organization::findOne((int) $audience->audience_id);
        if ($organization === null) {
            throw new \DomainException('ไม่พบหน่วยงานที่กำหนดเป็นผู้รับเอกสาร');
        }

        $organizationIds = [(int) $organization->id];
        if ($audience->include_children) {
            $organizationIds = Organization::find()
                ->select('id')
                ->where(['root' => $organization->root])
                ->andWhere(['between', 'lft', $organization->lft, $organization->rgt])
                ->orderBy(['lft' => SORT_ASC])
                ->column();
        }

        return array_map('intval', Employees::find()
            ->select('id')
            ->where(['department' => $organizationIds, 'status' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->column());
    }

    private function teamEmployeeIds(DocumentAudience $audience): array
    {
        $appointment = TeamGroupDetail::find()
            ->where([
                'id' => (int) $audience->audience_version_id,
                'name' => 'appointment',
                'category_id' => (int) $audience->audience_id,
            ])
            ->one();
        if ($appointment === null) {
            throw new \DomainException('ไม่พบวาระหรือคำสั่งแต่งตั้งของทีมที่เลือก');
        }

        $employeeIds = TeamGroupDetail::find()
            ->select('emp_id')
            ->where(['name' => 'committee', 'category_id' => (int) $appointment->id])
            ->andWhere(['not', ['emp_id' => null]])
            ->column();
        if ($employeeIds === []) {
            return [];
        }

        return array_map('intval', Employees::find()
            ->select('id')
            ->where([
                'id' => array_values(array_unique(array_map('intval', $employeeIds))),
                'status' => 1,
            ])
            ->orderBy(['id' => SORT_ASC])
            ->column());
    }
}
