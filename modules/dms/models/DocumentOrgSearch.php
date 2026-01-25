<?php

namespace app\modules\dms\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\dms\models\DocumentOrg;

/**
 * DocumentOrgSearch represents the model behind the search form of `app\modules\dms\models\DocumentOrg`.
 */
class DocumentOrgSearch extends DocumentOrg
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'active', 'qty_min', 'qty_max'], 'integer'],
            [['sort', 'ref', 'group_id', 'category_id', 'code', 'emp_id', 'name', 'title', 'description', 'data_json', 'ma_items','q'], 'safe'],
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
        $query = DocumentOrg::find();

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
            'qty' => $this->qty,
            'active' => $this->active,
            'qty_min' => $this->qty_min,
            'qty_max' => $this->qty_max,
        ]);

        $query->andFilterWhere(['like', 'sort', $this->sort])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'group_id', $this->group_id])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'ma_items', $this->ma_items]);

        return $dataProvider;
    }
}
