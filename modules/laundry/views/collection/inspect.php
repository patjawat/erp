<?php

use app\components\widgets\DataSummaryWidget;
use app\components\ThaiDateHelper;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $provider */
/** @var array $stats round_id => [stops, weighed, kg] */
$this->title = 'ตรวจรับผ้า';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'inspect']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-clipboard-check me-2"></i>ตรวจรับผ้า</h1>
        <div class="text-body-secondary small">รอบที่ยังไม่ยืนยัน — ตรวจสอบจำนวนถุง/น้ำหนักให้ครบก่อนยืนยันรับเข้า</div>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle me-2"></i><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="ps-4">เลขรอบ</th><th>วันที่เก็บ</th>
                    <th class="text-end">หน่วยงาน</th><th class="text-end">ชั่งแล้ว</th><th class="text-end">รวม (กก.)</th>
                    <th class="text-end pe-4">ตรวจรับ</th>
                </tr></thead>
                <tbody>
                <?php foreach ($provider->getModels() as $round): $st = $stats[$round->id] ?? ['stops' => 0, 'weighed' => 0, 'kg' => 0]; ?>
                    <?php $ready = $st['stops'] > 0 && (int) $st['stops'] === (int) $st['weighed']; ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($round->round_no) ?></td>
                        <td><?= Html::encode(ThaiDateHelper::formatThaiDate($round->collection_date)) ?></td>
                        <td class="text-end"><?= (int) $st['stops'] ?></td>
                        <td class="text-end">
                            <span class="<?= $ready ? 'text-success-emphasis fw-semibold' : 'text-warning-emphasis' ?>"><?= (int) $st['weighed'] ?>/<?= (int) $st['stops'] ?></span>
                        </td>
                        <td class="text-end fw-semibold"><?= number_format((float) $st['kg'], 1) ?></td>
                        <td class="text-end pe-4">
                            <?= Html::a('<i class="bi bi-search me-1"></i>ตรวจ/ยืนยัน', ['view', 'id' => $round->id],
                                ['class' => 'btn btn-sm ' . ($ready ? 'btn-primary' : 'btn-outline-primary')]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$provider->getModels()): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-5">
                        <i class="bi bi-check2-circle fs-3 d-block mb-2"></i>ไม่มีรอบที่รอตรวจรับ — ทุกรอบยืนยันแล้ว
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div>
        <div class="card-footer bg-body border-top py-3 px-4">
            <?= DataSummaryWidget::widget(['dataProvider' => $provider, 'pagerOptions' => []]) ?>
        </div>
    </div>
</div>
