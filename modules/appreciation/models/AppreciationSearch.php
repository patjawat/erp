<?php

namespace app\modules\appreciation\models;

use yii\data\ActiveDataProvider;

class AppreciationSearch extends Appreciation
{
    public function search($params)
    {
        $query = Appreciation::find()
            ->with(['fromEmp', 'toEmp'])
            ->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'defaultPageSize' => 20,
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'from_emp_id' => $this->from_emp_id,
            'to_emp_id' => $this->to_emp_id,
            'badge_type' => $this->badge_type,
        ]);

        return $dataProvider;
    }
}
