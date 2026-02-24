<?php

namespace app\modules\notify\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class NotifySearch extends Notify
{
    public $q;

    public function rules()
    {
        return [
            [['id', 'recipient_emp_id'], 'integer'],
            [['type', 'title', 'message', 'ref_type', 'ref_id', 'read_at', 'created_at', 'q'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Notify::find()->with(['recipientEmp']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // recipient_emp_id ไม่กรองใน search — ให้ controller บังคับใน actionIndex เพื่อไม่ให้ params แทนที่แล้วได้ 0 แถว
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
        ]);
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['ref_type' => $this->ref_type])
            ->andFilterWhere(['ref_id' => $this->ref_id]);
        if ($this->read_at !== null && $this->read_at !== '') {
            if ($this->read_at === '0' || $this->read_at === 'unread') {
                $query->andWhere(['read_at' => null]);
            } elseif ($this->read_at === 'read') {
                $query->andWhere(['not', ['read_at' => null]]);
            } else {
                $query->andFilterWhere(['read_at' => $this->read_at]);
            }
        }
        if (!empty($this->q)) {
            $query->andWhere([
                'or',
                ['like', 'title', $this->q],
                ['like', 'message', $this->q],
            ]);
        }

        return $dataProvider;
    }
}
