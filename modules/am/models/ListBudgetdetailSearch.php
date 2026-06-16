<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ListBudgetdetailSearch extends ListBudgetdetail
{
    public function rules()
    {
        return [
            [['code', 'title', 'description'], 'safe'],
            [['active'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = ListBudgetdetail::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['active' => $this->active]);
        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
