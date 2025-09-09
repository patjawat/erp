<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StockTransactionSearch represents the model behind the search form of `app\modules\inventory\models\StockTransaction`.
 */
class StockTransactionSearch extends StockTransaction
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['asset_type', 'category_id', 'asset_item', 'asset_name', 'unit', 'code', 'po_number', 'from_warehouse_type', 'from_warehouse_name', 'warehouse_type', 'warehouse_name', 'transaction_type', 'order_status', 'movement_date', 'created_at','date_start','date_end','date_filter'], 'safe'],
            [['warehouse_id', 'thai_year', 'order_month'], 'integer'],
            [['qty', 'unit_price', 'total_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = StockTransaction::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20, // ปรับได้ตามต้องการ
            ],
            'sort' => [
                'defaultOrder' => ['movement_date' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        
        // grid filtering conditions
        $query->andFilterWhere([
            'warehouse_id' => $this->warehouse_id,
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'thai_year' => $this->thai_year,
            'order_month' => $this->order_month,
            'movement_date' => $this->movement_date,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'asset_type', $this->asset_type])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'asset_name', $this->asset_name])
            ->andFilterWhere(['like', 'unit', $this->unit])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'po_number', $this->po_number])
            ->andFilterWhere(['like', 'from_warehouse_type', $this->from_warehouse_type])
            ->andFilterWhere(['like', 'from_warehouse_name', $this->from_warehouse_name])
            ->andFilterWhere(['like', 'warehouse_type', $this->warehouse_type])
            ->andFilterWhere(['like', 'warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'transaction_type', $this->transaction_type])
            ->andFilterWhere(['like', 'order_status', $this->order_status]);

        return $dataProvider;
    }



}
