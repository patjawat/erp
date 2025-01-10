<?php

namespace app\modules\dms\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\dms\models\DocumentsDetail;

/**
 * DocumentsDetailSearch represents the model behind the search form of `app\modules\dms\models\DocumentsDetail`.
 */
class DocumentsDetailSearch extends DocumentsDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['doc_read','show_reading','ref', 'name', 'document_id', 'to_id', 'to_name', 'to_type', 'from_id', 'from_name', 'from_type', 'created_at', 'updated_at', 'deleted_at','q','thai_year','tags_department','tags_employee'], 'safe'],
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
        $query = DocumentsDetail::find();

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
            'doc_read' => $this->doc_read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'document_id', $this->document_id])
            ->andFilterWhere(['like', 'to_id', $this->to_id])
            ->andFilterWhere(['like', 'to_name', $this->to_name])
            ->andFilterWhere(['like', 'to_type', $this->to_type])
            ->andFilterWhere(['like', 'from_id', $this->from_id])
            ->andFilterWhere(['like', 'from_name', $this->from_name])
            ->andFilterWhere(['like', 'from_type', $this->from_type]);

        return $dataProvider;
    }
}
