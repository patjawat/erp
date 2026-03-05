<?php

namespace app\modules\jd\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class JdTemplateSearch extends JdTemplate
{
    public function rules()
    {
        return [
            [['id', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['name', 'position_code', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = JdTemplate::find()->with(['positionName', 'sections']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'is_active' => $this->is_active,
            'position_code' => $this->position_code,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
