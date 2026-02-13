<?php

namespace app\modules\health\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\health\models\HealthScreen;

/**
 * HealthScreenSearch represents the model behind the search form of `app\modules\health\models\HealthScreen`.
 */
class HealthScreenSearch extends HealthScreen
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'thai_year', 'emp_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['date_checkup', 'data_json', 'created_at', 'updated_at', 'deleted_at','weight','height','health_status'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = HealthScreen::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'thai_year' => $this->thai_year,
            'emp_id' => $this->emp_id,
            'weight' => $this->weight,
            'height' => $this->height,
            'health_status' => $this->health_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'date_checkup', $this->date_checkup]);

        return $dataProvider;
    }
}
