<?php

namespace app\modules\warehouse\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\warehouse\models\Whdep;

/**
 * WhdepSearch represents the model behind the search form of `app\modules\warehouse\models\Whdep`.
 */
class WhdepSearch extends Whdep
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'depsup_code', 'depsup_detail', 'depsup_type', 'depsup_store', 'depsup_unit', 'data_json', 'updated_at', 'created_at'], 'safe'],
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
        $query = Whdep::find();

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
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'depsup_code', $this->depsup_code])
            ->andFilterWhere(['like', 'depsup_detail', $this->depsup_detail])
            ->andFilterWhere(['like', 'depsup_type', $this->depsup_type])
            ->andFilterWhere(['like', 'depsup_store', $this->depsup_store])
            ->andFilterWhere(['like', 'depsup_unit', $this->depsup_unit])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
