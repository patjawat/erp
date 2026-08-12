<?php

namespace app\modules\purchase\models;

use yii\data\ActiveDataProvider;

/**
 * ค้นหาทะเบียนสัญญา
 */
class ContractSearch extends Contract
{
    /** ตัวกรองพิเศษที่ไม่ใช่คอลัมน์: overdue = ค้างส่ง, duesoon = ใกล้ครบกำหนด 7 วัน */
    public $flag;

    public function rules()
    {
        return [
            [['thai_year'], 'integer'],
            [['q', 'status', 'contract_type', 'vendor_id', 'flag'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // ปิด scenario ของ model แม่ ไม่ให้ required ของฟอร์มมาบังคับตอนค้นหา
        return \yii\base\Model::scenarios();
    }

    public function search($params)
    {
        $query = Contract::find()->where(['purchase_contract.deleted_at' => null]);

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
            'contract_type' => $this->contract_type,
            'vendor_id' => $this->vendor_id,
        ]);

        if (!empty($this->q)) {
            $query->andWhere([
                'or',
                ['like', 'title', $this->q],
                ['like', 'doc_no', $this->q],
                ['like', 'contract_no', $this->q],
                ['like', 'egp_no', $this->q],
                ['like', 'vendor_name', $this->q],
            ]);
        }

        // งานค้างส่ง/ใกล้ครบกำหนด นับจากวันนี้เทียบกับ end_date ของสัญญาที่ยังไม่ส่งมอบ
        // และยังไม่ถูกปิด — ใช้เงื่อนไขเดียวกับ Contract::isOverdue() เพื่อให้ตัวเลข
        // บนหน้าทะเบียนกับป้ายในแต่ละแถวตรงกัน
        if (in_array($this->flag, ['overdue', 'duesoon'], true)) {
            $query->andWhere(['delivery_date' => null])
                ->andWhere(['receive_date' => null])
                ->andWhere(['not in', 'status', [Contract::STATUS_RECEIVED, Contract::STATUS_CANCELLED]])
                ->andWhere(['not', ['end_date' => null]]);

            if ($this->flag === 'overdue') {
                $query->andWhere(['<', 'end_date', date('Y-m-d')]);
            } else {
                $query->andWhere(['between', 'end_date', date('Y-m-d'), date('Y-m-d', strtotime('+7 day'))]);
            }
        }

        return $dataProvider;
    }

    /** จำนวนสัญญาค้างส่ง/ใกล้ครบกำหนดของปีที่กำลังดู — ใช้ทำปุ่มกรองด่วน */
    public function counters(): array
    {
        $base = function () {
            $q = Contract::find()
                ->where(['purchase_contract.deleted_at' => null])
                ->andWhere(['delivery_date' => null])
                ->andWhere(['receive_date' => null])
                ->andWhere(['not in', 'status', [Contract::STATUS_RECEIVED, Contract::STATUS_CANCELLED]])
                ->andWhere(['not', ['end_date' => null]]);
            if (!empty($this->thai_year)) {
                $q->andWhere(['thai_year' => $this->thai_year]);
            }
            return $q;
        };

        return [
            'overdue' => (int) $base()->andWhere(['<', 'end_date', date('Y-m-d')])->count(),
            'duesoon' => (int) $base()
                ->andWhere(['between', 'end_date', date('Y-m-d'), date('Y-m-d', strtotime('+7 day'))])
                ->count(),
        ];
    }
}
