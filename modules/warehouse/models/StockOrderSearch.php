<?php

namespace app\modules\warehouse\models;

use app\modules\warehouse\models\StockOrder;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StockOrderSearch represents the model behind the search form of `app\modules\warehouse\models\StockOrder`.
 */
class StockOrderSearch extends StockOrder
{
    /**
     * {@inheritdoc}
     */
    public $qty_check;

    public function rules()
    {
        return [
            [['id', 'product_id', 'from_warehouse_id', 'to_warehouse_id', 'qty'], 'integer'],
            [['name', 'po_number', 'rc_number', 'movement_type', 'movement_date', 'lot_number', 'expiry_date', 'qty_check'], 'safe'],
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
        $query = StockOrder::find();

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
            'product_id' => $this->product_id,
            'from_warehouse_id' => $this->from_warehouse_id,
            'to_warehouse_id' => $this->to_warehouse_id,
            'qty' => $this->qty,
            'movement_date' => $this->movement_date,
            'expiry_date' => $this->expiry_date,
        ]);

        $query
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'po_number', $this->po_number])
            ->andFilterWhere(['like', 'rc_number', $this->rc_number])
            ->andFilterWhere(['like', 'movement_type', $this->movement_type])
            ->andFilterWhere(['like', 'lot_number', $this->lot_number]);

        return $dataProvider;
    }
}
