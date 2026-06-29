<?php

use yii\helpers\Html;

/** @var array $warehouses */
/** @var int|null $selectedWarehouseId */

$this->title = 'ย้ายประวัติทั้งคลัง → V2 (history-only)';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="history"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-3 border-info">
    <div class="card-body">
        <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-circle-info me-1"></i>
            วิธีใช้งานเมื่อย้ายข้อมูลจริง
        </h6>

        <ol class="mb-3 ps-3">
            <li class="mb-2">
                สำรองข้อมูลก่อนรันจริง โดยเฉพาะตาราง <code>stock_order</code>,
                <code>stock_detail</code> และ <code>stock_balance</code>
            </li>
            <li class="mb-2">
                รันคำสั่งย้ายประวัติจาก Inventory เดิมมายัง Inventory V2
            </li>
            <li class="mb-2">
                รันคำสั่งคำนวณยอดคงเหลือแบบตรวจสอบผลก่อน โดยยังไม่เขียนฐานข้อมูล
            </li>
            <li>
                เมื่อผลตรวจสอบถูกต้องแล้ว จึงรันคำสั่งเขียนยอดคงเหลือจริง
            </li>
        </ol>

        <div class="mb-3">
            <div class="fw-bold mb-1">คำสั่งที่ใช้ใน Docker</div>
            <pre class="bg-light border rounded p-3 mb-0 small"><code>docker exec -w /app dansai php -d error_reporting=22527 yii transfer-history-all-v2/run
docker exec -w /app dansai php -d error_reporting=22527 yii sync-stock-balance/recalc
docker exec -w /app dansai php -d error_reporting=22527 yii sync-stock-balance/recalc --apply</code></pre>
        </div>

        <div class="alert alert-warning mb-0">
            <div class="fw-bold mb-1">ข้อควรทราบ</div>
            <div>
                คำสั่ง <code>transfer-history-all-v2/run</code> ย้ายเฉพาะประวัติไปที่
                <code>stock_order</code> และ <code>stock_detail</code> เท่านั้น
                ยังไม่อัปเดต <code>stock_balance</code> จึงต้องรัน
                <code>sync-stock-balance/recalc --apply</code> ต่อเพื่อให้หน้า Balance แสดงยอดคงเหลือ
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-info-subtle">
        <h6 class="mt-2 mb-0">
            <i class="fa-solid fa-circle-info me-1 text-info"></i>
            คำอธิบายการใช้งาน
        </h6>
    </div>
    <div class="card-body small">
        <p class="mb-2">
            หน้านี้ใช้ <strong>ย้ายประวัติใบเบิก/รับเข้า</strong> จากระบบเดิม (V1, ตาราง <code>stock_events</code>)
            ไปยังระบบใหม่ (V2, ตาราง <code>stock_order</code> + <code>stock_detail</code>) แบบ
            <span class="badge bg-secondary">history-only</span> —
            <u>ไม่กระทบ <code>stock_balance</code> / FIFO</u> ไม่ทำให้ยอดคงเหลือเพี้ยน
        </p>

        <ol class="mb-3 ps-3">
            <li>เลือก <strong>วิธีย้าย</strong> ตามสถานการณ์:
                <ul>
                    <li><strong>ย้ายทั้งคลัง</strong> (หน้านี้) — เลือก warehouse_id ที่ต้องการ → กด "ย้ายประวัติทั้งคลัง"</li>
                    <li><strong>ย้ายรายตัว</strong> — เปิดผ่าน <code>/inventory/transfer-to-v2/item-history?stock_id=…</code>
                        แล้ว tick ใบเฉพาะที่ต้องการ (สำหรับ inspect/debug)</li>
                    <li><strong>ย้ายทั้งระบบ</strong> — ใช้ console command (รายละเอียดด้านล่าง)
                        เพราะ HTTP request อาจ timeout ถ้าข้อมูลใหญ่</li>
                </ul>
            </li>
            <li>ระบบ <strong>idempotent</strong> — รันซ้ำกี่ครั้งก็ได้ ใบที่
                <code>order_no</code> ซ้ำใน V2 แล้วจะถูก skip อัตโนมัติ ไม่สร้างซ้ำ</li>
            <li>ใบที่ <strong>map ไม่ได้</strong> (เช่น <code>asset_item</code> ไม่อยู่ใน V2 master, หรือ
                <code>warehouse_id</code> ไม่ตรง) จะถูกข้ามและรายงานในสรุปผล</li>
            <li>ขอบเขตข้อมูล: เฉพาะใบ <code>order_status='success'</code> และ
                <code>transaction_type IN ('IN','OUT')</code></li>
        </ol>

        <div class="bg-light p-3 rounded mb-2">
            <div class="fw-bold text-warning mb-2">
                <i class="fa-solid fa-terminal me-1"></i>
                ย้ายทั้งระบบ (ทุกคลัง) — ผ่าน console command
            </div>
            <p class="mb-2 text-muted">
                สำหรับข้อมูลที่ใหญ่มาก (หลายพัน-หลายหมื่นใบ) ห้ามรันผ่านหน้าเว็บนี้
                เพราะจะ timeout — ใช้ console แทน (รัน background ได้ปลอดภัยกว่า)
            </p>
<pre class="bg-dark text-white p-2 rounded mb-2 small">
# preview รายการคลังที่จะถูกย้าย (dry-run)
php yii transfer-history-all-v2/run --dry-run

# ย้ายทั้งระบบ
php yii transfer-history-all-v2/run

# ย้ายเฉพาะคลังเดียว (debug)
php yii transfer-history-all-v2/run --warehouse-id=12
</pre>
            <p class="mb-0 text-muted">
                Command นี้จะวนทีละ warehouse → ใช้ memory จำกัด → idempotent
                (รันซ้ำหลังหยุดกลางคันได้เลย) · ผลลัพธ์แสดงสรุปต่อคลังและสรุปรวมท้ายสุด
            </p>
        </div>

        <div class="alert alert-warning mb-0 py-2">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            <strong>ระวัง:</strong> ตรวจ <code>item_code</code> ที่ขาดใน V2 master ก่อนรัน
            เพราะ line เหล่านั้นจะถูก skip ทั้งหมด — ดู report ที่ <code>/inventory/stock-item</code>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0">
            <i class="fa-solid fa-warehouse me-1"></i>
            ย้ายทั้งคลัง (เลือก warehouse_id เดียว)
        </h6>
    </div>
    <div class="card-body">
        <?= Html::beginForm(['bulk-item-history-save'], 'post', ['id' => 'bulk-form', 'class' => 'row g-2 align-items-end']) ?>
            <div class="col-md-8">
                <label class="form-label fw-bold">คลังปลายทาง</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">— เลือกคลัง —</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int) $w['id'] ?>"
                            <?= $selectedWarehouseId === (int) $w['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($w['warehouse_name']) ?>
                            <?= !empty($w['warehouse_type']) ? ' [' . Html::encode($w['warehouse_type']) . ']' : '' ?>
                            (id=<?= (int) $w['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <?= Html::submitButton('<i class="fa-solid fa-clone me-1"></i> ย้ายประวัติทั้งคลัง', [
                    'class' => 'btn btn-primary w-100',
                    'data' => ['confirm' => 'ยืนยันย้ายประวัติทุกใบใน warehouse นี้เข้า V2 (history-only, idempotent)?'],
                ]) ?>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>
