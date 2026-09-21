<?php
namespace app\modules\accounting\services;

use Yii;
use app\modules\accounting\models\AccountingGlPeriodClose;
use app\modules\accounting\models\AccountingPeriod;

class AccountingPeriodCloseService
{
    public function close(AccountingPeriod $period): AccountingGlPeriodClose
    {
        $tx=Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand('SELECT id FROM {{%accounting_periods}} WHERE id=:id FOR UPDATE',[':id'=>$period->id])->queryScalar();
            if(AccountingGlPeriodClose::find()->where(['period_id'=>$period->id])->exists()) throw new \DomainException('งวด GL นี้ปิดแล้ว');
            $review=(new AccountingPeriodReviewService())->review($period);
            if(!$review['summary']['ready']) throw new \DomainException('ยังปิดงวดไม่ได้: '.implode(' / ',$review['summary']['issues']));
            $summary=$review['summary'];
            $model=new AccountingGlPeriodClose([
                'period_id'=>$period->id,'fiscal_year'=>$period->fiscal_year,'status'=>AccountingGlPeriodClose::STATUS_CLOSED,
                'journal_count'=>$summary['journal_count'],'total_debit'=>$summary['debit'],'total_credit'=>$summary['credit'],
                'snapshot_hash'=>self::snapshotHash($summary,$review['balances']),'closed_at'=>date('Y-m-d H:i:s'),
                'closed_by'=>Yii::$app->user->id,
            ]);
            if(!$model->save()) throw new \RuntimeException(implode(' ',$model->getFirstErrors()));
            $tx->commit(); return $model;
        } catch(\Throwable $e){$tx->rollBack();throw $e;}
    }

    public static function snapshotHash(array $summary,array $balances): string
    {
        usort($balances,fn($a,$b)=>strcmp((string)$a['account_code_snapshot'],(string)$b['account_code_snapshot']));
        $data=['journal_count'=>(int)$summary['journal_count'],'debit'=>number_format((float)$summary['debit'],2,'.',''),'credit'=>number_format((float)$summary['credit'],2,'.',''),'balances'=>array_map(fn($r)=>[(string)$r['account_code_snapshot'],number_format((float)$r['debit'],2,'.',''),number_format((float)$r['credit'],2,'.','')],$balances)];
        return hash('sha256',json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
}
