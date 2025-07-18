<?php

namespace app\modules\am\models;

use yii\base\Model;
use app\models\Categorise;
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
            [['id', 'active'], 'integer'],
            [['ref','group_id', 'category_id', 'code', 'emp_id', 'name', 'title', 'description', 'data_json','fsn_auto','q','asset_type_id'], 'safe'],
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
        // $query = Assetitem::find()->where(['name' => 'asset_item']);

        // // add conditions that should always apply here

        // $dataProvider = new ActiveDataProvider([
        //     'query' => $query,
        // ]);

        $query = AssetItem::find()->alias('i');

    $query
        ->leftJoin(['cat' => 'categorise'], 'cat.code = i.category_id AND cat.name = "asset_category"')
        ->leftJoin(['t' => 'categorise'], 't.code = cat.category_id AND t.name = "asset_type"')
        ->leftJoin(['g' => 'categorise'], 'g.code = t.category_id AND g.name = "asset_group"')
        ->where(['i.group_id' => 'EQUIP', 'i.name' => 'asset_item']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
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
        ]);

         $query->andFilterWhere(['i.group_id' => $this->group_id]);
        $query->andFilterWhere(['like', 'i.title', $this->title]);
        $query->andFilterWhere(['t.code' => $this->asset_type_id]);
        $query->andFilterWhere(['cat.code' => $this->category_id]);
        // $query->andFilterWhere(['like', 'ref', $this->ref])
        //     ->andFilterWhere(['like', 'category_id', $this->category_id])
        //     ->andFilterWhere(['like', 'code', $this->code])
        //     ->andFilterWhere(['like', 'emp_id', $this->emp_id])
        //     ->andFilterWhere(['like', 'name', $this->name])
        //     ->andFilterWhere(['like', 'title', $this->title])
        //     ->andFilterWhere(['like', 'description', $this->description])
        //     ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }

      public function searchWithRelations($params)
    {
        $query = Categorise::find()
            ->alias('i')
            ->with(['assetCategory', 'assetType', 'assetGroup'])
            ->where(['i.group_id' => 'EQUIP'])
            ->andWhere(['i.name' => 'asset_item']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }

}
