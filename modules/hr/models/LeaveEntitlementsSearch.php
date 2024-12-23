<?php

namespace app\modules\hr\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\LeaveEntitlements;

/**
 * LeaveEntitlementsSearch represents the model behind the search form of `app\modules\hr\models\LeaveEntitlements`.
 */
class LeaveEntitlementsSearch extends LeaveEntitlements
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'month_of_service', 'year_of_service', 'days', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['emp_id', 'position_type_id', 'leave_type_id', 'data_json', 'created_at', 'updated_at', 'deleted_at','q','q_department'], 'safe'],
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
        $query = LeaveEntitlements::find();

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
            'emp_id' => $this->emp_id,
            'month_of_service' => $this->month_of_service,
            'year_of_service' => $this->year_of_service,
            'days' => $this->days,
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
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
