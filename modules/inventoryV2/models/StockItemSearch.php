<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventoryV2\models\StockItem;

/**
 * StockItemSearch represents the model behind the search form of `app\modules\inventoryV2\models\StockItem`.
 */
class StockItemSearch extends StockItem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','is_asset', 'is_innovation', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['item_code', 'item_name', 'ref', 'data_json','category_id','q'], 'safe'],
            [['min_qty', 'max_qty'], 'number'],
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
        $query = StockItem::find();

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
            'category_id' => $this->category_id,
            'min_qty' => $this->min_qty,
            'max_qty' => $this->max_qty,
            'is_asset' => $this->is_asset,
            'is_innovation' => $this->is_innovation,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'item_code', $this->item_code])
            ->andFilterWhere(['like', 'item_name', $this->item_name])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
