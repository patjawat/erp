<?php

use app\modules\qms\models\RequirementLink;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Standard[] $standards */
/** @var app\modules\qms\models\Requirement[] $rows */
/** @var int $totalReq */
/** @var int $sharedCount */
/** @var float $sharedPercent */

$this->title = 'เมทริกซ์การเชื่อมโยงข้อกำหนด';
?>
<?php $this->beginBlock('page-title'); ?>Mapping ข้อกำหนด<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>การใช้ข้อกำหนดร่วมข้ามมาตรฐาน (Cross-Standard Shared Control)<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold mb-0"><i class="bi bi-diagram-3 me-1"></i> เมทริกซ์การเชื่อมโยงข้อกำหนด</h1>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ทะเบียนมาตรฐาน', ['standards'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => 'standards']) ?></div>

    <!-- ตัวชี้วัดข้อใช้ร่วม -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card border shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-primary"><?= number_format($sharedCount) ?></div>
                    <div class="text-body-secondary">ข้อกำหนดที่ใช้ร่วม (<?= $sharedPercent ?>%)</div>
                    <div class="small text-body-secondary">จากทั้งหมด <?= number_format($totalReq) ?> ข้อ</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8">
            <div class="card border shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-shield-check text-success"></i> ลดความซ้ำซ้อนของการดำเนินงาน — แนบหลักฐานครั้งเดียว ใช้ได้หลายมาตรฐาน</div>
                    <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-bullseye text-primary"></i> เพิ่มประสิทธิภาพการควบคุม ครอบคลุมหลายมาตรฐาน</div>
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-diagram-3 text-info"></i> สนับสนุนการบูรณาการระบบคุณภาพ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border shadow-sm">
        <div class="card-header bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">เมทริกซ์ ข้อกำหนด × มาตรฐาน</span>
            <span class="small text-body-secondary">
                <span class="badge rounded-pill text-bg-primary">เจ้าของ</span>
                <span class="badge rounded-pill text-bg-success">เชื่อมตรง</span>
                <span class="badge rounded-pill text-bg-warning">บางส่วน</span>
                <span class="text-body-secondary">– ไม่เกี่ยวข้อง</span>
            </span>
        </div>
        <div class="card-body">
            <?php if (empty($rows)): ?>
                <div class="text-center text-body-secondary py-4">
                    <i class="bi bi-diagram-3 fs-3"></i>
                    <div>ยังไม่มีการเชื่อมโยงข้อกำหนดข้ามมาตรฐาน</div>
                    <div class="small">ไปที่ทะเบียนมาตรฐาน → ข้อกำหนด → ปุ่ม “เชื่อมโยง” ของแต่ละข้อ</div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" style="min-width: 720px;">
                        <thead>
                            <tr>
                                <th style="min-width:260px;">ข้อกำหนด</th>
                                <?php foreach ($standards as $std): ?>
                                    <th class="text-center small" title="<?= Html::encode($std->name) ?>"><?= Html::encode($std->short_name ?: $std->code) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $req): ?>
                                <?php
                                $linkByStd = [];
                                foreach ($req->links as $l) {
                                    $linkByStd[(int) $l->standard_id] = $l->relation;
                                }
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($req->code): ?><span class="badge text-bg-light border me-1"><?= Html::encode($req->code) ?></span><?php endif; ?>
                                        <?= Html::encode($req->title) ?>
                                        <div class="small text-body-secondary">เจ้าของ: <?= Html::encode($req->standard->short_name ?? $req->standard->code ?? '') ?></div>
                                    </td>
                                    <?php foreach ($standards as $std): ?>
                                        <td class="text-center">
                                            <?php if ((int) $std->id === (int) $req->standard_id): ?>
                                                <span class="badge rounded-pill text-bg-primary" title="เจ้าของ">●</span>
                                            <?php elseif (isset($linkByStd[(int) $std->id])): ?>
                                                <?php if ($linkByStd[(int) $std->id] === RequirementLink::RELATION_DIRECT): ?>
                                                    <span class="text-success fs-5" title="เชื่อมโยงโดยตรง">●</span>
                                                <?php else: ?>
                                                    <span class="text-warning fs-5" title="เชื่อมโยงบางส่วน">◐</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-body-secondary">–</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
