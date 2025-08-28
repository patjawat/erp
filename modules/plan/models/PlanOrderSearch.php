<?php

namespace app\modules\plan\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\plan\models\PlanOrder;

/**
 * PlanOrderSearch represents the model behind the search form of `app\modules\plan\models\PlanOrder`.
 */
class PlanOrderSearch extends PlanOrder
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['plan_group_id', 'title', 'description', 'start_date', 'end_date', 'status', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at','plan_type_id','asset_type_id','asset_category_id','plan_category_id','thai_year'], 'safe'],
            [['budget_total', 'budget_used'], 'number'],
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
        $query = PlanOrder::find();

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
            'thai_year' => $this->thai_year,
            'budget_total' => $this->budget_total,
            'budget_used' => $this->budget_used,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
            'plan_type_id' => $this->plan_type_id,
            'asset_type_id' => $this->asset_type_id,
            'asset_category_id' => $this->asset_category_id,
            'plan_category_id' => $this->plan_category_id
        ]);

        $query->andFilterWhere(['like', 'plan_group_id', $this->plan_group_id])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
