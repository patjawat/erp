<?php

namespace app\modules\approve\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\approve\models\Approve;

/**
 * ApproveSearch represents the model behind the search form of `app\modules\approve\models\Approve`.
 */
class ApproveSearch extends Approve
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'emp_id', 'level', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['from_id', 'name', 'title', 'data_json', 'status', 'comment', 'created_at', 'updated_at', 'deleted_at','q','leave_type_id','date_start','date_end','q_department','thai_year'], 'safe'],
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
        $query = Approve::find();

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
            'approve.emp_id' => $this->approve_emp_id,
            'level' => $this->level,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'from_id', $this->from_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'approve.status', $this->status])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
