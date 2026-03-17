<?php

namespace app\modules\amSurvey\models;

use yii\data\ActiveDataProvider;

class AssetSurveySearch extends AssetSurvey
{
    public function rules()
    {
        return [
            [['id', 'survey_year', 'department_id', 'created_by'], 'integer'],
            [['survey_name', 'status'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = AssetSurvey::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['survey_year' => SORT_DESC, 'id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['survey_year' => $this->survey_year]);
        $query->andFilterWhere(['status' => $this->status]);
        $query->andFilterWhere(['like', 'survey_name', $this->survey_name]);

        return $dataProvider;
    }
}
