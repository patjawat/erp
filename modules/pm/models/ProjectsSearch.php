<?php

namespace app\modules\pm\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProjectsSearch — ค้นหา/กรองรายการโครงการ
 */
class ProjectsSearch extends Projects
{
    public function rules()
    {
        return [
            [['id', 'thai_year', 'department_id'], 'integer'],
            [['name', 'code', 'status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Projects::find()->andWhere(['deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'thai_year' => $this->thai_year,
            'department_id' => $this->department_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code]);

        return $dataProvider;
    }
}
