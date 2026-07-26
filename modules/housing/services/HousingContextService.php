<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\filemanager\models\Uploads;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\LocationPhoto;
use app\modules\housing\models\MaintenanceRequest;
use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\models\Occupancy;
use app\modules\hr\models\Employees;
use yii\data\ActiveDataProvider;

final class HousingContextService
{
    public function forUser(int $userId, array $options = []): array
    {
        $requestedTab = (string)($options['tab'] ?? 'overview');
        $tab = in_array($requestedTab, ['overview', 'expenses', 'maintenance', 'assets', 'documents'], true)
            ? $requestedTab
            : 'overview';
        $employee = Employees::findOne(['user_id' => $userId]);
        if (!$employee) {
            return ['mode' => 'unavailable', 'employee' => null, 'occupancy' => null, 'request' => null, 'assets' => [], 'photos' => [], 'recentExpenses' => [], 'recentMaintenance' => [], 'expenseProvider' => null, 'maintenanceProvider' => null, 'maintenancePhotos' => [], 'summary' => [], 'tab' => $tab];
        }
        $occupancy = Occupancy::find()
            ->with(['unit.building', 'unit.floor', 'room', 'handover', 'checkout'])
            ->where(['emp_id' => $employee->id, 'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if ($occupancy) {
            $locationFilter = $occupancy->room_id ? ['room_id' => $occupancy->room_id] : ['room_id' => null];
            $maintenanceBase = MaintenanceRequest::find()
                ->with('assignedEmployee')
                ->where([
                    'or',
                    ['occupancy_id' => $occupancy->id],
                    [
                        'and',
                        ['building_id' => $occupancy->unit?->building_id],
                        ['reporter_type' => MaintenanceRequest::REPORTER_CARETAKER],
                        ['problem_scope' => [MaintenanceRequest::SCOPE_STRUCTURE, MaintenanceRequest::SCOPE_COMMON]],
                    ],
                ]);
            if ($occupancy->start_date) {
                $maintenanceBase->andWhere(['>=', 'reported_at', $occupancy->start_date . ' 00:00:00']);
            }
            $expenseBase = MonthlyAccount::find()
                ->joinWith('period')
                ->where([
                    'housing_monthly_account.occupancy_id' => $occupancy->id,
                    'housing_billing_period.status' => 'closed',
                ]);
            $expenseSummary = (clone $expenseBase)
                ->select([
                    'expense_total' => 'COALESCE(SUM(housing_monthly_account.total_amount),0)',
                    'paid_total' => 'COALESCE(SUM(housing_monthly_account.paid_amount),0)',
                    'balance_total' => 'COALESCE(SUM(housing_monthly_account.balance_amount),0)',
                ])
                ->asArray()
                ->one();
            $summary = [
                'expenseTotal' => (float)($expenseSummary['expense_total'] ?? 0),
                'paidTotal' => (float)($expenseSummary['paid_total'] ?? 0),
                'balanceTotal' => (float)($expenseSummary['balance_total'] ?? 0),
                'openMaintenance' => (int)(clone $maintenanceBase)
                    ->andWhere(['status' => [MaintenanceRequest::STATUS_NEW, MaintenanceRequest::STATUS_IN_PROGRESS]])
                    ->count(),
            ];
            $recentExpenses = $tab === 'overview'
                ? (clone $expenseBase)
                    ->with(['period', 'items.chargeType'])
                    ->orderBy(['housing_billing_period.start_date' => SORT_DESC])
                    ->limit(3)
                    ->all()
                : [];
            $recentMaintenance = $tab === 'overview'
                ? (clone $maintenanceBase)
                    ->orderBy(['reported_at' => SORT_DESC, 'id' => SORT_DESC])
                    ->limit(3)
                    ->all()
                : [];
            $expenseProvider = null;
            if ($tab === 'expenses') {
                $expenseQuery = (clone $expenseBase)->with(['period', 'items.chargeType']);
                $expenseYear = (int)($options['expenseYear'] ?? 0);
                if ($expenseYear >= 2000 && $expenseYear <= 2200) {
                    $expenseQuery->andWhere(['between', 'housing_billing_period.start_date', $expenseYear . '-01-01', $expenseYear . '-12-31']);
                }
                $expenseProvider = new ActiveDataProvider([
                    'query' => $expenseQuery,
                    'sort' => ['defaultOrder' => ['billing_period_id' => SORT_DESC]],
                    'pagination' => ['pageSize' => 10, 'pageParam' => 'housing_expense_page'],
                ]);
            }
            $maintenanceProvider = null;
            if ($tab === 'maintenance') {
                $maintenanceQuery = clone $maintenanceBase;
                $maintenanceStatus = (string)($options['maintenanceStatus'] ?? 'all');
                if ($maintenanceStatus === 'open') {
                    $maintenanceQuery->andWhere(['status' => [MaintenanceRequest::STATUS_NEW, MaintenanceRequest::STATUS_IN_PROGRESS]]);
                } elseif (array_key_exists($maintenanceStatus, MaintenanceRequest::statusOptions())) {
                    $maintenanceQuery->andWhere(['status' => $maintenanceStatus]);
                }
                $maintenanceYear = (int)($options['maintenanceYear'] ?? 0);
                if ($maintenanceYear >= 2000 && $maintenanceYear <= 2200) {
                    $maintenanceQuery->andWhere(['between', 'reported_at', $maintenanceYear . '-01-01 00:00:00', $maintenanceYear . '-12-31 23:59:59']);
                }
                $maintenanceProvider = new ActiveDataProvider([
                    'query' => $maintenanceQuery,
                    'sort' => ['defaultOrder' => ['reported_at' => SORT_DESC, 'id' => SORT_DESC]],
                    'pagination' => ['pageSize' => 10, 'pageParam' => 'housing_maintenance_page'],
                ]);
            }
            $maintenance = $maintenanceProvider ? $maintenanceProvider->getModels() : $recentMaintenance;
            $maintenancePhotos = [];
            $maintenanceRefs = array_values(array_filter(array_map(static fn(MaintenanceRequest $item): ?string => $item->ref, $maintenance)));
            if ($maintenanceRefs !== []) {
                foreach (Uploads::find()
                    ->where(['ref' => $maintenanceRefs, 'name' => ['housing_repair_before', 'housing_repair_after']])
                    ->orderBy(['id' => SORT_ASC])
                    ->all() as $upload) {
                    $maintenancePhotos[$upload->ref][] = $upload;
                }
            }
            return [
                'mode' => $occupancy->status === Occupancy::STATUS_ACTIVE ? 'resident' : 'allocated',
                'employee' => $employee,
                'occupancy' => $occupancy,
                'request' => null,
                'assets' => $tab === 'assets' ? AssetAssignment::find()
                    ->where(['unit_id' => $occupancy->unit_id, 'is_active' => 1])
                    ->andWhere($locationFilter)
                    ->orderBy(['item_name' => SORT_ASC])
                    ->all() : [],
                'photos' => in_array($tab, ['overview', 'assets'], true) ? LocationPhoto::find()
                    ->with('upload')
                    ->where(['unit_id' => $occupancy->unit_id])
                    ->andWhere($locationFilter)
                    ->orderBy(['is_primary' => SORT_DESC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])
                    ->limit($tab === 'overview' ? 1 : 8)
                    ->all() : [],
                'recentExpenses' => $recentExpenses,
                'recentMaintenance' => $recentMaintenance,
                'expenseProvider' => $expenseProvider,
                'maintenanceProvider' => $maintenanceProvider,
                'maintenancePhotos' => $maintenancePhotos,
                'summary' => $summary,
                'tab' => $tab,
            ];
        }
        $request = HousingRequest::find()
            ->where(['emp_id' => $employee->id])
            ->andWhere(['not in', 'status', [
                HousingRequest::STATUS_REJECTED,
                HousingRequest::STATUS_COMPLETED,
                HousingRequest::STATUS_CANCELLED,
            ]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        return [
            'mode' => $request ? 'request' : 'applicant',
            'employee' => $employee,
            'occupancy' => null,
            'request' => $request,
            'assets' => [],
            'photos' => [],
            'recentExpenses' => [],
            'recentMaintenance' => [],
            'expenseProvider' => null,
            'maintenanceProvider' => null,
            'maintenancePhotos' => [],
            'summary' => [],
            'tab' => $tab,
        ];
    }
}
