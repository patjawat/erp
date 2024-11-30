<?php

namespace app\modules\hr\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\LeaveSummary;

class LeaveSummarySearch extends LeaveSummary
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active','m1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12'], 'integer'],
            [['thai_year'], 'safe'],
            [['code', 'title'], 'string', 'max' => 255],
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
        $query = LeaveSummary::find();

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
            'code' => $this->code,
            'thai_year' => $this->thai_year,
            'active' => $this->active,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
