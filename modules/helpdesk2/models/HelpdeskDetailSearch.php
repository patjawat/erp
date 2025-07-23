<?php

namespace app\modules\helpdesk2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\helpdesk\models\HelpdeskDetail;

/**
 * HelpdeskDetailSearch represents the model behind the search form of `app\modules\helpdesk\models\HelpdeskDetail`.
 */
class HelpdeskDetailSearch extends HelpdeskDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'helpdesk_id', 'move_out', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'name', 'code', 'title', 'data_json', 'status', 'rating', 'created_at', 'updated_at','emp_id'], 'safe'],
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
        $query = HelpdeskDetail::find();

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
            'helpdesk_id' => $this->helpdesk_id,
            'emp_id' => $this->emp_id,
            'move_out' => $this->move_out,
            'thai_year' => $this->thai_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'rating', $this->rating]);

        return $dataProvider;
    }
}
