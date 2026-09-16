<?php
namespace app\modules\accounting\services;

use app\modules\accounting\models\AccountingJournalDraft;
use app\modules\accounting\models\AccountingJournalLine;
use app\modules\accounting\models\AccountingPeriod;

class AccountingPeriodReviewService
{
    public function review(AccountingPeriod $period): array
    {
        $posted = AccountingJournalDraft::find()->alias('j')->where([
            'j.period_id' => $period->id,
            'j.status' => AccountingJournalDraft::STATUS_POSTED,
        ]);
        $totals = (clone $posted)->select([
            'journal_count' => 'COUNT(j.id)',
            'debit' => 'COALESCE(SUM(j.total_debit), 0)',
            'credit' => 'COALESCE(SUM(j.total_credit), 0)',
        ])->asArray()->one();

        $draftCount = AccountingJournalDraft::find()->where(['status' => AccountingJournalDraft::STATUS_DRAFT])
            ->andWhere(['between', 'document_date', $period->start_date, $period->end_date])->count();
        $misplacedCount = AccountingJournalDraft::find()->where(['status' => AccountingJournalDraft::STATUS_POSTED])
            ->andWhere(['between', 'document_date', $period->start_date, $period->end_date])
            ->andWhere(['or', ['period_id' => null], ['<>', 'period_id', $period->id]])->count();
        $balances = AccountingJournalLine::find()->alias('l')->joinWith(['journal j'])
            ->select([
                'l.account_code_snapshot', 'l.account_name_snapshot',
                'debit' => 'SUM(l.debit_amount)', 'credit' => 'SUM(l.credit_amount)',
                'balance' => 'SUM(l.debit_amount-l.credit_amount)',
            ])->where(['j.period_id' => $period->id, 'j.status' => AccountingJournalDraft::STATUS_POSTED])
            ->groupBy(['l.account_code_snapshot', 'l.account_name_snapshot'])
            ->orderBy(['l.account_code_snapshot' => SORT_ASC])->asArray()->all();

        $summary = [
            'journal_count' => (int)($totals['journal_count'] ?? 0),
            'debit' => round((float)($totals['debit'] ?? 0), 2),
            'credit' => round((float)($totals['credit'] ?? 0), 2),
            'draft_count' => (int)$draftCount,
            'misplaced_count' => (int)$misplacedCount,
        ];
        $summary += self::evaluateReadiness($summary);
        return ['summary' => $summary, 'balances' => $balances];
    }

    public static function evaluateReadiness(array $summary): array
    {
        $issues = [];
        if (($summary['journal_count'] ?? 0) < 1) $issues[] = 'ยังไม่มีรายการผ่านบัญชีในงวดนี้';
        if (abs((float)($summary['debit'] ?? 0) - (float)($summary['credit'] ?? 0)) > 0.001) $issues[] = 'ยอดรวมเดบิตและเครดิตไม่เท่ากัน';
        if (($summary['draft_count'] ?? 0) > 0) $issues[] = 'ยังมีรายการบัญชีร่างในช่วงวันที่ของงวด';
        if (($summary['misplaced_count'] ?? 0) > 0) $issues[] = 'พบรายการผ่านบัญชีที่ผูกผิดงวดหรือไม่ผูกงวด';
        return ['ready' => $issues === [], 'issues' => $issues];
    }
}
