<?php

namespace app\modules\lm\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\lm\models\LeavePermission;

/**
 * LeavePermissionSearch represents the model behind the search form of `app\modules\lm\models\LeavePermission`.
 */
class LeavePermissionSearch extends LeavePermission
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'service_time', 'point', 'last_point', 'new_point', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
        $query = LeavePermission::find();

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
            'service_time' => $this->service_time,
            'point' => $this->point,
            'last_point' => $this->last_point,
            'new_point' => $this->new_point,
            'thai_year' => $this->thai_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
