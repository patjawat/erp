<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanSettlement;
use Yii;

/**
 * บันทึกการส่งใช้เงินยืม และรักษาความสอดคล้องของยอดคงค้างทั้งสาย
 *
 * ทุกการเปลี่ยนแปลงรายการส่งใช้กระทบสามอย่างพร้อมกัน คือแถวนั้นเอง ยอดคงค้างของ
 * รายการที่อยู่หลังมัน และยอดสรุปบนหัวสัญญา ถ้าอัปเดตแยกกันแล้วมีอันใดล้มกลางทาง
 * จะเหลือทะเบียนที่ยอดไม่ตรงกันโดยมองด้วยตาไม่เห็น ทุกเมธอดในนี้จึงห่อด้วยธุรกรรม
 */
class FinanceLoanSettlementService
{
    /** บันทึกรายการส่งใช้ (ทั้งเพิ่มใหม่และแก้ของเดิม) แล้วปรับยอดทั้งใบ */
    public function save(FinanceLoanSettlement $settlement): bool
    {
        $loan = $settlement->loan ?: FinanceLoan::findOne($settlement->loan_id);
        if (!$loan) {
            $settlement->addError('loan_id', 'ไม่พบใบยืมที่อ้างถึง');
            return false;
        }
        if ($settlement->isNewRecord && !$settlement->seq) {
            $settlement->seq = FinanceLoanSettlement::nextSeq($loan->id);
        }
        if (!$settlement->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $settlement->save(false);
            $this->refresh($loan);
            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            $settlement->addError('voucher_amount', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }

    /** ลบรายการส่งใช้ แล้วเรียงครั้งที่ใหม่ให้ต่อเนื่อง */
    public function delete(FinanceLoanSettlement $settlement): bool
    {
        $loan = $settlement->loan;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $settlement->delete();
            $this->renumber($loan);
            $this->refresh($loan);
            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            return false;
        }
    }

    /**
     * ไล่คำนวณยอดคงค้างใหม่ทั้งสาย แล้วปรับยอดสรุปกับสถานะบนหัวสัญญา
     *
     * ต้องไล่ทั้งสายเสมอ ไม่ใช่แค่แถวที่เพิ่งแก้ เพราะการแก้ยอดของครั้งที่ 1
     * ทำให้คงค้างของครั้งที่ 2 และ 3 เปลี่ยนตามไปด้วยทั้งหมด
     */
    public function refresh(FinanceLoan $loan): void
    {
        $loan->refresh();
        $running = (float) $loan->getItems()->sum('amount');
        foreach ($loan->getSettlements()->all() as $settlement) {
            $running = round($running - $settlement->totalAmount(), 2);
            $balance = max(0, $running);
            if (abs((float) $settlement->balance_after - $balance) > 0.001) {
                $settlement->updateAttributes(['balance_after' => $balance]);
            }
        }

        // ข้อมูลบนหัวสัญญาที่เอกสารอ้างถึง ให้ยึดตามรายการส่งใช้ล่าสุดที่มีค่า
        $latest = $loan->getSettlements()->orderBy(['seq' => SORT_DESC])->one();
        $loan->updateAttributes([
            'evidence_sent_at' => $this->latestValue($loan, 'evidence_sent_at'),
            'disbursement_document_no' => $latest?->receipt_no ?: null,
        ]);
        $loan->recalcTotals();
    }

    /** ค่าไม่ว่างล่าสุดของฟิลด์หนึ่งจากรายการส่งใช้ ใช้กับช่องที่ไม่ได้กรอกทุกครั้ง */
    private function latestValue(FinanceLoan $loan, string $attribute): ?string
    {
        foreach ($loan->getSettlements()->orderBy(['seq' => SORT_DESC])->all() as $settlement) {
            if (!empty($settlement->$attribute)) {
                return (string) $settlement->$attribute;
            }
        }
        return null;
    }

    /**
     * เรียงครั้งที่ใหม่ให้เป็น 1, 2, 3 ต่อเนื่อง
     *
     * ลบครั้งที่ 2 จาก 3 รายการแล้วปล่อยให้เหลือ 1 กับ 3 จะทำให้ตารางหน้า 2
     * ของแบบ 8500 ดูเหมือนมีรายการหาย ทั้งที่ผู้ใช้ตั้งใจลบทิ้ง
     *
     * เขียนผ่านค่าลบชั่วคราวก่อน เพราะดัชนี unique (loan_id, seq) จะชนกันเอง
     * ถ้าไล่เขียนเลขใหม่ทับทีละแถวโดยตรง
     */
    private function renumber(FinanceLoan $loan): void
    {
        $rows = $loan->getSettlements()->orderBy(['seq' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($rows as $index => $settlement) {
            $settlement->updateAttributes(['seq' => -($index + 1)]);
        }
        foreach ($rows as $index => $settlement) {
            $settlement->updateAttributes(['seq' => $index + 1]);
        }
    }
}
