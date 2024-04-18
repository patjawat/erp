<?php

namespace app\modules\sm\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\sm\models\Supregister;

/**
 * SupregisterSearch represents the model behind the search form of `app\modules\sm\models\Supregister`.
 */
class SupregisterSearch extends Supregister
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'regisnumber', 'start_date', 'price', 'status', 'dep_code', 'data_json', 'updated_at', 'created_at'], 'safe'],
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
        $query = Supregister::find();

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
            'start_date' => $this->start_date,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'regisnumber', $this->regisnumber])
            ->andFilterWhere(['like', 'price', $this->price])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'dep_code', $this->dep_code])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
