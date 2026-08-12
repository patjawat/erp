<?php

namespace app\modules\purchase\models;

use yii\data\ActiveDataProvider;
use app\modules\purchase\components\BondCalculator;

/**
 * ค้นหาทะเบียนหลักประกัน
 */
class BondSearch extends Bond
{
    /** ตัวกรองพิเศษที่ไม่ใช่คอลัมน์: expired = หมดอายุแล้ว, near = ใกล้หมดอายุ, pending = ยังไม่วาง */
    public $flag;

    public function rules()
    {
        return [
            [['thai_year'], 'integer'],
            [['q', 'status', 'bond_type', 'bond_form', 'source_type', 'vendor_id', 'flag'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // ปิด scenario ของ model แม่ ไม่ให้ required ของฟอร์มมาบังคับตอนค้นหา
        return \yii\base\Model::scenarios();
    }

    public function search($params)
    {
        $query = Bond::find()->where(['purchase_bond.deleted_at' => null]);

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
            'bond_type' => $this->bond_type,
            'bond_form' => $this->bond_form,
            'source_type' => $this->source_type,
            'vendor_id' => $this->vendor_id,
        ]);

        if (!empty($this->q)) {
            $query->andWhere([
                'or',
                ['like', 'title', $this->q],
                ['like', 'doc_no', $this->q],
                ['like', 'doc_ref', $this->q],
                ['like', 'issuer', $this->q],
                ['like', 'vendor_name', $this->q],
            ]);
        }

        // เงื่อนไขอายุใช้ชุดเดียวกับ BondCalculator::expiryState() เพื่อให้จำนวนบนปุ่ม
        // กรองด่วนกับป้ายในแต่ละแถวตรงกันเสมอ
        if ($this->flag === 'expired') {
            $query->andWhere(['not in', 'status', Bond::closedStatuses()])
                ->andWhere(['not', ['expiry_date' => null]])
                ->andWhere(['<', 'expiry_date', date('Y-m-d')]);
        } elseif ($this->flag === 'near') {
            $query->andWhere(['not in', 'status', Bond::closedStatuses()])
                ->andWhere(['not', ['expiry_date' => null]])
                ->andWhere([
                    'between',
                    'expiry_date',
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+' . BondCalculator::NEAR_DAYS . ' day')),
                ]);
        } elseif ($this->flag === 'pending') {
            $query->andWhere(['status' => Bond::STATUS_PENDING]);
        }

        return $dataProvider;
    }

    /** จำนวนใบที่ต้องจับตาของปีที่กำลังดู — ใช้ทำปุ่มกรองด่วนบนหัวหน้าทะเบียน */
    public function counters(): array
    {
        $base = function () {
            $q = Bond::find()->where(['purchase_bond.deleted_at' => null]);
            if (!empty($this->thai_year)) {
                $q->andWhere(['thai_year' => $this->thai_year]);
            }
            return $q;
        };

        $alive = function () use ($base) {
            return $base()
                ->andWhere(['not in', 'status', Bond::closedStatuses()])
                ->andWhere(['not', ['expiry_date' => null]]);
        };

        return [
            'expired' => (int) $alive()->andWhere(['<', 'expiry_date', date('Y-m-d')])->count(),
            'near' => (int) $alive()->andWhere([
                'between',
                'expiry_date',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+' . BondCalculator::NEAR_DAYS . ' day')),
            ])->count(),
            'pending' => (int) $base()->andWhere(['status' => Bond::STATUS_PENDING])->count(),
            'total' => (int) $base()->count(),
            // ยอดรวมนับเฉพาะใบที่ยังเดินอยู่ ใบที่คืนแล้วไม่ใช่เงินที่หน่วยงานถืออยู่
            'amount' => (float) $base()
                ->andWhere(['status' => [Bond::STATUS_PENDING, Bond::STATUS_ACTIVE]])
                ->sum('amount'),
        ];
    }

    /**
     * สัญญาที่เข้าเกณฑ์ต้องวางหลักประกัน แต่ยังไม่มีหลักประกันใบไหนในทะเบียน
     *
     * ต้องกรองในฝั่ง PHP เพราะเกณฑ์อยู่ในตาราง purchase_bond_policy เป็นช่วงวงเงิน
     * ที่งานพัสดุแก้เองได้ ไม่สามารถเขียนเป็นเงื่อนไข SQL ตายตัวได้ จำนวนสัญญาต่อปี
     * อยู่ในหลักสิบ การวนอ่านจึงไม่เป็นภาระ
     *
     * เฟสนี้ตรวจเฉพาะฝั่งสัญญา ยังไม่รวมใบสั่งซื้อ เพราะวงเงินของใบสั่งซื้อต้องรวมจาก
     * รายการย่อยและนิยาม "ใบที่ต้องมีหลักประกัน" ของใบสั่งซื้อยังไม่ได้ตกลงกัน
     *
     * @return array<int,array{contract:Contract, policy:array}>
     */
    public static function missingContracts(?int $thaiYear = null): array
    {
        $query = Contract::find()
            ->where(['purchase_contract.deleted_at' => null])
            ->andWhere(['not in', 'status', [Contract::STATUS_CANCELLED]]);

        if (!empty($thaiYear)) {
            $query->andWhere(['thai_year' => $thaiYear]);
        }

        $contracts = $query->orderBy(['id' => SORT_DESC])->all();
        if (!$contracts) {
            return [];
        }

        // ดึงรายชื่อสัญญาที่มีหลักประกันอยู่แล้วทีเดียว ไม่ยิงทีละฉบับ
        $covered = Bond::find()
            ->select('source_id')
            ->distinct()
            ->where([
                'source_type' => Bond::SOURCE_CONTRACT,
                'deleted_at' => null,
                'status' => Bond::openStatuses(),
            ])
            ->column();
        $covered = array_map('intval', $covered);

        $rows = [];
        foreach ($contracts as $contract) {
            if (in_array((int) $contract->id, $covered, true)) {
                continue;
            }
            $policy = BondCalculator::policyFor((float) $contract->budget, $contract->contract_type);
            if (!$policy['required']) {
                continue;
            }
            $rows[] = ['contract' => $contract, 'policy' => $policy];
        }

        return $rows;
    }
}
