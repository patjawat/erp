<?php
namespace app\modules\accounting\services;

use Yii;
use app\modules\accounting\models\AccountingJournalDraft;
use app\modules\accounting\models\AccountingPeriod;

class AccountingPostingService
{
    public function post(AccountingJournalDraft $journal): AccountingJournalDraft
    {
        if ($journal->status !== AccountingJournalDraft::STATUS_DRAFT) throw new \DomainException('รายการนี้ผ่านบัญชีแล้วหรือไม่อยู่ในสถานะที่ผ่านรายการได้');
        $lines = array_map(fn($line) => ['debit_amount'=>(float)$line->debit_amount, 'credit_amount'=>(float)$line->credit_amount], $journal->lines);
        AccountingJournalDraftService::assertBalanced($lines);
        if (abs((float)$journal->total_debit - array_sum(array_column($lines, 'debit_amount'))) > 0.001 || abs((float)$journal->total_credit - array_sum(array_column($lines, 'credit_amount'))) > 0.001) {
            throw new \DomainException('ยอดรวมของรายการไม่ตรงกับยอดรวมบรรทัดบัญชี');
        }
        $period = $this->findOpenPeriod($journal->document_date);
        if (!$period) throw new \DomainException('ไม่พบงวดบัญชีรายเดือนที่ยังไม่ล็อกสำหรับวันที่เอกสาร กรุณาสร้างหรือเปิดงวดก่อนผ่านรายการ');
        if ((int)$period->fiscal_year !== (int)$journal->fiscal_year) throw new \DomainException('ปีงบประมาณของรายการไม่ตรงกับงวดบัญชี');

        $tx = Yii::$app->db->beginTransaction();
        try {
            $changed = AccountingJournalDraft::updateAll([
                'status'=>AccountingJournalDraft::STATUS_POSTED, 'period_id'=>$period->id,
                'posted_at'=>date('Y-m-d H:i:s'), 'posted_by'=>Yii::$app->user->id,
                'updated_at'=>date('Y-m-d H:i:s'), 'updated_by'=>Yii::$app->user->id,
            ], ['id'=>$journal->id, 'status'=>AccountingJournalDraft::STATUS_DRAFT]);
            if ($changed !== 1) throw new \DomainException('รายการถูกผ่านบัญชีโดยผู้ใช้อื่นแล้ว กรุณาโหลดหน้าใหม่');
            $tx->commit();
            return AccountingJournalDraft::findOne($journal->id);
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
    }

    public function findOpenPeriod(string $date): ?AccountingPeriod
    {
        return AccountingPeriod::find()->where(['period_type'=>AccountingPeriod::TYPE_MONTH])
            ->andWhere(['<>','status',AccountingPeriod::STATUS_LOCKED])
            ->andWhere(['<=','start_date',$date])->andWhere(['>=','end_date',$date])->one();
    }
}
