<?php

namespace app\modules\health\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\health\models\HealthLab;

/**
 * HealthLabSearch represents the model behind the search form of `app\modules\health\models\HealthLab`.
 */
class HealthLabSearch extends HealthLab
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lab_code', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['lab_name', 'lab_type', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['lab_price'], 'number'],
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
        $query = HealthLab::find();

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
            'lab_code' => $this->lab_code,
            'lab_price' => $this->lab_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'lab_name', $this->lab_name])
            ->andFilterWhere(['like', 'lab_type', $this->lab_type])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
