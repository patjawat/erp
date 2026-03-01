<?php

namespace app\modules\leave\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class LeaveSummarySearch extends LeaveSummary
{
    public static function tableName()
    {
        return 'leave_summary';
    }

    public function rules()
    {
        return [
            [['active','m1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12'], 'integer'],
            [['thai_year'], 'safe'],
            [['code', 'title'], 'string', 'max' => 255],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = LeaveSummary::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'code' => $this->code,
            'thai_year' => $this->thai_year,
            'active' => $this->active,
        ]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        return $dataProvider;
    }
}
