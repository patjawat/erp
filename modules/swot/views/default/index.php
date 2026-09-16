<?php

use app\modules\swot\models\SwotBoard;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoardSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'คลังการวิเคราะห์ SWOT & SOAR';
$boards = $dataProvider->getModels();
?>
<?php $this->beginBlock('page-title'); ?>SWOT &amp; SOAR<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังเครื่องมือวิเคราะห์เชิงกลยุทธ์ของทีม<?php $this->endBlock(); ?>

<div class="swot-index">

    <!-- แถบเครื่องมือ: ค้นหา + ตัวกรอง + ปุ่มสร้าง -->
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap gap-2 align-items-center']) ?>
            <div class="input-group input-group-sm" style="width: 260px;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <?= Html::textInput('q', $searchModel->q, ['class' => 'form-control', 'placeholder' => 'ค้นหาชื่อเรื่อง / วัตถุประสงค์']) ?>
            </div>
            <?= Html::dropDownList('framework', $searchModel->framework, [
                '' => 'ทุกกรอบคิด', 'swot' => 'SWOT', 'soar' => 'SOAR',
            ], ['class' => 'form-select form-select-sm', 'style' => 'width:auto;', 'onchange' => 'this.form.submit()']) ?>
            <?= Html::dropDownList('status', $searchModel->status, [
                '' => 'ทุกสถานะ', 'active' => 'กำลังใช้งาน', 'archived' => 'เก็บเข้าคลัง',
            ], ['class' => 'form-select form-select-sm', 'style' => 'width:auto;', 'onchange' => 'this.form.submit()']) ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">กรอง</button>
        <?= Html::endForm() ?>

        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#swotCreateModal">
            <i class="bi bi-plus-lg me-1"></i> สร้างเรื่องวิเคราะห์ใหม่
        </button>
    </div>

    <?php if (empty($boards)): ?>
        <div class="text-center text-muted py-5 border rounded-4 bg-light">
            <i class="bi bi-clipboard2-data d-block mb-2" style="font-size:2.5rem;"></i>
            <p class="mb-1">ยังไม่มีเรื่องวิเคราะห์</p>
            <p class="small mb-3">เริ่มต้นด้วยการสร้างเรื่องแรก แล้วระดมความคิดเป็นโพสต์อิทในกระดาน</p>
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#swotCreateModal">
                <i class="bi bi-plus-lg me-1"></i> สร้างเรื่องแรก
            </button>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($boards as $board): /** @var SwotBoard $board */
                $isSoar = $board->isSoar();
                $tone = $isSoar ? 'primary' : 'success';
                $noteCount = $board->getNotes()->count();
            ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0 swot-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill text-bg-<?= $tone ?>"><?= SwotBoard::frameworkLabel($board->framework) ?></span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><?= Html::a('<i class="bi bi-pencil-square me-2"></i>เปิดกระดาน', ['board', 'id' => $board->id], ['class' => 'dropdown-item']) ?></li>
                                    <li>
                                        <?= Html::a('<i class="bi bi-trash me-2"></i>ลบ', ['delete', 'id' => $board->id], [
                                            'class' => 'dropdown-item text-danger',
                                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการลบเรื่อง "' . $board->title . '" ? โพสต์อิทในเรื่องนี้จะถูกลบทั้งหมด'],
                                        ]) ?>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <a href="<?= Url::to(['board', 'id' => $board->id]) ?>" class="text-decoration-none text-dark stretched-link-title">
                            <h6 class="fw-semibold mb-1"><?= Html::encode($board->title) ?></h6>
                        </a>
                        <p class="small text-muted flex-grow-1 mb-2" style="min-height:2.4em;">
                            <?= Html::encode(mb_substr((string) $board->objective, 0, 90)) ?><?= mb_strlen((string) $board->objective) > 90 ? '…' : '' ?>
                        </p>

                        <div class="d-flex align-items-center justify-content-between small text-muted border-top pt-2">
                            <span><i class="bi bi-sticky me-1"></i><?= $noteCount ?> ประเด็น</span>
                            <span><i class="bi bi-calendar3 me-1"></i>ปีงบ <?= $board->budget_year ?: '-' ?></span>
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-person me-1"></i><?= Html::encode($board->ownerName) ?>
                        </div>
                    </div>
                    <a href="<?= Url::to(['board', 'id' => $board->id]) ?>" class="card-footer bg-white border-0 text-<?= $tone ?> small fw-semibold text-decoration-none">
                        เปิดกระดาน <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination pagination-sm mb-0'],
                'linkContainerOptions' => ['class' => 'page-item'],
                'linkOptions' => ['class' => 'page-link'],
                'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: สร้างเรื่องใหม่ -->
<div class="modal fade" id="swotCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <?= Html::beginForm(['create'], 'post', ['class' => 'modal-content']) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>สร้างเรื่องวิเคราะห์ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <?= $this->render('_form', ['model' => new SwotBoard()]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>สร้างและเริ่มวิเคราะห์</button>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>

<style>
.swot-card { transition: transform .12s ease, box-shadow .12s ease; }
.swot-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
.text-teal { color: #0d9488 !important; }
.badge.text-bg-teal { background-color: #0d9488 !important; color: #fff; }
</style>
