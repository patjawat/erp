<?php

namespace app\modules\sm\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\sm\models\SupRequest;

/**
 * SupRequestSearch represents the model behind the search form of `app\modules\sm\models\SupRequest`.
 */
class SupRequestSearch extends SupRequest
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'req_code', 'req_date', 'req_detail', 'req_vendor', 'req_amount', 'req_status', 'req_dep', 'data_json', 'updated_at', 'created_at'], 'safe'],
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
        $query = SupRequest::find();

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
            'req_date' => $this->req_date,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'req_code', $this->req_code])
            ->andFilterWhere(['like', 'req_detail', $this->req_detail])
            ->andFilterWhere(['like', 'req_vendor', $this->req_vendor])
            ->andFilterWhere(['like', 'req_amount', $this->req_amount])
            ->andFilterWhere(['like', 'req_status', $this->req_status])
            ->andFilterWhere(['like', 'req_dep', $this->req_dep])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
