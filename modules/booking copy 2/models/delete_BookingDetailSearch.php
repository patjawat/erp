<?php

namespace app\modules\booking\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\booking\models\BookingDetail;

/**
 * BookingDetailSearch represents the model behind the search form of `app\modules\booking\models\BookingDetail`.
 */
class BookingDetailSearch extends BookingDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'emp_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'name', 'booking_id', 'ambulance_type', 'license_plate', 'driver_id', 'date_start', 'time_start', 'date_end', 'time_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['mileage_start', 'mileage_end', 'distance_km', 'oil_price', 'oil_liter'], 'number'],
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
        $query = BookingDetail::find();

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
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'mileage_start' => $this->mileage_start,
            'mileage_end' => $this->mileage_end,
            'distance_km' => $this->distance_km,
            'oil_price' => $this->oil_price,
            'oil_liter' => $this->oil_liter,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'booking_id', $this->booking_id])
            ->andFilterWhere(['like', 'ambulance_type', $this->ambulance_type])
            ->andFilterWhere(['like', 'license_plate', $this->license_plate])
            ->andFilterWhere(['like', 'driver_id', $this->driver_id])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
