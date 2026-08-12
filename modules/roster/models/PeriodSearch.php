<?php

namespace app\modules\roster\models;

use app\modules\roster\helpers\RosterAccess;
use yii\data\ActiveDataProvider;

/**
 * ค้นหารอบเวร — จำกัดเฉพาะหน่วยงานที่ผู้ใช้มีสิทธิ์เสมอ
 */
class PeriodSearch extends Period
{
    public function rules()
    {
        return [
            [['unit_id', 'month', 'year_ce', 'thai_year'], 'integer'],
            [['status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return \yii\base\Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Period::find()->where(['deleted_at' => null]);

        $allowed = RosterAccess::viewableUnitIds();
        if ($allowed !== null) {
            $query->andWhere(['unit_id' => $allowed ?: [0]]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['year_ce' => SORT_DESC, 'month' => SORT_DESC, 'unit_id' => SORT_ASC]],
            'pagination' => ['pageSize' => 30],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'unit_id' => $this->unit_id,
            'month' => $this->month,
            'thai_year' => $this->thai_year,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }
}
