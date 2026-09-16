<?php

namespace app\modules\flowchart\models;

use yii\data\ActiveDataProvider;

/**
 * ค้นหา/กรองรายการผังกระบวนการ
 */
class FlowchartSearch extends Flowchart
{
    public $q;

    public function rules(): array
    {
        return [
            [['q', 'category', 'status'], 'safe'],
            [['budget_year', 'org_unit_id'], 'integer'],
        ];
    }

    public function scenarios(): array
    {
        return ['default' => ['q', 'category', 'status', 'budget_year', 'org_unit_id']];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Flowchart::find()->with('orgUnit');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 24],
            'sort' => ['defaultOrder' => ['updated_at' => SORT_DESC, 'id' => SORT_DESC]],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'category' => $this->category ?: null,
            'status' => $this->status ?: null,
            'budget_year' => $this->budget_year ?: null,
            'org_unit_id' => $this->org_unit_id ?: null,
        ]);

        if (($q = trim((string) $this->q)) !== '') {
            $query->andWhere(['or',
                ['like', 'title', $q],
                ['like', 'description', $q],
                ['like', 'code', $q],
            ]);
        }

        return $dataProvider;
    }
}
