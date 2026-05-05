<?php

namespace app\modules\am\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\hr\models\Employees;

/**
 * Search model for annual asset audits.
 */
class AssetAuditSearch extends AssetAudit
{
    public $q;

    public function rules()
    {
        return [
            [['id', 'seq_no', 'thai_year'], 'integer'],
            [['audit_no', 'audit_date', 'emp_id', 'summary_note', 'status', 'q'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = AssetAudit::find()
            ->alias('aa')
            ->with(['auditItems.asset.assetType', 'departmentRef', 'auditorEmp'])
            ->leftJoin(['ae' => Employees::tableName()], 'ae.id = aa.emp_id');

        $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'thai_year' => SORT_DESC,
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
            'aa.id' => $this->id,
            'aa.seq_no' => $this->seq_no,
        ]);

        $query->andFilterWhere([
            'thai_year' => $this->thai_year,
        ]);

        $query->andFilterWhere(['like', 'aa.audit_no', $this->audit_no])
            ->andFilterWhere(['like', 'aa.status', $this->status])
            ->andFilterWhere(['like', 'aa.emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'aa.summary_note', $this->summary_note])
            ->andFilterWhere(['like', 'aa.audit_date', $this->audit_date]);

        $q = trim((string) $this->q);
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'aa.audit_no', $q],
                ['like', 'aa.emp_id', $q],
                ['like', 'aa.summary_note', $q],
                new \yii\db\Expression("CONCAT(COALESCE(ae.prefix,''),COALESCE(ae.fname,''),' ',COALESCE(ae.lname,'')) LIKE :q"),
            ]);
            $query->addParams([':q' => '%' . $q . '%']);
        }

        return $dataProvider;
    }
}
