<?php


namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\db\Expression;
use yii\data\ActiveDataProvider;

/**
 * StockItemSearch represents the model behind the search form of `app\modules\sm\models\StockItem`.
 */
class StockItemSearch extends StockItem
{
    public function rules()
    {
        return [
            [['id', 'active'], 'integer'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description', 'data_json', 'q_category', 'q', 'metter_type', 'unit','innovation_account','group_id'], 'safe'],
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
        $query = StockItem::find();

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
            'id' => $this->id,
            'active' => $this->active,
            'group_id' => $this->group_id,
            'qty_min' => $this->qty_min,
            'qty_max' => $this->qty_max,
        ]);
        $query
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', new Expression("JSON_EXTRACT(data_json, '$.metter_type')"), $this->metter_type])
            // ->andFilterWhere(['like', new Expression("JSON_EXTRACT(data_json, '$.innovation_account')"), $this->innovation_account])
            ->andFilterWhere(['like', new Expression("JSON_EXTRACT(data_json, '$.unit')"), $this->unit]);

        return $dataProvider;
    }
}
