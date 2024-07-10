<?php

namespace app\modules\inventory\models;

use app\modules\inventory\models\Stock;
use yii\base\Model;
use yii\data\ActiveDataProvider;

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
            [['stock_id', 'product_id', 'warehouse_id', 'qty'], 'integer'],
            [['lot_number', 'expiry_date', 'po_number'], 'safe'],
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
            'stock_id' => $this->stock_id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'qty' => $this->qty,
            'expiry_date' => $this->expiry_date,
        ]);

        $query->andFilterWhere(['like', 'lot_number', $this->lot_number]);

        return $dataProvider;
    }
}
