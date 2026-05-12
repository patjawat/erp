<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\Employees;

/**
 * Search model for asset disposal requests.
 */
class AssetDisposalSearch extends AssetDisposal
{
    public $q;

    public function rules()
    {
        return [
            [['id', 'seq_no', 'fiscal_year', 'department', 'responsible_emp_id'], 'integer'],
            [['disposal_no', 'disposal_date', 'disposal_method', 'summary_note', 'status', 'q'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = AssetDisposal::find()
            ->alias('ad')
            ->with(['disposalItems', 'departmentRef', 'responsibleEmp'])
            ->leftJoin(['e' => Employees::tableName()], 'e.id = ad.responsible_emp_id');

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
            'ad.id' => $this->id,
            'ad.seq_no' => $this->seq_no,
            'ad.fiscal_year' => $this->fiscal_year,
            'ad.department' => $this->department,
            'ad.responsible_emp_id' => $this->responsible_emp_id,
        ]);

        $query->andFilterWhere(['like', 'ad.disposal_no', $this->disposal_no])
            ->andFilterWhere(['like', 'ad.disposal_method', $this->disposal_method])
            ->andFilterWhere(['like', 'ad.summary_note', $this->summary_note])
            ->andFilterWhere(['like', 'ad.status', $this->status])
            ->andFilterWhere(['like', 'ad.disposal_date', $this->disposal_date]);

        $q = trim((string) $this->q);
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'ad.disposal_no', $q],
                ['like', 'ad.summary_note', $q],
                ['like', 'ad.disposal_method', $q],
                ['like', 'ad.status', $q],
                new \yii\db\Expression("CONCAT(COALESCE(e.prefix,''),COALESCE(e.fname,''),' ',COALESCE(e.lname,'')) LIKE :q"),
            ]);
            $query->addParams([':q' => '%' . $q . '%']);
        }

        return $dataProvider;
    }
}
