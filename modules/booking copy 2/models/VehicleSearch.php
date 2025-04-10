<?php

namespace app\modules\booking\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\booking\models\Vehicle;

/**
 * VehicleSearch represents the model behind the search form of `app\modules\booking\models\Vehicle`.
 */
class VehicleSearch extends Vehicle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'thai_year', 'go_type', 'document_id', 'owner_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'code', 'car_type_id','refer_type', 'urgent', 'license_plate', 'location', 'reason', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'driver_id', 'leader_id', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at','q','q_department'], 'safe'],
            [['oil_price', 'oil_liter'], 'number'],
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
        $query = Vehicle::find();

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
            'thai_year' => $this->thai_year,
            'go_type' => $this->go_type,
            'oil_price' => $this->oil_price,
            'oil_liter' => $this->oil_liter,
            'document_id' => $this->document_id,
            'owner_id' => $this->owner_id,
            // 'date_start' => $this->date_start,
            // 'date_end' => $this->date_end,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'car_type_id', $this->car_type_id])
            ->andFilterWhere(['like', 'urgent', $this->urgent])
            ->andFilterWhere(['like', 'license_plate', $this->license_plate])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'vehicle.status', $this->status])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'driver_id', $this->driver_id])
            ->andFilterWhere(['like', 'leader_id', $this->leader_id])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
