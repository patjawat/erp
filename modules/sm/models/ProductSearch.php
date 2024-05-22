<?php

namespace app\modules\sm\models;

use app\modules\sm\models\Product;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductSearch represents the model behind the search form of `app\modules\sm\models\Product`.
 */
class ProductSearch extends Product
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'purchase', 'department', 'life', 'on_year', 'dep_id', 'depre_type', 'budget_year', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'asset_group', 'asset_item', 'code', 'fsn_number', 'receive_date', 'repair', 'owner', 'device_items', 'asset_status', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['price'], 'number'],
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
        $query = Product::find();

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
            'qty' => $this->qty,
            'receive_date' => $this->receive_date,
            'price' => $this->price,
            'purchase' => $this->purchase,
            'department' => $this->department,
            'life' => $this->life,
            'on_year' => $this->on_year,
            'dep_id' => $this->dep_id,
            'depre_type' => $this->depre_type,
            'budget_year' => $this->budget_year,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'asset_group', $this->asset_group])
            ->andFilterWhere(['like', 'asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'fsn_number', $this->fsn_number])
            ->andFilterWhere(['like', 'owner', $this->owner])
            ->andFilterWhere(['like', 'device_items', $this->device_items])
            ->andFilterWhere(['like', 'asset_status', $this->asset_status])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
