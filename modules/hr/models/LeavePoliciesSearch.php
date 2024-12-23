<?php

namespace app\modules\hr\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\LeavePolicies;

/**
 * LeavePoliciesSearch represents the model behind the search form of `app\modules\hr\models\LeavePolicies`.
 */
class LeavePoliciesSearch extends LeavePolicies
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'days', 'max_days', 'accumulation', 'leave_before_days', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['position_type_id', 'leave_type_id', 'additional_rules', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['month_of_service', 'year_of_service'], 'number'],
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
        $query = LeavePolicies::find();

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
            'month_of_service' => $this->month_of_service,
            'year_of_service' => $this->year_of_service,
            'days' => $this->days,
            'max_days' => $this->max_days,
            'accumulation' => $this->accumulation,
            'leave_before_days' => $this->leave_before_days,
            'thai_year' => $this->thai_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'position_type_id', $this->position_type_id])
            ->andFilterWhere(['like', 'leave_type_id', $this->leave_type_id])
            ->andFilterWhere(['like', 'additional_rules', $this->additional_rules])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
