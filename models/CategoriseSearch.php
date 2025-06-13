<?php

namespace app\models;

use app\models\Categorise;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CategoriseSearch represents the model behind the search form of `app\models\Categorise`.
 */
class CategoriseSearch extends Categorise
{
    /**
     * {@inheritdoc}
     */
    public $q;
    
    public function rules()
    {
        return [
            [['id', 'active'], 'integer'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description', 'data_json', 'position_group', 'position_type', 'qty'], 'safe'],
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
        $query = Categorise::find();

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
            'code' => $this->code,
            'active' => $this->active,
        ]);

        $query
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'categorise.title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
