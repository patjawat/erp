<?php

namespace app\modules\hr\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\Development;

/**
 * DevelopmentSearch represents the model behind the search form of `app\modules\hr\models\Development`.
 */
class DevelopmentSearch extends Development
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'document_id', 'assigned_to', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['development_type_id', 'topic', 'description', 'location', 'location_org', 'province_name', 'status', 'vehicle_type_id', 'claim_type', 'time_slot', 'date_start', 'time_start', 'date_end', 'time_end', 'driver_id', 'leader_id', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Development::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'document_id' => $this->document_id,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'assigned_to' => $this->assigned_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);


            $query->andFilterWhere(['like', 'topic', $this->topic])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'driver_id', $this->driver_id])
            ->andFilterWhere(['like', 'leader_id', $this->leader_id])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
