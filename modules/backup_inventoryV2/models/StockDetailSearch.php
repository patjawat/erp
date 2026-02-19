<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventoryV2\models\StockDetail;

/**
 * StockDetailSearch represents the model behind the search form of `app\modules\inventoryV2\models\StockDetail`.
 */
class StockDetailSearch extends StockDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'stock_order_id', 'item_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['qty', 'unit_price'], 'number'],
            [['lot_number', 'expiry_date', 'ref', 'data_json'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = StockDetail::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'stock_order_id' => $this->stock_order_id,
            'item_id' => $this->item_id,
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'expiry_date' => $this->expiry_date,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'lot_number', $this->lot_number])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
