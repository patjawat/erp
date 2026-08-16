<?php
use yii\helpers\Html;
use app\modules\finance\models\FinanceVoucher;

$this->title = 'เบิกจ่ายและฎีกา';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?><div class="d-flex align-items-center gap-2"><i class="bi bi-file-earmark-check fs-4"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div><?php $this->endBlock();
$this->beginBlock('sub-title'); ?>รับช่วงจากทะเบียนเจ้าหนี้ที่ฝ่ายบัญชีอนุมัติแล้ว เพื่อเตรียมแหล่งเงินและวิธีจ่าย<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'voucher']); $this->endBlock();
?>
<div class="alert alert-info d-flex gap-2 align-items-start"><i class="bi bi-shield-check mt-1"></i><div><strong>ขอบเขตระยะนี้: จัดทำร่างเท่านั้น</strong><div>ยังไม่อนุมัติฎีกา ไม่ออกเช็ค ไม่บันทึกจ่าย และไม่ลงบัญชี ยอดเงินถูกคัดลอกจากทะเบียนเจ้าหนี้และแก้ไขที่หน้านี้ไม่ได้</div></div></div>

<section class="card border shadow-sm mb-4" aria-labelledby="ready-heading">
 <div class="card-header bg-body d-flex justify-content-between"><h5 id="ready-heading" class="mb-0">รายการพร้อมจัดทำฎีกา</h5><span class="badge text-bg-primary"><?= count($ready) ?></span></div>
 <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>ทะเบียนเจ้าหนี้</th><th>ผู้รับเงิน</th><th>ใบแจ้งหนี้</th><th>ครบกำหนด</th><th class="text-end">ยอดสุทธิ</th><th></th></tr></thead><tbody>
 <?php if (!$ready): ?><tr><td colspan="6" class="text-center text-body-secondary py-4">ยังไม่มีรายการที่ฝ่ายบัญชีอนุมัติและรอจัดทำฎีกา</td></tr><?php endif; ?>
 <?php foreach ($ready as $row): ?><tr><td class="fw-semibold"><?= Html::encode($row->payable_no) ?></td><td><?= Html::encode($row->vendor_name_snapshot) ?></td><td><?= Html::encode($row->invoice_no) ?></td><td class="text-nowrap"><?= Yii::$app->formatter->asDate($row->due_date, 'php:d/m/Y') ?></td><td class="text-end text-nowrap"><?= number_format($row->net_amount, 2) ?></td><td class="text-end"><?= Html::a('<i class="bi bi-plus-circle me-1"></i>สร้างร่างฎีกา', ['create', 'payableId' => $row->id], ['class' => 'btn btn-sm btn-primary']) ?></td></tr><?php endforeach; ?>
 </tbody></table></div>
</section>

<section class="card border shadow-sm" aria-labelledby="draft-heading">
 <div class="card-header bg-body d-flex justify-content-between"><h5 id="draft-heading" class="mb-0">ร่างฎีกา</h5><span class="badge text-bg-secondary"><?= count($drafts) ?></span></div>
 <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>เลขที่ร่าง</th><th>ผู้รับเงิน</th><th>แหล่งเงิน</th><th>วันที่ขอจ่าย</th><th>วิธีจ่าย</th><th class="text-end">ยอดสุทธิ</th><th></th></tr></thead><tbody>
 <?php if (!$drafts): ?><tr><td colspan="7" class="text-center text-body-secondary py-4">ยังไม่มีร่างฎีกา</td></tr><?php endif; ?>
 <?php foreach ($drafts as $row): ?><tr><td class="fw-semibold"><?= Html::encode($row->voucher_no) ?></td><td><?= Html::encode($row->vendor_name_snapshot) ?></td><td><?= Html::encode($row->funding_source) ?></td><td><?= Yii::$app->formatter->asDate($row->requested_payment_date, 'php:d/m/Y') ?></td><td><?= Html::encode(FinanceVoucher::paymentMethodOptions()[$row->payment_method]) ?></td><td class="text-end"><?= number_format($row->net_amount, 2) ?></td><td class="text-end"><?= Html::a('ดูรายละเอียด', ['view', 'id' => $row->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?>
 </tbody></table></div>
</section>
