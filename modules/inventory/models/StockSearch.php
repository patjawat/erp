<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventory\models\Stock;

/**
 * StockSearch represents the model behind the search form of `app\modules\inventory\models\Stock`.
 */
class StockSearch extends Stock
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'asset_item', 'from_warehouse_id', 'to_warehouse_id', 'qty', 'created_by', 'updated_by'], 'integer'],
            [['name', 'rc_number', 'po_number', 'movement_type', 'receive_type', 'movement_date', 'lot_number', 'expiry_date', 'category_id', 'ref', 'data_json', 'created_at', 'updated_at','q'], 'safe'],
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
        $query = Stock::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'asset_item' => $this->asset_item,
            'from_warehouse_id' => $this->from_warehouse_id,
            'to_warehouse_id' => $this->to_warehouse_id,
            'qty' => $this->qty,
            'total_price' => $this->total_price,
            'unit_price' => $this->unit_price,
            'movement_date' => $this->movement_date,
            'expiry_date' => $this->expiry_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'rc_number', $this->rc_number])
            ->andFilterWhere(['like', 'po_number', $this->po_number])
            ->andFilterWhere(['like', 'movement_type', $this->movement_type])
            ->andFilterWhere(['like', 'receive_type', $this->receive_type])
            ->andFilterWhere(['like', 'lot_number', $this->lot_number])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
