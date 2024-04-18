<?php

namespace app\modules\warehouse\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\warehouse\models\Whcheck;

/**
 * WhcheckSearch represents the model behind the search form of `app\modules\warehouse\models\Whcheck`.
 */
class WhcheckSearch extends Whcheck
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'check_code', 'check_date', 'check_type', 'check_store', 'check_from', 'check_hr', 'check_status', 'data_json', 'updated_at', 'created_at'], 'safe'],
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
        $query = Whcheck::find();

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
            'check_date' => $this->check_date,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'check_code', $this->check_code])
            ->andFilterWhere(['like', 'check_type', $this->check_type])
            ->andFilterWhere(['like', 'check_store', $this->check_store])
            ->andFilterWhere(['like', 'check_from', $this->check_from])
            ->andFilterWhere(['like', 'check_hr', $this->check_hr])
            ->andFilterWhere(['like', 'check_status', $this->check_status])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
