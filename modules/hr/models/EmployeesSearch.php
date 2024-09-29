<?php

namespace app\modules\hr\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\Employees;

/**
 * EmployeesSearch represents the model behind the search form of `app\modules\hr\models\Employees`.
 */
class EmployeesSearch extends Employees
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'province', 'amphure', 'district', 'zipcode',  'department', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'avatar', 'photo', 'phone', 'cid', 'email', 'gender', 'prefix', 'fname', 'lname', 'fname_en', 'lname_en', 'birthday', 'join_date', 'end_date', 'address', 'status', 'data_json', 'emergency_contact', 'updated_at', 'created_at','position_type','position_name','show','fullname','all_status','range1','range2','q_department','user_register','q'], 'safe'],
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
        $query = Employees::find();

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
            'user_id' => $this->user_id,
            'birthday' => $this->birthday,
            'join_date' => $this->join_date,
            'end_date' => $this->end_date,
            'province' => $this->province,
            'amphure' => $this->amphure,
            'district' => $this->district,
            'zipcode' => $this->zipcode,
            'position_name' => $this->position_name,
            'position_type' => $this->position_type,
            'department' => $this->department,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'avatar', $this->avatar])
            ->andFilterWhere(['like', 'photo', $this->photo])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'cid', $this->cid])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'prefix', $this->prefix])
            ->andFilterWhere(['like', 'fname', $this->fname])
            ->andFilterWhere(['like', 'lname', $this->lname])
            ->andFilterWhere(['like', 'fname_en', $this->fname_en])
            ->andFilterWhere(['like', 'lname_en', $this->lname_en])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'data_json', $this->data_json])
            ->andFilterWhere(['like', 'emergency_contact', $this->emergency_contact]);

        return $dataProvider;
    }
}
