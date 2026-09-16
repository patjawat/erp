<?php

namespace app\modules\swot\models;

use yii\data\ActiveDataProvider;

/**
 * ค้นหา/กรองรายการในคลังการวิเคราะห์
 */
class SwotBoardSearch extends SwotBoard
{
    public $q;

    public function rules(): array
    {
        return [
            [['q', 'framework', 'status'], 'safe'],
            [['budget_year', 'org_unit_id'], 'integer'],
        ];
    }

    public function scenarios(): array
    {
        return ['default' => ['q', 'framework', 'status', 'budget_year', 'org_unit_id']];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = SwotBoard::find();

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
            'framework' => $this->framework ?: null,
            'status' => $this->status ?: null,
            'budget_year' => $this->budget_year ?: null,
            'org_unit_id' => $this->org_unit_id ?: null,
        ]);

        if (($q = trim((string) $this->q)) !== '') {
            $query->andWhere(['or',
                ['like', 'title', $q],
                ['like', 'objective', $q],
            ]);
        }

        return $dataProvider;
    }
}
