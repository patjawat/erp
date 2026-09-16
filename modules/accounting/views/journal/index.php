<?php
use yii\grid\GridView;
use yii\helpers\Html;
use app\modules\accounting\models\AccountingJournalDraft;
$this->title='รายการบัญชีร่าง';$this->params['breadcrumbs'][]=['label'=>'ระบบบัญชี','url'=>['/accounting/dashboard']];$this->params['breadcrumbs'][]=$this->title;
$this->beginBlock('page-title'); ?><h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-journal-check" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4><?php $this->endBlock();
$this->beginBlock('sub-title'); ?>รายการที่สร้างจากเอกสารอนุมัติแล้ว แต่ยังไม่ผ่านเข้าบัญชีแยกประเภท<?php $this->endBlock();
$this->beginBlock('page-action'); ?><div class="d-flex flex-wrap gap-2"><?= $this->render('@app/modules/accounting/menu',['active'=>'journal']) ?><?php if(Yii::$app->user->can('accountingChartManage'))echo Html::a('ตั้งค่าบัญชีรายปี',['settings'],['class'=>'btn btn-outline-secondary']); ?></div><?php $this->endBlock(); ?>
<div class="alert alert-info" role="status"><strong>พื้นที่ตรวจสอบก่อนผ่านรายการ</strong> — ระบบยังไม่สร้างยอดในบัญชีแยกประเภทและไม่แก้ข้อมูลต้นทาง</div>
<section class="card border" aria-labelledby="journal-list-heading"><div class="card-header bg-body"><h5 class="mb-0" id="journal-list-heading">รายการรอตรวจ</h5></div><div class="table-responsive">
<?= GridView::widget(['dataProvider'=>$dataProvider,'layout'=>"{items}\n<div class=\"card-footer bg-body\">{pager}</div>",'tableOptions'=>['class'=>'table table-hover align-middle mb-0'],'columns'=>[
 ['attribute'=>'document_date','label'=>'วันที่','format'=>['date','php:d/m/Y'],'contentOptions'=>['class'=>'text-nowrap']],
 ['attribute'=>'document_no','label'=>'เอกสาร','format'=>'raw','value'=>fn($m)=>Html::a(Html::encode($m->document_no),['view','id'=>$m->id],['class'=>'fw-semibold'])],
 ['attribute'=>'description','label'=>'คำอธิบาย'],['attribute'=>'fiscal_year','label'=>'ปีงบประมาณ'],
 ['attribute'=>'total_debit','label'=>'เดบิต','format'=>['decimal',2],'contentOptions'=>['class'=>'text-end'],'headerOptions'=>['class'=>'text-end']],
 ['attribute'=>'total_credit','label'=>'เครดิต','format'=>['decimal',2],'contentOptions'=>['class'=>'text-end'],'headerOptions'=>['class'=>'text-end']],
 ['attribute'=>'status','label'=>'สถานะ','format'=>'raw','value'=>fn(AccountingJournalDraft $m)=>Html::tag('span','ร่าง',['class'=>'badge bg-warning-subtle text-warning-emphasis'])],
],'emptyText'=>'ยังไม่มีรายการบัญชีร่าง','emptyTextOptions'=>['class'=>'text-center text-body-secondary py-5']]) ?>
</div></section>
