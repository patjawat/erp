<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventory\models\StockSummary;

/**
 * StockEventSearch represents the model behind the search form of `app\modules\inventory\models\StockEvent`.
 */
class StockSummarySearch extends StockSummary
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receive_date','receive_month','thai_year','type_code'], 'safe'],
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
        $query = StockSummary::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'receive_date' => 'SORT_DESC',
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
            'receive_date' => $this->receive_date,
            'receive_month' => $this->receive_month,
            'thai_year' => $this->thai_year,
            'type_code' => $this->type_code,
        ]);

        // $query->andFilterWhere(['like', 'receive_date', $this->name]);

        return $dataProvider;
    }
}
