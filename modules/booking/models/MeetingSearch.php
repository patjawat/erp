<?php

namespace app\modules\booking\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\booking\models\Meeting;

/**
 * MeetingSearch represents the model behind the search form of `app\modules\booking\models\Meeting`.
 */
class MeetingSearch extends Meeting
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'thai_year', 'document_id', 'emp_number', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'code','room_id', 'title', 'date_start', 'date_end', 'time_start', 'time_end', 'urgent', 'status', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at','q','date_filter'], 'safe'],
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
        $query = Meeting::find();

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
            'room_id' => $this->room_id,
            'thai_year' => $this->thai_year,
            'document_id' => $this->document_id,
            'emp_number' => $this->emp_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'urgent', $this->urgent])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
