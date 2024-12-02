<?php

namespace app\modules\hr\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\LeaveRole;

/**
 * LeaveRoleSearch represents the model behind the search form of `app\modules\hr\models\LeaveRole`.
 */
class LeaveRoleSearch extends LeaveRole
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'emp_id', 'thai_year', 'work_year', 'max_point', 'point','point_use'], 'integer'],
            [['data_json', 'position_type_id','q'], 'safe'],
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
        $query = LeaveRole::find();

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
            'thai_year' => $this->thai_year,
            // 'work_year' => $this->work_year,
            // 'last_days' => $this->last_days,
            'max_point' => $this->max_point,
            'point' => $this->point,
            'point' => $this->point,
        ]);

        $query->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'position_type_id', $this->position_type_id]);

        return $dataProvider;
    }
}
