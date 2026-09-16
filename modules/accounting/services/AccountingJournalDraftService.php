<?php

namespace app\modules\accounting\services;

use Yii;
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartVersion;
use app\modules\accounting\models\AccountingFiscalConfig;
use app\modules\accounting\models\AccountingJournalDraft;
use app\modules\accounting\models\AccountingJournalLine;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\services\FinancePayableDraftService;

class AccountingJournalDraftService
{
    public function saveConfig(AccountingFiscalConfig $config): AccountingFiscalConfig
    {
        $version = AccountingChartVersion::findOne($config->chart_version_id);
        $payable = AccountingChartAccount::findOne($config->payable_account_id);
        $vat = $config->input_vat_account_id ? AccountingChartAccount::findOne($config->input_vat_account_id) : null;
        if (!$version || $version->scope !== AccountingChartVersion::SCOPE_HOSPITAL || $version->status !== AccountingChartVersion::STATUS_ACTIVE || (int)$version->fiscal_year !== (int)$config->fiscal_year) {
            throw new \DomainException('กรุณาเลือกผังโรงพยาบาลที่เปิดใช้และตรงกับปีงบประมาณ');
        }
        if (!$payable || (int)$payable->version_id !== (int)$version->id || !$payable->is_active || $payable->category !== '2') {
            throw new \DomainException('บัญชีเจ้าหนี้ต้องเป็นบัญชีหมวดหนี้สินในผังที่เลือก');
        }
        if ($vat && ((int)$vat->version_id !== (int)$version->id || !$vat->is_active || $vat->category !== '1')) {
            throw new \DomainException('บัญชีภาษีซื้อต้องเป็นบัญชีหมวดสินทรัพย์ในผังที่เลือก');
        }
        if (!$config->save()) throw new \RuntimeException(implode(' ', $config->getFirstErrors()));
        return $config;
    }

    public function createFromPayable(FinancePayable $payable): AccountingJournalDraft
    {
        if ($payable->status !== FinancePayable::STATUS_APPROVED) throw new \DomainException('สร้างรายการบัญชีร่างได้เฉพาะเจ้าหนี้ที่อนุมัติแล้ว');
        if (AccountingJournalDraft::find()->where(['source_type'=>AccountingJournalDraft::SOURCE_PAYABLE,'source_id'=>$payable->id])->exists()) throw new \DomainException('รายการเจ้าหนี้นี้มีรายการบัญชีร่างแล้ว');
        $year = FinancePayableDraftService::fiscalYearForDate($payable->invoice_date);
        $config = AccountingFiscalConfig::findOne(['fiscal_year'=>$year]);
        if (!$config) throw new \DomainException('ยังไม่ได้ตั้งค่าบัญชีเจ้าหนี้สำหรับปีงบประมาณ ' . $year);
        if ((int)$payable->accounting_chart_version_id !== (int)$config->chart_version_id) throw new \DomainException('ผังบัญชีของเจ้าหนี้ไม่ตรงกับค่าตั้งต้นปีงบประมาณ');
        $gross=round((float)$payable->gross_amount,2); $vat=round((float)$payable->vat_amount,2);
        if ($gross <= 0 || $vat < 0 || $vat > $gross) throw new \DomainException('ยอดหนี้หรือยอดภาษีซื้อไม่ถูกต้อง');
        if ($vat > 0 && !$config->input_vat_account_id) throw new \DomainException('รายการมี VAT แต่ยังไม่ได้ตั้งค่าบัญชีภาษีซื้อ');
        $primary=AccountingChartAccount::findOne($payable->accounting_chart_account_id); $payableAccount=$config->payableAccount; $vatAccount=$config->inputVatAccount;
        if (!$primary || !$payableAccount || ($vat > 0 && !$vatAccount)) throw new \DomainException('ไม่พบบัญชีที่จำเป็นสำหรับสร้างรายการร่าง');
        $lines=[]; $base=round($gross-$vat,2);
        if($base>0)$lines[]=$this->line($primary,$base,0,'มูลค่าก่อนภาษี ' . $payable->invoice_no);
        if($vat>0)$lines[]=$this->line($vatAccount,$vat,0,'ภาษีซื้อ ' . $payable->invoice_no);
        $lines[]=$this->line($payableAccount,0,$gross,'ตั้งเจ้าหนี้ ' . $payable->vendor_name_snapshot);
        self::assertBalanced($lines);
        $tx=Yii::$app->db->beginTransaction();
        try {
            $journal=new AccountingJournalDraft(['source_type'=>AccountingJournalDraft::SOURCE_PAYABLE,'source_id'=>$payable->id,'fiscal_year'=>$year,'document_date'=>$payable->invoice_date,'document_no'=>$payable->invoice_no,'description'=>'ตั้งเจ้าหนี้ ' . $payable->vendor_name_snapshot,'status'=>AccountingJournalDraft::STATUS_DRAFT,'total_debit'=>$gross,'total_credit'=>$gross]);
            if(!$journal->save())throw new \RuntimeException(implode(' ',$journal->getFirstErrors()));
            foreach($lines as $i=>$data){$line=new AccountingJournalLine($data+['journal_id'=>$journal->id,'sequence'=>$i+1]);if(!$line->save())throw new \RuntimeException(implode(' ',$line->getFirstErrors()));}
            $tx->commit(); return $journal;
        } catch(\Throwable $e){$tx->rollBack();throw $e;}
    }

    public static function assertBalanced(array $lines): void
    {
        $debit=round(array_sum(array_column($lines,'debit_amount')),2); $credit=round(array_sum(array_column($lines,'credit_amount')),2);
        if($debit<=0 || abs($debit-$credit)>0.001)throw new \DomainException('ยอดเดบิตและเครดิตของรายการบัญชีไม่สมดุล');
        foreach($lines as $line)if(((float)$line['debit_amount']>0)===((float)$line['credit_amount']>0))throw new \DomainException('แต่ละบรรทัดต้องมีเดบิตหรือเครดิตเพียงด้านเดียว');
    }

    private function line(AccountingChartAccount $account,float $debit,float $credit,string $description): array
    { return ['chart_version_id'=>$account->version_id,'chart_account_id'=>$account->id,'account_code_snapshot'=>$account->code,'account_name_snapshot'=>$account->name,'description'=>$description,'debit_amount'=>$debit,'credit_amount'=>$credit]; }
}
