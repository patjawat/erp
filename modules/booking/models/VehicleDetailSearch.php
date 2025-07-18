<?php

namespace app\modules\booking\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\booking\models\VehicleDetail;

/**
 * VehicleDetailSearch represents the model behind the search form of `app\modules\booking\models\VehicleDetail`.
 */
class VehicleDetailSearch extends VehicleDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'vehicle_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'license_plate', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'driver_id', 'data_json', 'created_at', 'updated_at', 'deleted_at','emp_id','q','thai_year','date_filter'], 'safe'],
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
        $query = VehicleDetail::find();

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
            'vehicle_id' => $this->vehicle_id,
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
            ->andFilterWhere(['like', 'license_plate', $this->license_plate])
            ->andFilterWhere(['like', 'vehicle_detail.status', $this->status])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'driver_id', $this->driver_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
