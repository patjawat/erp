<?php

namespace app\modules\sm\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\am\models\AssetItem;
use app\models\Categorise;

/**
 * AssetItemSearch represents the model behind the search form of `app\modules\am\models\AssetItem`.
 */
class AssetItemSearch extends AssetItem
{
    /**
     * {@inheritdoc}
     */


     public $category_id_type;
    public function rules()
    {
        return [
            [['id', 'active'], 'integer'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description', 'data_json','fsn_auto'], 'safe'],
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
        $query = Assetitem::find();

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
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'category_id', $this->category_id != '' ? Categorise::findOne(['name' => 'asset_type','title'=>$this->category_id])->code : ''])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'JSON_UNQUOTE(JSON_EXTRACT(data_json, "$.asset_type.category_id"))', $this->data_json]);

        return $dataProvider;
    }
}
