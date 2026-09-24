<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * เล่มเช็ค — ช่วงเลขเช็คที่รับเข้าตามบัญชีจ่าย
 *
 * @property int $id
 * @property int $cash_account_id
 * @property string|null $book_no
 * @property string|null $prefix
 * @property int $start_no
 * @property int $end_no
 * @property int $number_width
 * @property string|null $received_date
 * @property string $status
 */
class FinanceChequeBook extends ActiveRecord
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_USED_UP = 'used_up';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_USED_UP => 'ใช้หมดเล่ม',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function tableName(): string
    {
        return '{{%finance_cheque_book}}';
    }

    public function rules(): array
    {
        return [
            [['cash_account_id', 'start_no', 'end_no'], 'required'],
            [['cash_account_id', 'start_no', 'end_no', 'number_width'], 'integer'],
            [['start_no', 'end_no'], 'integer', 'min' => 0],
            [['end_no'], 'compare', 'compareAttribute' => 'start_no', 'operator' => '>=', 'message' => 'เลขสุดท้ายต้องไม่น้อยกว่าเลขเริ่ม'],
            [['received_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['book_no', 'prefix'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'cash_account_id' => 'บัญชีจ่าย',
            'book_no' => 'เลข/ชื่อเล่ม',
            'prefix' => 'คำนำหน้าเลข',
            'start_no' => 'เลขเริ่ม',
            'end_no' => 'เลขสุดท้าย',
            'number_width' => 'จำนวนหลัก',
            'received_date' => 'วันที่รับเล่ม',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // เดาจำนวนหลักจากเลขสุดท้ายถ้ายังไม่ระบุ
        if (!$this->number_width) {
            $this->number_width = strlen((string) $this->end_no);
        }
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
        $now = time();
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }

    public function getCashAccount()
    {
        return $this->hasOne(FinanceCashAccount::class, ['id' => 'cash_account_id']);
    }

    /** จำนวนใบทั้งเล่ม */
    public function totalLeaves(): int
    {
        return max(0, (int) $this->end_no - (int) $this->start_no + 1);
    }

    /** จำนวนใบที่ใช้ไปแล้ว (นับเช็คทุกสถานะรวมยกเลิก — ใบกระดาษถูกใช้จริง) */
    public function usedCount(): int
    {
        return (int) FinanceCheque::find()->where(['book_id' => $this->id])->count();
    }

    public function remaining(): int
    {
        return max(0, $this->totalLeaves() - $this->usedCount());
    }

    /** จัดรูปเลขเช็คตาม prefix + จำนวนหลัก */
    public function formatNo(int $value): string
    {
        return (string) $this->prefix . str_pad((string) $value, (int) $this->number_width, '0', STR_PAD_LEFT);
    }

    /** เลขเช็คถัดไปในเล่ม (คืน null ถ้าใช้หมดเล่มแล้ว) */
    public function nextNo(): ?string
    {
        $used = FinanceCheque::find()->select('cheque_no')->where(['book_id' => $this->id])->column();
        $maxVal = (int) $this->start_no - 1;
        foreach ($used as $no) {
            if (preg_match('/(\d+)\s*$/u', (string) $no, $m)) {
                $maxVal = max($maxVal, (int) $m[1]);
            }
        }
        $next = $maxVal + 1;
        return $next > (int) $this->end_no ? null : $this->formatNo($next);
    }

    public function isFull(): bool
    {
        return $this->remaining() <= 0 || $this->status === self::STATUS_USED_UP;
    }

    public function label(): string
    {
        $range = $this->formatNo((int) $this->start_no) . '–' . $this->formatNo((int) $this->end_no);
        return ($this->book_no ? 'เล่ม ' . $this->book_no . ' ' : '') . '(' . $range . ')';
    }

    /** เล่มที่ใช้งานได้ของบัญชี เรียงตามเลขเริ่ม */
    public static function activeForAccount(int $accountId): array
    {
        return self::find()->where(['cash_account_id' => $accountId, 'status' => self::STATUS_ACTIVE])
            ->orderBy(['start_no' => SORT_ASC])->all();
    }
}
