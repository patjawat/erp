<?php

namespace app\modules\inventory\models;

use app\modules\inventory\models\Warehouse;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * WarehouseSearch represents the model behind the search form of `app\modules\inventory\models\Warehouse`.
 */
class WarehouseSearch extends Warehouse
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_main'], 'integer'],
            [['warehouse_name', 'warehouse_code','category_id'], 'safe'],
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
        $query = Warehouse::find();

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
            'is_main' => $this->is_main,
        ]);

        $query
            ->andFilterWhere(['like', 'warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code]);

        return $dataProvider;
    }
}
