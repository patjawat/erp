<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Uploads;

/**
 * UploadsSearch represents the model behind the search form of `app\models\Uploads`.
 */
class UploadsSearch extends Uploads
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['upload_id', 'type'], 'integer'],
            [['ref', 'file_name', 'real_filename', 'create_date'], 'safe'],
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
        $query = Uploads::find();

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
            'upload_id' => $this->upload_id,
            'create_date' => $this->create_date,
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'real_filename', $this->real_filename]);

        return $dataProvider;
    }
}
