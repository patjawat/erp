<?php

use yii\helpers\Html;

$this->title = 'สอบยอดผ้าปี ' . $count['count_year'];
$canManage = $count['status'] === 'OPEN' && Yii::$app->user->can('laundry.manage');
$canApprove = $count['status'] === 'OPEN' && Yii::$app->user->can('laundry.approve')
    && (int) $count['created_by'] !== (int) Yii::$app->user->id;
$allRecorded = true;
foreach ($lines as $line) {
    if ($line['actual_qty'] === null) {
        $allRecorded = false;
        break;
    }
}
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div><h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted"><?= Html::encode($count['department_name'] ?: '#' . $count['department_id']) ?> · ตัดยอด <?= Html::encode($count['cutoff_at']) ?> · <?= $count['status'] === 'APPROVED' ? 'อนุมัติแล้ว' : ($count['status'] === 'CANCELLED' ? 'ยกเลิก' : 'รอตรวจนับ') ?></div></div>
        <?= Html::a('กลับรายการสอบยอด', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    </div>
    <?php if (Yii::$app->session->hasFlash('error')): ?><div class="alert alert-danger"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div><?php endif; ?>
    <div class="alert alert-info">ตรวจนับผ้าในหน่วยงาน และเพิ่มเฉพาะผ้าระหว่างส่งซักที่พิสูจน์ชนิด/จำนวนชิ้นและหน่วยงานต้นทางได้จริง ห้ามแปลงกิโลกรัมเป็นชิ้น ผลต่างยังไม่แก้ยอดบัญชีจนกว่าผู้มีสิทธิ์อนุมัติ หากมีรับ–จ่ายผ้าหลังเปิดรอบ ให้ยกเลิกรอบพร้อมเหตุแล้วเปิดใหม่</div>
    <?php if ($count['status'] === 'CANCELLED'): ?><div class="alert alert-secondary">ยกเลิกรอบ: <?= Html::encode($count['cancel_reason']) ?></div><?php endif; ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ชนิดผ้าที่ต้องตรวจนับ</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">ชนิดผ้า</th><th class="text-end">บัญชี (ชิ้น)</th><th class="text-end">ที่หน่วยงาน</th><th class="text-end">ระหว่างซักที่ยืนยัน</th><th class="text-end">รวมตรวจจริง</th><th class="text-end">ผลต่าง</th><th class="pe-4">เหตุ / บันทึก</th></tr></thead><tbody>
            <?php foreach ($lines as $line): ?>
                <?php $difference = $line['actual_qty'] === null ? null : (int) $line['actual_qty'] - (int) $line['book_qty']; ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($line['item_name']) ?></td>
                    <td class="text-end"><?= number_format($line['book_qty']) ?></td>
                    <td class="text-end"><?= $line['on_hand_qty'] === null ? '—' : number_format($line['on_hand_qty']) ?></td>
                    <td class="text-end"><?= $line['verified_in_transit_qty'] === null ? '—' : number_format($line['verified_in_transit_qty']) ?></td>
                    <td class="text-end"><?= $line['actual_qty'] === null ? 'รอนับ' : number_format($line['actual_qty']) ?></td>
                    <td class="text-end <?= $difference !== null && $difference !== 0 ? 'text-danger fw-semibold' : '' ?>"><?= $difference === null ? '—' : ($difference > 0 ? '+' : '') . number_format($difference) ?></td>
                    <td class="pe-4">
                        <?php if ($canManage): ?>
                            <?= Html::beginForm(['record', 'id' => $count['id']], 'post', ['class' => 'd-flex flex-wrap gap-2 align-items-center']) ?>
                                <?= Html::hiddenInput('item_id', $line['item_id']) ?>
                                <?= Html::input('number', 'on_hand_qty', $line['on_hand_qty'], ['class' => 'form-control form-control-sm w-auto', 'min' => 0, 'step' => 1, 'placeholder' => 'ที่หน่วยงาน', 'required' => true]) ?>
                                <?= Html::input('number', 'verified_in_transit_qty', $line['verified_in_transit_qty'] ?? 0, ['class' => 'form-control form-control-sm w-auto', 'min' => 0, 'step' => 1, 'placeholder' => 'ระหว่างซัก', 'required' => true]) ?>
                                <?= Html::textInput('transit_evidence', $line['transit_evidence'], ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'หลักฐานผ้าระหว่างซัก', 'maxlength' => 255]) ?>
                                <?= Html::textInput('variance_reason', $line['variance_reason'], ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'เหตุผลต่างถ้ามี', 'maxlength' => 255]) ?>
                                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?><?= Html::encode($line['variance_reason'] ?: '—') ?><?php if ($line['transit_evidence']): ?><div class="small text-muted">ระหว่างซัก: <?= Html::encode($line['transit_evidence']) ?></div><?php endif; ?><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody></table></div></div>
    </div>
    <?php if ($canApprove): ?>
        <?= Html::beginForm(['approve', 'id' => $count['id']], 'post', ['class' => 'text-end mt-3']) ?>
            <?= Html::submitButton('อนุมัติผลสอบยอดและปรับบัญชี', ['class' => 'btn btn-success rounded-3', 'disabled' => !$allRecorded, 'data-confirm' => 'ยืนยันผลตรวจนับและปรับยอดบัญชีตามผลต่างทุกชนิดผ้า?']) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
    <?php if ($canManage): ?>
        <?= Html::beginForm(['cancel', 'id' => $count['id']], 'post', ['class' => 'd-flex flex-wrap gap-2 justify-content-end mt-3']) ?>
            <?= Html::textInput('reason', '', ['class' => 'form-control w-auto', 'placeholder' => 'เหตุยกเลิกรอบ', 'required' => true, 'minlength' => 5]) ?>
            <?= Html::submitButton('ยกเลิกรอบเพื่อสอบใหม่', ['class' => 'btn btn-outline-danger rounded-3', 'data-confirm' => 'ยืนยันยกเลิกรอบสอบยอดนี้? ข้อมูลเดิมยังเก็บเป็นประวัติ']) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</div>
