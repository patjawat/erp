<?php

namespace app\modules\booking\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\booking\models\BookingCarsItems;

/**
 * BookingCarsItemsSearch represents the model behind the search form of `app\modules\booking\models\BookingCarsItems`.
 */
class BookingCarsItemsSearch extends BookingCarsItems
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'asset_item_id', 'active', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['car_type', 'license_plate', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
        $query = BookingCarsItems::find();

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
            'asset_item_id' => $this->asset_item_id,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'car_type', $this->car_type])
            ->andFilterWhere(['like', 'license_plate', $this->license_plate])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
