<?php

namespace app\modules\inventoryV2\models;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * WarehouseSearch for Warehouse in inventoryV2
 */
class WarehouseSearch extends Warehouse
{
    public $officer_name;
    public $department_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_main', 'department_id'], 'integer'],
            [['warehouse_name', 'warehouse_code', 'category_id', 'warehouse_type', 'officer_name'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Warehouse::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'is_main' => $this->is_main,
            'warehouse_type' => $this->warehouse_type,
        ]);
        if (!empty($this->department_id)) {
            $departmentIds = [$this->department_id];
            $organization = Organization::findOne($this->department_id);
            if ($organization !== null) {
                $departmentIds = Organization::find()
                    ->select('id')
                    ->where(['root' => $organization->root])
                    ->andWhere(['>=', 'lft', $organization->lft])
                    ->andWhere(['<=', 'rgt', $organization->rgt])
                    ->column();
            }
            $query->andWhere(['department' => $departmentIds]);
        }

        $query
            ->andFilterWhere(['like', 'warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code]);

        if (!empty($this->officer_name)) {
            $employeeUserIds = Employees::find()
                ->select('user_id')
                ->where(['or',
                    ['like', 'fname', $this->officer_name],
                    ['like', 'lname', $this->officer_name],
                    ['like', new Expression("CONCAT(fname, ' ', lname)"), $this->officer_name],
                    ['like', new Expression("CONCAT(fname, lname)"), $this->officer_name],
                ])
                ->column();

            if (empty($employeeUserIds)) {
                $query->andWhere('0=1');
            } else {
                $officerConditions = ['or'];
                foreach ($employeeUserIds as $index => $userId) {
                    $paramName = ':officer_user_id_' . $index;
                    $officerConditions[] = new Expression(
                        "JSON_CONTAINS(COALESCE(data_json, '{}'), JSON_QUOTE({$paramName}), '$.officer')",
                        [$paramName => (string) $userId]
                    );
                }
                $query->andWhere($officerConditions);
            }
        }

        return $dataProvider;
    }
}
