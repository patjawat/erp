<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ล้างยอดคลัง (สำหรับทดสอบ)';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'ปรับยอด stock', 'url' => ['/inventory-v2/stock-adjust/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-arrow-counterclockwise fs-4 text-warning"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">ตั้งยอดคงเหลือในคลังที่เลือกเป็น 0 ทั้งหมด — ใช้เพื่อทดสอบระบบใหม่เท่านั้น (ไม่ลบเอกสารรับ/จ่าย)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปปรับยอด', ['/inventory-v2/stock-adjust/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="card border-warning shadow-sm rounded-3">
        <div class="card-header bg-warning bg-opacity-10">
            <h6 class="mb-0 text-dark"><i class="bi bi-exclamation-triangle me-1"></i>คำเตือน</h6>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">การล้างยอดจะตั้ง <strong>ยอดคงเหลือ (stock_balance)</strong> ในคลังที่เลือกเป็น <strong>0</strong> ทุกรายการ เอกสารรับเข้า/จ่ายออก (stock_order, stock_detail) จะไม่ถูกลบ — เหมาะสำหรับการทดสอบ flow รับเข้า/จ่ายใหม่จากศูนย์</p>

            <form method="post" action="<?= Url::to(['/inventory-v2/stock-adjust/reset-warehouse']) ?>">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">คลังที่ต้องการล้างยอด <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select" required>
                            <?php foreach ($warehouses as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (int)($selectedWarehouseId ?? 0) === (int)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">พิมพ์คำว่า <strong>ล้าง</strong> เพื่อยืนยัน <span class="text-danger">*</span></label>
                        <input type="text" name="confirm_text" class="form-control" placeholder="ล้าง" maxlength="10" autocomplete="off">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> ล้างยอดคลังที่เลือก
                    </button>
                    <?= Html::a('ยกเลิก', ['/inventory-v2/stock-adjust/index'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
                </div>
            </form>
        </div>
    </div>
</div>
