<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\am\models\Asset2;

/**
 * Asset2Search represents the model behind the search form of `app\modules\am\models\Asset`.
 */
class Asset2Search extends Asset
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'purchase', 'department', 'life', 'on_year', 'dep_id', 'depre_type', 'budget_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['q_department','ref', 'asset_group', 'asset_name', 'asset_item', 'code', 'fsn_number', 'receive_date', 'owner', 'asset_status', 'data_json', 'device_items', 'updated_at', 'created_at', 'deleted_at', 'license_plate', 'car_type'], 'safe'],
            [['price'], 'number'],
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
        $query = Asset2::find();

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
            'qty' => $this->qty,
            'receive_date' => $this->receive_date,
            'price' => $this->price,
            'purchase' => $this->purchase,
            'department' => $this->department,
            'life' => $this->life,
            'on_year' => $this->on_year,
            'dep_id' => $this->dep_id,
            'depre_type' => $this->depre_type,
            'budget_year' => $this->budget_year,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'asset_group', $this->asset_group])
            ->andFilterWhere(['like', 'asset_name', $this->asset_name])
            ->andFilterWhere(['like', 'asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'fsn_number', $this->fsn_number])
            ->andFilterWhere(['like', 'owner', $this->owner])
            ->andFilterWhere(['like', 'asset_status', $this->asset_status])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'device_items', $this->device_items])
            ->andFilterWhere(['like', 'license_plate', $this->license_plate])
            ->andFilterWhere(['like', 'car_type', $this->car_type]);

        return $dataProvider;
    }
}
