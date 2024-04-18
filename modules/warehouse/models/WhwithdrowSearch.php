<?php

namespace app\modules\warehouse\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\warehouse\models\Whwithdrow;

/**
 * WhwithdrowSearch represents the model behind the search form of `app\modules\warehouse\models\Whwithdrow`.
 */
class WhwithdrowSearch extends Whwithdrow
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'withdrow_code', 'withdrow_date', 'withdrow_store', 'withdrow_dep', 'withdrow_hr', 'withdrow_pay', 'withdrow_status', 'data_json', 'updated_at', 'created_at'], 'safe'],
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
        $query = Whwithdrow::find();

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
            'withdrow_date' => $this->withdrow_date,
            'withdrow_pay' => $this->withdrow_pay,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'withdrow_code', $this->withdrow_code])
            ->andFilterWhere(['like', 'withdrow_store', $this->withdrow_store])
            ->andFilterWhere(['like', 'withdrow_dep', $this->withdrow_dep])
            ->andFilterWhere(['like', 'withdrow_hr', $this->withdrow_hr])
            ->andFilterWhere(['like', 'withdrow_status', $this->withdrow_status])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        return $dataProvider;
    }
}
