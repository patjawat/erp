<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventoryV2\models\StockOrder;

/**
 * StockOrderSearch represents the model behind the search form of `app\modules\inventoryV2\models\StockOrder`.
 */
class StockOrderSearch extends StockOrder
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'to_warehouse_id', 'contact_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['order_no', 'order_type', 'order_date', 'status', 'ref', 'data_json'], 'safe'],
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
        $query = StockOrder::find();

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
            'order_date' => $this->order_date,
            'warehouse_id' => $this->warehouse_id,
            'to_warehouse_id' => $this->to_warehouse_id,
            'contact_id' => $this->contact_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'order_no', $this->order_no])
            ->andFilterWhere(['like', 'order_type', $this->order_type])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
