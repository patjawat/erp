<?php

namespace app\modules\finance\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * ตัวค้นทะเบียนเงินยืม
 *
 * เก็บ query ที่กรองแล้วไว้ให้ผู้เรียกหยิบไปคิดยอดสรุปได้ด้วย ไม่งั้นการ์ดสรุป
 * ด้านบนหน้าทะเบียนจะนับทั้งตารางตลอด กรองอะไรไปตัวเลขก็ไม่ขยับ ซึ่งอ่านแล้วสับสน
 */
class FinanceLoanSearch extends Model
{
    public $q;
    public $status;
    public $fiscal_year;
    public $expense_type_id;
    public $due_state;

    private ?ActiveQuery $filtered = null;

    public function rules()
    {
        return [
            [['q', 'status', 'due_state'], 'string'],
            [['fiscal_year', 'expense_type_id'], 'integer'],
            [['status'], 'in', 'range' => array_keys(FinanceLoan::statusOptions())],
            [['due_state'], 'in', 'range' => array_keys(self::dueStateOptions())],
        ];
    }

    public function attributeLabels()
    {
        return [
            'q' => 'ค้นหา',
            'status' => 'ขั้นตอน',
            'fiscal_year' => 'ปีงบประมาณ',
            'expense_type_id' => 'ประเภทค่าใช้จ่าย',
            'due_state' => 'กำหนดส่งใช้',
        ];
    }

    public static function dueStateOptions(): array
    {
        return [
            'overdue' => 'เกินกำหนด',
            'due_soon' => 'ใกล้ครบกำหนด',
            'open' => 'อยู่ระหว่างดำเนินการ',
            'missing' => 'ไม่ระบุวันครบกำหนด',
            'closed' => 'ปิดยอดแล้ว',
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = FinanceLoan::find()->with(['expenseType']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 25],
            'sort' => [
                // ทุกคีย์ที่ใช้ใน defaultOrder ต้องอยู่ใน attributes ด้วย ไม่งั้น Sort::getOrders()
                // จะพังตอน GridView ดึงข้อมูล — 'id' ไม่ได้ให้ผู้ใช้กดเรียง แต่ใช้ตัดสินลำดับ
                // เมื่อวันที่ยืมซ้ำกัน จึงต้องประกาศไว้ด้วย
                'attributes' => ['contract_no', 'borrower_name', 'borrowed_at', 'due_at', 'approved_amount', 'outstanding_amount', 'status', 'id'],
                'defaultOrder' => ['borrowed_at' => SORT_DESC, 'id' => SORT_DESC],
            ],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            $this->filtered = clone $query;
            return $provider;
        }

        if ($this->q) {
            $query->andWhere(['or',
                ['like', 'contract_no', $this->q],
                ['like', 'borrower_name', $this->q],
                ['like', 'purpose', $this->q],
                ['like', 'request_document_no', $this->q],
            ]);
        }
        $query->andFilterWhere([
            'status' => $this->status,
            'fiscal_year' => $this->fiscal_year,
            'expense_type_id' => $this->expense_type_id,
        ]);
        $this->applyDueState($query);

        $this->filtered = clone $query;
        return $provider;
    }

    /**
     * ตัวกรองกำหนดส่งใช้ ต้องให้ผลตรงกับป้ายสีที่ FinanceLoan::dueState() คำนวณ
     * ไม่งั้นกรอง “ใกล้ครบกำหนด” แล้วได้รายการที่ป้ายเขียนว่าปิดยอดแล้วปนมาด้วย
     */
    private function applyDueState(ActiveQuery $query): void
    {
        if (!$this->due_state) {
            return;
        }
        $today = date('Y-m-d');
        if ($this->due_state === 'closed') {
            $query->andWhere(['or',
                ['status' => [FinanceLoan::STATUS_CLEARED, FinanceLoan::STATUS_COMPLETED]],
                ['and', ['>', 'approved_amount', 0], ['<=', 'outstanding_amount', 0]],
            ]);
            return;
        }

        // ทุกสถานะเวลาที่เหลือพูดถึงเฉพาะใบที่ยังค้างจริง
        $query->andWhere(['not in', 'status', FinanceLoan::CLOSED_STATUSES])
            ->andWhere(['>', 'outstanding_amount', 0]);

        match ($this->due_state) {
            'overdue' => $query->andWhere(['<', 'due_at', $today]),
            'due_soon' => $query->andWhere(['between', 'due_at', $today, date('Y-m-d', strtotime('+7 day'))]),
            'open' => $query->andWhere(['>', 'due_at', date('Y-m-d', strtotime('+7 day'))]),
            'missing' => $query->andWhere(['due_at' => null]),
            default => null,
        };
    }

    /** query ชุดเดียวกับที่ตารางแสดง ใช้คิดการ์ดสรุปให้ตรงกับตัวกรอง */
    public function filteredQuery(): ActiveQuery
    {
        return clone ($this->filtered ?? FinanceLoan::find());
    }

    /** ยอดสรุปของชุดที่กรองแล้ว */
    public function summary(): array
    {
        $base = $this->filteredQuery();
        return [
            'count' => (int) (clone $base)->count(),
            'approved' => (float) (clone $base)->sum('approved_amount'),
            'outstanding' => (float) (clone $base)->sum('outstanding_amount'),
            'overdue' => (int) (clone $base)
                ->andWhere(['not in', 'status', FinanceLoan::CLOSED_STATUSES])
                ->andWhere(['>', 'outstanding_amount', 0])
                ->andWhere(['<', 'due_at', date('Y-m-d')])
                ->count(),
        ];
    }

    /** ปีงบประมาณที่มีข้อมูลจริง สำหรับ dropdown */
    public static function fiscalYearOptions(): array
    {
        $years = FinanceLoan::find()->select('fiscal_year')->distinct()->orderBy(['fiscal_year' => SORT_DESC])->column();
        $current = FinanceLoan::currentFiscalYear();
        if (!in_array($current, array_map('intval', $years), true)) {
            array_unshift($years, $current);
        }
        return array_combine($years, $years);
    }
}
