<?php

namespace app\modules\purchase\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form of `app\modules\purchase\models\Order`.
 */
class OrderSearch extends Order
{
    const REQUEST_PENDING = 'pending';

    /**
     * ตัวเลือกปีงบ: ปีที่มีใบจริง + ปีงบปัจจุบัน + ปีถัดไป (เฉพาะเมื่อเปิดรอบทำแผนปีถัดไปแล้ว — ดูล่วงหน้า)
     */
    public static function yearOptions()
    {
        $current = (int) \app\components\AppHelper::YearBudget();
        $years = Order::find()->select('thai_year')->distinct()
            ->where(['name' => 'order'])->andWhere(['not', ['thai_year' => null]])
            ->column();
        $years[] = $current;
        if (\app\modules\plan\components\PlanHelper::period($current + 1)) {
            $years[] = $current + 1;
        }
        $years = array_unique(array_map('intval', $years));
        rsort($years);

        $out = [];
        foreach ($years as $y) {
            $out[$y] = 'ปีงบ ' . $y . ($y === $current ? ' (ปัจจุบัน)' : ($y > $current ? ' (ล่วงหน้า)' : ''));
        }
        return $out;
    }

    public function rules()
    {
        return [
            [['id', 'asset_item', 'qty', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [[
                // รหัสผู้ขายเป็นข้อความ (V282 / เลขผู้เสียภาษีขึ้นต้น 0) — ห้ามตรวจเป็น integer
                // เพราะตรวจไม่ผ่านแล้ว search() คืนผลโดยไม่ใส่ตัวกรองใดเลย
                'vendor_id',
                'ref',
                'name',
                'category_id',
                'code',
                'pr_number',
                'po_number',
                'pq_number',
                'approve',
                'data_json',
                'created_at',
                'updated_at',
                'deleted_at',
                'q',
                'vendor_name',
                'order_type_name',
                'thai_year',
                'date_start',
                'date_end',
                'date_between',
                'plan_group_id',
                'plan_type_id',
                'plan_category_id',
                'plan_item_id',
                'plan_order_id',
                'q_budget_type',
                'pq_purchase_type',
                'request_type',
                'emp_id'
            ], 'safe'],
            [['price'], 'number'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        // "รอตรวจแผน" = request_type ว่าง (ใบปีผูกแผนที่ยังไม่ผ่านทะเบียนคุม) — andFilterWhere ข้ามค่า null จึงกรองแยก
        $requestType = $this->request_type;
        if ($requestType === self::REQUEST_PENDING) {
            $query->andWhere(['request_type' => null]);
            $this->request_type = null;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'asset_item' => $this->asset_item,
            'price' => $this->price,
            'qty' => $this->qty,
            'vendor_id' => $this->vendor_id,
            'status' => $this->status,
            'thai_year' => $this->thai_year,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'request_type' => $this->request_type,
        ]);

        $query
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'pr_number', $this->pr_number])
            ->andFilterWhere(['like', 'po_number', $this->po_number])
            ->andFilterWhere(['like', 'pq_number', $this->pq_number])
            ->andFilterWhere(['like', 'approve', $this->approve])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        $this->request_type = $requestType;

        return $dataProvider;
    }
}
