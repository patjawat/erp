<?php

namespace app\modules\purchase\models;

use yii\data\ActiveDataProvider;

/**
 * ค้นหาทะเบียน TOR
 */
class TorSearch extends Tor
{
    public function rules()
    {
        return [
            [['thai_year'], 'integer'],
            [['q', 'status', 'asset_type_id', 'purchase_method'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // ปิด scenario ของ model แม่ เพื่อไม่ให้ validation ของฟอร์ม (required) มาบังคับตอนค้นหา
        return \yii\base\Model::scenarios();
    }

    public function search($params)
    {
        $query = Tor::find()->where(['purchase_tor.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'thai_year' => $this->thai_year,
            'status' => $this->status,
            'asset_type_id' => $this->asset_type_id,
            'purchase_method' => $this->purchase_method,
        ]);

        if (!empty($this->q)) {
            $query->andWhere([
                'or',
                ['like', 'title', $this->q],
                ['like', 'doc_no', $this->q],
                ['like', 'egp_no', $this->q],
            ]);
        }

        return $dataProvider;
    }
}
