<?php

namespace app\modules\booking\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\booking\models\BookingCar;

/**
 * BookingCarSearch represents the model behind the search form of `app\modules\booking\models\BookingCar`.
 */
class BookingCarSearch extends BookingCar
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'document_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['booking_type', 'urgent', 'asset_item_id', 'location', 'data_json', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'driver_id', 'leader_id', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
        $query = BookingCar::find();

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
            'document_id' => $this->document_id,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'booking_type', $this->booking_type])
            ->andFilterWhere(['like', 'urgent', $this->urgent])
            ->andFilterWhere(['like', 'asset_item_id', $this->asset_item_id])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'driver_id', $this->driver_id])
            ->andFilterWhere(['like', 'leader_id', $this->leader_id]);

        return $dataProvider;
    }
}
