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
            [['id', 'leave_type_id', 'position_type_id', 'days_available', 'year_service', 'point', 'point_days', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'leave_type_id' => $this->leave_type_id,
            'position_type_id' => $this->position_type_id,
            'days_available' => $this->days_available,
            'year_service' => $this->year_service,
            'point' => $this->point,
            'point_days' => $this->point_days,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
