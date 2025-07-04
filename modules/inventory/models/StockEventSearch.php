<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventory\models\StockEvent;

/**
 * StockEventSearch represents the model behind the search form of `app\modules\inventory\models\StockEvent`.
 */
class StockEventSearch extends StockEvent
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'from_warehouse_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['name', 'code', 'asset_item', 'receive_type', 'movement_date', 'lot_number', 'category_id', 'order_status', 'ref', 'data_json', 'created_at', 'updated_at','q','asset_type_name', 'date_start',
            'date_end','transaction_type','q_month','receive_month','date_filter'], 'safe'],
            [['total_price', 'unit_price'], 'number'],
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
        $query = StockEvent::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => 'SORT_DESC',
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'from_warehouse_id' => $this->from_warehouse_id,
            'transaction_type' => $this->transaction_type,
            'qty' => $this->qty,
            'total_price' => $this->total_price,
            'unit_price' => $this->unit_price,
            'movement_date' => $this->movement_date,
            'thai_year' => $this->thai_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'receive_type', $this->receive_type])
            ->andFilterWhere(['like', 'lot_number', $this->lot_number])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'order_status', $this->order_status])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }

    public  function getDemo()
    {
        
        return 100;
    }
}
