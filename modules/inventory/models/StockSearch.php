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
            [['id', 'warehouse_id', 'qty', 'created_by'], 'integer'],
            [['name', 'code', 'asset_item', 'data_json', 'created_at', 'updated_at'], 'safe'],
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
            'warehouse_id' => $this->warehouse_id,
            'qty' => $this->qty,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
