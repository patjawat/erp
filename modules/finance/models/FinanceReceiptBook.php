<?php

namespace app\modules\finance\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;

/**
 * ทะเบียนคุมเล่มใบเสร็จรับเงิน — รับเข้า → เบิกจ่ายให้ จนท. → ใช้บันทึกรายรับ
 * ยอดใช้แล้ว/คงเหลือ/ซ้ำ-ข้าม ดึงจาก finance_cash_txn (IN) doc_no = "เล่ม/เลข"
 *
 * @property int $id
 * @property string $book_no
 * @property int $number_from
 * @property int $number_to
 * @property string|null $receipt_type
 * @property string|null $received_date
 * @property int|null $issued_to_emp_id
 * @property string|null $issued_date
 * @property string $status
 * @property string|null $note
 * @property Employees|null $issuedTo
 */
class FinanceReceiptBook extends ActiveRecord
{
    use LoanAuditTrait;

    public const STATUS_RECEIVED = 'received';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_RECEIVED => 'รับเข้า (ยังไม่เบิก)',
        self::STATUS_ISSUED => 'เบิกแล้ว/กำลังใช้',
        self::STATUS_COMPLETED => 'ใช้หมดเล่ม',
        self::STATUS_CANCELLED => 'ยกเลิก',
    ];

    public static function tableName()
    {
        return '{{%finance_receipt_book}}';
    }

    public function rules()
    {
        return [
            [['book_no', 'number_from', 'number_to'], 'required'],
            [['number_from', 'number_to', 'issued_to_emp_id', 'created_by', 'updated_by'], 'integer'],
            [['received_date', 'issued_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::STATUS_LABELS)],
            [['book_no', 'status'], 'string', 'max' => 32],
            [['receipt_type'], 'string', 'max' => 120],
            [['note'], 'string', 'max' => 255],
            [['number_to'], 'compare', 'compareAttribute' => 'number_from', 'operator' => '>=', 'message' => 'เลขสิ้นสุดต้องไม่น้อยกว่าเลขเริ่ม'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'book_no' => 'เลขที่เล่ม',
            'number_from' => 'เลขที่เริ่ม',
            'number_to' => 'เลขที่สิ้นสุด',
            'receipt_type' => 'ประเภทใบเสร็จ',
            'received_date' => 'วันที่รับเข้า',
            'issued_to_emp_id' => 'เบิกให้',
            'issued_date' => 'วันที่เบิก',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getIssuedTo()
    {
        return $this->hasOne(Employees::class, ['id' => 'issued_to_emp_id']);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function total(): int
    {
        return max(0, (int) $this->number_to - (int) $this->number_from + 1);
    }

    /** สรุปการใช้เล่ม จากเลขใบเสร็จที่บันทึกในรายการรับ (doc_no = "เล่ม/เลข") */
    public function usage(): array
    {
        $docs = FinanceCashTxn::find()->select('doc_no')
            ->where(['txn_type' => FinanceCashCategory::TYPE_IN])
            ->andWhere(['like', 'doc_no', $this->book_no . '/%', false])
            ->column();
        $seen = [];
        $dupes = [];
        $outOfRange = [];
        foreach ($docs as $doc) {
            $parts = explode('/', (string) $doc, 2);
            if (count($parts) < 2 || $parts[0] !== $this->book_no) {
                continue;
            }
            $n = (int) $parts[1];
            if (isset($seen[$n])) {
                $dupes[$n] = true;
            } else {
                $seen[$n] = true;
            }
            if ($n < $this->number_from || $n > $this->number_to) {
                $outOfRange[$n] = true;
            }
        }
        $used = count($seen);
        $total = $this->total();
        return [
            'used' => $used,
            'total' => $total,
            'remaining' => max(0, $total - $used),
            'last' => $seen ? max(array_keys($seen)) : null,
            'duplicates' => array_keys($dupes),
            'outOfRange' => array_keys($outOfRange),
        ];
    }

    /** เล่มที่เบิกให้เจ้าหน้าที่คนหนึ่ง (สำหรับ dropdown ฝั่งบันทึกรายรับ — เฟส B) */
    public static function issuedToEmployee(int $empId): array
    {
        return self::find()->where(['issued_to_emp_id' => $empId, 'status' => [self::STATUS_ISSUED, self::STATUS_RECEIVED]])
            ->orderBy(['book_no' => SORT_ASC])->all();
    }
}
