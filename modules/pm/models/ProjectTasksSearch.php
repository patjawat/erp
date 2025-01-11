<?php

namespace app\modules\pm\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\pm\models\ProjectTasks;

/**
 * ProjectTasksSearch represents the model behind the search form of `app\modules\pm\models\ProjectTasks`.
 */
class ProjectTasksSearch extends ProjectTasks
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'emp_id'], 'integer'],
            [['task_name', 'status', 'due_date'], 'safe'],
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
        $query = ProjectTasks::find();

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
            'project_id' => $this->project_id,
            'emp_id' => $this->emp_id,
            'due_date' => $this->due_date,
        ]);

        $query->andFilterWhere(['like', 'task_name', $this->task_name])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
