<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Search model for annual asset audits.
 */
class AssetAuditSearch extends AssetAudit
{
    public $q;

    public function rules()
    {
        return [
            [['id', 'seq_no', 'fiscal_year'], 'integer'],
            [['audit_no', 'audit_date', 'auditors', 'summary_note', 'status', 'q'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = AssetAudit::find()->with(['auditItems', 'departmentRef']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'fiscal_year' => SORT_DESC,
                    'seq_no' => SORT_DESC,
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'seq_no' => $this->seq_no,
            'fiscal_year' => $this->fiscal_year,
        ]);

        $query->andFilterWhere(['like', 'audit_no', $this->audit_no])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'auditors', $this->auditors])
            ->andFilterWhere(['like', 'summary_note', $this->summary_note])
            ->andFilterWhere(['like', 'audit_date', $this->audit_date]);

        $q = trim((string) $this->q);
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'audit_no', $q],
                ['like', 'auditors', $q],
                ['like', 'summary_note', $q],
            ]);
        }

        return $dataProvider;
    }
}
