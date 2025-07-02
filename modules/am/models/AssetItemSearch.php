<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\am\models\AssetItem;

/**
 * AssetItemSearch represents the model behind the search form of `app\modules\am\models\AssetItem`.
 */
class AssetItemSearch extends AssetItem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ref', 'asset_group_id', 'asset_type_id', 'asset_category_id', 'title', 'fsn', 'description', 'status', 'data_json', 'created_at', 'updated_at','q'], 'safe'],
            [['price', 'depreciation'], 'number'],
            [['service_life', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
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
        $query = AssetItem::find();

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
            'price' => $this->price,
            'depreciation' => $this->depreciation,
            'service_life' => $this->service_life,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            // 'asset_item.deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'asset_group_id', $this->asset_group_id])
            ->andFilterWhere(['like', 'asset_type_id', $this->asset_type_id])
            ->andFilterWhere(['like', 'asset_category_id', $this->asset_category_id])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'fsn', $this->fsn])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
