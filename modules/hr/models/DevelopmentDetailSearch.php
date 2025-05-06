<?php

namespace app\modules\hr\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\DevelopmentDetail;

/**
 * DevelopmentDetailSearch represents the model behind the search form of `app\modules\hr\models\DevelopmentDetail`.
 */
class DevelopmentDetailSearch extends DevelopmentDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'development_id', 'qty', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['category_id', 'name', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['price'], 'number'],
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
        $query = DevelopmentDetail::find();

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
            'development_id' => $this->development_id,
            'qty' => $this->qty,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
