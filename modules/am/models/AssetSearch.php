<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\am\models\Asset;

/**
 * AssetSearch represents the model behind the search form of `app\modules\am\models\Asset`.
 */
class AssetSearch extends Asset
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'life', 'department', 'depre_type', 'budget_year', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'code', 'receive_date', 'data_json', 'updated_at', 'created_at','fsn_auto','asset_item','asset_group','asset_type','q','purchase','on_year','owner','price1','price2','q_department'.'q_date','q_receive_date','q_month','q_year','budget_type','method_get','asset_status'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Asset::find();

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
            'asset_group' => $this->asset_group,
            'receive_date' => $this->receive_date,
            'price' => $this->price,
            'life' => $this->life,
            'department' => $this->department,
            'purchase' => $this->purchase,
            'asset_item' => $this->asset_item,
            'on_year' => $this->on_year,
            'owner' => $this->owner,
            'asset_status' => $this->asset_status,
            'depre_type' => $this->depre_type,
            'budget_year' => $this->budget_year,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
