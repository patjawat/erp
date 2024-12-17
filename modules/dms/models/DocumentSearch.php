<?php

namespace app\modules\dms\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\dms\models\Documents;

/**
 * DocumentSearch represents the model behind the search form of `app\modules\dms\models\Documents`.
 */
class DocumentSearch extends Documents
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['doc_type_id', 'topic', 'document_org_id', 'thai_year', 'doc_regis_number', 'doc_number', 'urgent', 'secret', 'doc_date', 'doc_expire', 'doc_receive', 'doc_time', 'data_json'], 'safe'],
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
        $query = Documents::find();

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
        ]);

        $query->andFilterWhere(['like', 'doc_type_id', $this->doc_type_id])
            ->andFilterWhere(['like', 'topic', $this->topic])
            ->andFilterWhere(['like', 'document_org_id', $this->document_org_id])
            ->andFilterWhere(['like', 'thai_year', $this->thai_year])
            ->andFilterWhere(['like', 'doc_regis_number', $this->doc_regis_number])
            ->andFilterWhere(['like', 'doc_number', $this->doc_number])
            ->andFilterWhere(['like', 'urgent', $this->urgent])
            ->andFilterWhere(['like', 'secret', $this->secret])
            ->andFilterWhere(['like', 'doc_date', $this->doc_date])
            ->andFilterWhere(['like', 'doc_expire', $this->doc_expire])
            ->andFilterWhere(['like', 'doc_receive', $this->doc_receive])
            ->andFilterWhere(['like', 'doc_time', $this->doc_time])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
