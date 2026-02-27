<?php

namespace app\modules\usermanager\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\usermanager\models\User;

/**
 * UserSearch represents the model behind the search form of `app\modules\usermanager\models\User`.
 */
class UserSearch extends User
{
    public $q;
    public $phone;
    public $password_reset_token;
    /** @var string กรองตามบทบาท (role name) */
    public $role;

    public function rules()
    {
        return [
            [['id', 'confirmed_at', 'blocked_at', 'created_at', 'updated_at', 'last_login_at', 'status'], 'integer'],
            [['username', 'email', 'password_hash', 'auth_key', 'unconfirmed_email', 'registration_ip', 'password_reset_token', 'fullname', 'q', 'phone', 'line_id', 'hash_cid', 'role'], 'safe'],
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
        $query = User::find();

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
            'confirmed_at' => $this->confirmed_at,
            'blocked_at' => $this->blocked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_login_at' => $this->last_login_at,
            'hash_cid' => $this->hash_cid,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'unconfirmed_email', $this->unconfirmed_email])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token]);

        if ($this->role !== null && $this->role !== '' && \Yii::$app->db->getSchema()->getTableSchema('auth_assignment', true) !== null) {
            $query->innerJoin('auth_assignment', 'auth_assignment.user_id = ' . User::tableName() . '.id')
                ->andWhere(['auth_assignment.item_name' => $this->role]);
        }

        return $dataProvider;
    }
}
