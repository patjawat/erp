<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventory\models\Store;

/**
 * StoreSearch represents the model behind the search form of `app\modules\inventory\models\Store`.
 */
class StoreSearch extends Store
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'stock_qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['name', 'asset_item', 'ref', 'data_json', 'created_at', 'updated_at','q'], 'safe'],
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
        $query = Store::find();

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
            'stock_qty' => $this->stock_qty,
            'thai_year' => $this->thai_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
