<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StockMonthlyReportSearch represents the model behind the search form of `StockMonthlyReport`.
 */
class StockMonthlyReportSearch extends StockMonthlyReport
{
    public $q;
    public $category_id;

    public function rules()
    {
        return [
            [['id', 'report_year', 'report_month', 'warehouse_id'], 'integer'],
            [['item_code', 'unit_name', 'q', 'category_id'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = StockMonthlyReport::find()->alias('r');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'report_year' => SORT_DESC,
                    'report_month' => SORT_DESC,
                    'warehouse_id' => SORT_ASC,
                    'item_code' => SORT_ASC,
                ],
                'attributes' => [
                    'report_year', 'report_month', 'warehouse_id',
                    'item_code',
                    'opening_qty', 'opening_value',
                    'in_qty', 'in_value',
                    'out_sub_qty', 'out_hosp_qty',
                    'total_out_qty', 'total_out_value',
                    'closing_qty', 'closing_value',
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'r.report_year' => $this->report_year,
            'r.report_month' => $this->report_month,
            'r.warehouse_id' => $this->warehouse_id,
        ]);

        if ($this->item_code !== null && $this->item_code !== '') {
            $query->andFilterWhere(['like', 'r.item_code', $this->item_code]);
        }

        $query->leftJoin('stock_item si', 'si.item_code = r.item_code');

        if ($this->q !== null && $this->q !== '') {
            $query->andFilterWhere([
                'or',
                ['like', 'r.item_code', $this->q],
                ['like', 'si.item_name', $this->q],
            ]);
        }

        if ($this->category_id !== null && $this->category_id !== '') {
            $query->andFilterWhere(['si.category_id' => $this->category_id]);
        }

        return $dataProvider;
    }
}
