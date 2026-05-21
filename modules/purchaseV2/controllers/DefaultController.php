<?php

namespace app\modules\purchaseV2\controllers;

use Yii;
use yii\web\Controller;
use app\components\UserHelper;
use app\modules\purchaseV2\models\PurchaseRequest;
use app\modules\purchaseV2\models\PurchaseRequestApproval;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $canViewAll = Yii::$app->user->can('admin') || Yii::$app->user->can('purchase');

        $requestQuery = PurchaseRequest::find();
        if (!$canViewAll && $me) {
            $assignedIds = PurchaseRequestApproval::find()
                ->select('request_id')
                ->where(['approver_emp_id' => $me->id, 'status' => PurchaseRequestApproval::pendingStatusValues()])
                ->column();
            $requestQuery->andWhere([
                'or',
                ['created_by' => Yii::$app->user->id],
                ['requester_emp_id' => $me->id],
                ['id' => $assignedIds ?: [0]],
            ]);
        } elseif (!$canViewAll) {
            $requestQuery->andWhere(['created_by' => Yii::$app->user->id]);
        }

        $pendingApprovalCount = (clone $requestQuery)->andWhere(['status' => PurchaseRequest::STATUS_PENDING_APPROVAL])->count();
        $processingCount = (clone $requestQuery)->andWhere(['in', 'status', [PurchaseRequest::STATUS_APPROVED, PurchaseRequest::STATUS_ORDERED, PurchaseRequest::STATUS_RECEIVED, PurchaseRequest::STATUS_STOCKED]])->count();
        $completedCount = (clone $requestQuery)->andWhere(['status' => PurchaseRequest::STATUS_COMPLETED])->count();
        $draftCount = (clone $requestQuery)->andWhere(['status' => PurchaseRequest::STATUS_DRAFT])->count();
        $cancelledCount = (clone $requestQuery)->andWhere(['status' => PurchaseRequest::STATUS_CANCELLED])->count();
        $budgetUsed = (float) (clone $requestQuery)->andWhere(['not in', 'status', [PurchaseRequest::STATUS_DRAFT, PurchaseRequest::STATUS_CANCELLED]])->sum('grand_total');
        $recentRequests = (clone $requestQuery)->orderBy(['id' => SORT_DESC])->limit(8)->all();
        $currentApprovalsQuery = PurchaseRequestApproval::find()
            ->alias('pa')
            ->joinWith(['request pr'])
            ->where(['pa.status' => PurchaseRequestApproval::pendingStatusValues()]);

        if (!$canViewAll && $me) {
            $currentApprovalsQuery->andWhere([
                'or',
                ['pa.approver_emp_id' => $me->id],
                ['pr.created_by' => Yii::$app->user->id],
                ['pr.requester_emp_id' => $me->id],
            ]);
        } elseif (!$canViewAll) {
            $currentApprovalsQuery->andWhere('0=1');
        }

        $currentApprovals = $currentApprovalsQuery
            ->orderBy(['pa.step_no' => SORT_ASC, 'pa.id' => SORT_DESC])
            ->limit(8)
            ->all();

        return $this->render('index', [
            'draftCount' => $draftCount,
            'pendingApprovalCount' => $pendingApprovalCount,
            'processingCount' => $processingCount,
            'completedCount' => $completedCount,
            'cancelledCount' => $cancelledCount,
            'budgetUsed' => $budgetUsed,
            'recentRequests' => $recentRequests,
            'currentApprovals' => $currentApprovals,
        ]);
    }
}
