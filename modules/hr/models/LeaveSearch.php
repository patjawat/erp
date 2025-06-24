<?php

namespace app\modules\hr\models;

use yii\base\Model;
use app\modules\hr\models\Leave;
use yii\data\ActiveDataProvider;
use app\components\DateFilterHelper;

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
            [['date_filter', 'on_holidays', 'leave_type_id', 'data_json', 'date_start', 'date_end', 'created_at', 'updated_at', 'deleted_at', 'emp_id', 'thai_year', 'status', 'q', 'q_department'], 'safe'],
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
                'attributes' => [
                'total_days', // สมมุติว่า field ชื่อ days คือจำนวนวันลา
        ],
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

        // if ($this->date_filter) {
        //     $range = DateFilterHelper::getRange($this->date_filter);
        //     if ($range) {
        //         $date_start = date('Y-m-d', strtotime($range[0]));
        //         $date_end = date('Y-m-d', strtotime($range[1]));
        //         $query->andWhere(['>=', 'date_start', $date_start]);
        //         $query->andWhere(['<=', 'date_end', $date_end]);
        //     }
        // }


        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'leave.emp_id' => $this->emp_id,
            'on_holidays' => $this->on_holidays,
            'thai_year' => $this->thai_year,
            'leave_time_type' => $this->leave_time_type,
            'leave.status' => $this->status,
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
