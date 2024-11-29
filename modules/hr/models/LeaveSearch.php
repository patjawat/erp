<?php

namespace app\modules\hr\models;

use yii\base\Model;
use app\modules\hr\models\Leave;
use yii\data\ActiveDataProvider;

/**
 * LeaveSearch represents the model behind the search form of `app\modules\lm\models\Leave`.
 */
class LeaveSearch extends Leave
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['leave_type_id', 'data_json', 'start_date', 'start_end', 'created_at', 'updated_at', 'deleted_at','emp_id','thai_year','status','q'], 'safe'],
            [['leave_time_type'], 'number'],
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
        $query = Leave::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            'defaultOrder' => [
                'id' => SORT_DESC, // เรียงลำดับ id จากมากไปน้อย
            ],
        ],
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
            'leave_time_type' => $this->leave_time_type,
            'status' => $this->status,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        return $dataProvider;
    }
}
