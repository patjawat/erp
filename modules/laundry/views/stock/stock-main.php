<?php

use yii\helpers\Html;

/** @var array $rows  item_id,item_name,item_code,balance */
/** @var int $total */
$this->title = 'คลังหลัก — ผ้าสะอาดส่วนกลาง';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'stock']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-building me-2"></i>คลังหลัก</h1>
        <div class="text-body-secondary small">ผ้าสะอาดพร้อมจ่าย เป็นชิ้นรายประเภท (ยอดเพิ่มเมื่อผ้านับหลังอบผ่าน QC · ลดเมื่อเบิกจ่าย)</div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-body px-4 py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>ยอดคงเหลือรายประเภท</h2>
                    <span class="badge text-bg-primary rounded-pill">รวม <?= number_format($total) ?> ชิ้น</span>
                </div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">ประเภทผ้า</th><th>รหัส</th><th class="text-end pe-4">คงเหลือ (ชิ้น)</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= Html::encode($r['item_name']) ?></td>
                                <td class="text-body-secondary"><?= Html::encode($r['item_code']) ?></td>
                                <td class="text-end pe-4 fw-semibold"><?= number_format((int) $r['balance']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$rows): ?>
                            <tr><td colspan="3" class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีประเภทผ้า — เพิ่มที่เมนูตั้งค่า</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div></div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-2"><i class="bi bi-info-circle me-2"></i>เกี่ยวกับคลังหลัก</h2>
                    <p class="text-body-secondary small mb-2">คลังหลักคือจุดรวมผ้าสะอาดของงานซักฟอก เป็นบัญชีของผ้าโดยเฉพาะ ไม่เกี่ยวกับคลังพัสดุ และผ้าไม่มีมูลค่าทางบัญชี</p>
                    <ul class="text-body-secondary small mb-0 ps-3">
                        <li>ยอด <b>เพิ่ม</b> เมื่อผ้านับหลังอบผ่าน QC เข้าเป็นล็อตสะอาด</li>
                        <li>ยอด <b>ลด</b> เมื่อเบิกจ่ายให้หน่วยงาน</li>
                        <li>ผ้าเปื้อนที่เก็บกลับวัดเป็นกิโลกรัม จึงไม่หักคลังหลักโดยตรง</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
