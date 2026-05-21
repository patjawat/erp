<?php

namespace app\modules\purchaseV2\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use app\components\AppHelper;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

class PurchaseRequestSearch extends PurchaseRequest
{
    public $status_group;

    public function rules()
    {
        return [
            [['id', 'requester_emp_id', 'department_id', 'budget_year', 'status', 'current_approval_level', 'legacy_order_id', 'legacy_status'], 'integer'],
            [['request_no', 'request_title', 'request_type', 'request_date', 'budget_type_code', 'vendor_name', 'vendor_id', 'pr_number', 'pq_number', 'po_number', 'gr_number', 'legacy_ref', 'migrated_from', 'q', 'date_start', 'date_end', 'vat_type', 'status_group'], 'safe'],
            [['budget_amount', 'subtotal_amount', 'discount_amount', 'vat_amount', 'grand_total'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = PurchaseRequest::find()->alias('pr')->with(['vendor']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'request_date' => SORT_DESC,
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $this->applyFilters($query);

        return $dataProvider;
    }

    public function buildQuery(bool $includeStatusFilter = true): ActiveQuery
    {
        $query = PurchaseRequest::find()->alias('pr')->with(['vendor']);
        $this->applyFilters($query, $includeStatusFilter);
        return $query;
    }

    protected function applyFilters(ActiveQuery $query, bool $includeStatusFilter = true): void
    {
        $query->andFilterWhere([
            'pr.id' => $this->id,
            'pr.requester_emp_id' => $this->requester_emp_id,
            'pr.department_id' => $this->department_id,
            'pr.budget_year' => $this->budget_year,
            'pr.current_approval_level' => $this->current_approval_level,
            'pr.legacy_order_id' => $this->legacy_order_id,
            'pr.legacy_status' => $this->legacy_status,
            'pr.request_type' => $this->request_type,
            'pr.vat_type' => $this->vat_type,
        ]);

        $query
            ->andFilterWhere(['like', 'pr.request_no', $this->request_no])
            ->andFilterWhere(['like', 'pr.request_title', $this->request_title])
            ->andFilterWhere(['like', 'pr.vendor_name', $this->vendor_name])
            ->andFilterWhere(['like', 'pr.vendor_id', $this->vendor_id])
            ->andFilterWhere(['like', 'pr.pr_number', $this->pr_number])
            ->andFilterWhere(['like', 'pr.pq_number', $this->pq_number])
            ->andFilterWhere(['like', 'pr.po_number', $this->po_number])
            ->andFilterWhere(['like', 'pr.gr_number', $this->gr_number])
            ->andFilterWhere(['like', 'pr.legacy_ref', $this->legacy_ref])
            ->andFilterWhere(['like', 'pr.migrated_from', $this->migrated_from]);

        if (!empty($this->q)) {
            $requesterQuery = Employees::find()
                ->alias('e')
                ->select('1')
                ->where('e.id = pr.requester_emp_id')
                ->andWhere([
                    'or',
                    ['like', 'e.prefix', $this->q],
                    ['like', 'e.fname', $this->q],
                    ['like', 'e.lname', $this->q],
                ]);

            $departmentQuery = Organization::find()
                ->alias('o')
                ->select('1')
                ->where('o.id = pr.department_id')
                ->andWhere(['like', 'o.name', $this->q]);

            $vendorQuery = Categorise::find()
                ->alias('v')
                ->select('1')
                ->where('v.code = pr.vendor_id')
                ->andWhere(['v.name' => 'vendor'])
                ->andWhere(['like', 'v.title', $this->q]);

            $itemQuery = PurchaseRequestItem::find()
                ->alias('pri')
                ->select('1')
                ->where('pri.request_id = pr.id')
                ->andWhere(['like', 'pri.item_name', $this->q]);

            $query->andWhere([
                'or',
                ['like', 'pr.request_no', $this->q],
                ['like', 'pr.request_title', $this->q],
                ['like', 'pr.summary', $this->q],
                ['like', 'pr.vendor_name', $this->q],
                ['like', 'pr.vendor_id', $this->q],
                ['like', 'pr.pr_number', $this->q],
                ['like', 'pr.pq_number', $this->q],
                ['like', 'pr.po_number', $this->q],
                ['like', 'pr.gr_number', $this->q],
                ['like', 'pr.legacy_ref', $this->q],
                ['exists', $requesterQuery],
                ['exists', $departmentQuery],
                ['exists', $vendorQuery],
                ['exists', $itemQuery],
            ]);
        }

        if (!empty($this->date_start) || !empty($this->date_end)) {
            $start = !empty($this->date_start) ? AppHelper::convertToGregorian($this->date_start) : null;
            $end = !empty($this->date_end) ? AppHelper::convertToGregorian($this->date_end) : null;
            if ($start && $end) {
                $query->andWhere(['between', 'pr.request_date', $start, $end]);
            } elseif ($start) {
                $query->andWhere(['>=', 'pr.request_date', $start]);
            } elseif ($end) {
                $query->andWhere(['<=', 'pr.request_date', $end]);
            }
        }

        if ($includeStatusFilter) {
            $this->applyStatusFilter($query);
        }
    }

    protected function applyStatusFilter(ActiveQuery $query): void
    {
        if ($this->status_group !== null && $this->status_group !== '' && $this->status_group !== 'all') {
            switch ($this->status_group) {
                case 'draft':
                    $query->andWhere(['pr.status' => self::STATUS_DRAFT]);
                    return;
                case 'pending':
                    $query->andWhere(['pr.status' => self::STATUS_PENDING_APPROVAL]);
                    return;
                case 'approved':
                    $query->andWhere(['pr.status' => self::STATUS_APPROVED]);
                    return;
                case 'ordered':
                    $query->andWhere(['pr.status' => self::STATUS_ORDERED]);
                    return;
                case 'received':
                    $query->andWhere(['pr.status' => self::STATUS_RECEIVED]);
                    return;
                case 'completed':
                    $query->andWhere(['pr.status' => [self::STATUS_STOCKED, self::STATUS_COMPLETED]]);
                    return;
                case 'cancelled':
                    $query->andWhere(['pr.status' => self::STATUS_CANCELLED]);
                    return;
            }
        }

        if ($this->status !== null && $this->status !== '') {
            $query->andWhere(['pr.status' => $this->status]);
        }
    }
}
