<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title='บัญชีแยกประเภท';$this->params['breadcrumbs'][]=['label'=>'ระบบบัญชี','url'=>['/accounting/dashboard']];$this->params['breadcrumbs'][]=$this->title;
$this->beginBlock('page-title'); ?><h4 class="mb-0"><i class="bi bi-book me-2" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4><?php $this->endBlock();
$this->beginBlock('sub-title'); ?>แสดงเฉพาะรายการที่ตรวจสอบและผ่านบัญชีแล้ว<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('@app/modules/accounting/menu',['active'=>'ledger']) ?><?php $this->endBlock(); ?>
<form class="card card-body border mb-3" method="get"><div class="row g-2 align-items-end"><div class="col-sm-3"><label class="form-label" for="ledger-year">ปีงบประมาณ</label><?= Html::input('number','year',$year,['id'=>'ledger-year','class'=>'form-control','min'=>2500,'max'=>2700]) ?></div><div class="col-sm-6"><label class="form-label" for="ledger-account">รหัสบัญชี</label><?= Html::textInput('account',$account,['id'=>'ledger-account','class'=>'form-control','placeholder'=>'ค้นหารหัสบัญชีทั้งหมดหรือบางส่วน']) ?></div><div class="col-sm-3"><?= Html::submitButton('<i class="bi bi-search me-1"></i>แสดงรายการ',['class'=>'btn btn-primary w-100']) ?></div></div></form>
<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 gap-sm-4 bg-body-tertiary rounded px-3 py-2 mb-3" aria-label="ยอดรวมรายการ"><span><span class="text-body-secondary me-2">เดบิต</span><strong class="font-monospace"><?= number_format((float)($total['debit']??0),2) ?></strong></span><span><span class="text-body-secondary me-2">เครดิต</span><strong class="font-monospace"><?= number_format((float)($total['credit']??0),2) ?></strong></span></div>
<section class="card border"><div class="table-responsive"><?= GridView::widget(['dataProvider'=>$dataProvider,'layout'=>"{items}\n<div class=\"card-footer bg-body\">{pager}</div>",'tableOptions'=>['class'=>'table table-hover align-middle mb-0'],'columns'=>[
 ['label'=>'วันที่','value'=>fn($m)=>$m->journal->document_date,'format'=>['date','php:d/m/Y'],'contentOptions'=>['class'=>'text-nowrap']],
 ['label'=>'เอกสาร','format'=>'raw','value'=>fn($m)=>Html::a(Html::encode($m->journal->document_no),['/accounting/journal/view','id'=>$m->journal_id])],
 ['attribute'=>'account_code_snapshot','label'=>'รหัสบัญชี','contentOptions'=>['class'=>'font-monospace text-nowrap']],['attribute'=>'account_name_snapshot','label'=>'ชื่อบัญชี'],
 ['attribute'=>'debit_amount','label'=>'เดบิต','format'=>['decimal',2],'contentOptions'=>['class'=>'text-end']],['attribute'=>'credit_amount','label'=>'เครดิต','format'=>['decimal',2],'contentOptions'=>['class'=>'text-end']],
],'emptyText'=>'ไม่พบรายการที่ผ่านบัญชีในเงื่อนไขนี้','emptyTextOptions'=>['class'=>'text-center text-body-secondary py-5']]) ?></div></section>
