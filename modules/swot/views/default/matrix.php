<?php

use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotNote;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */
/** @var array $notesByQuadrant */

$isSoar = $model->isSoar();
$this->title = $model->title . ' — ' . ($isSoar ? 'SOAR Roadmap' : 'กลยุทธ์ TOWS');
$quadInfo = SwotNote::QUADRANT_INFO;

$this->registerCss(<<<CSS
.swot-tone-success { background: #198754; } .swot-tone-danger { background: #dc3545; }
.swot-tone-info { background: #0dcaf0; } .swot-tone-warning { background: #fd7e14; }
.swot-tone-primary { background: #0d6efd; } .swot-tone-teal { background: #0d9488; }
.swot-strategy { border: 1px solid var(--bs-border-color); border-left: 3px solid #0d6efd; border-radius: .5rem; padding: .55rem .65rem; margin-bottom: .5rem; }
.swot-strategy:last-child { margin-bottom: 0; }
.swot-strategy-title { font-weight: 600; font-size: .88rem; }
.swot-strategy-desc { font-size: .8rem; color: #555; white-space: pre-wrap; }
.swot-strategy-meta { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .35rem; }
.swot-cell-empty { color: #adb5bd; font-size: .82rem; text-align: center; padding: .75rem 0; }
.swot-strategy-actions button { border: 0; background: transparent; color: #888; padding: 0 .2rem; font-size: .78rem; }
.swot-strategy-actions button:hover { color: #000; }
/* SOAR grid */
.soar-grid-cell { border: 1px solid var(--bs-border-color); border-radius: .6rem; overflow: hidden; height: 100%; }
.soar-grid-head { padding: .45rem .65rem; color: #fff; }
.soar-axis { font-size: .72rem; color: #6c757d; font-weight: 600; }
.soar-flow-arrow { font-size: 1.4rem; color: #0d9488; }
CSS);
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ขั้นตอนที่ 4 · <?= $isSoar ? 'SOAR Matrix — แผนที่กลยุทธ์ S+O → A → R' : 'สังเคราะห์กลยุทธ์จากการจับคู่ปัจจัย (TOWS)' ?><?php $this->endBlock(); ?>

<div class="swot-matrix-view">

    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-arrow-left"></i> คลัง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
            <span class="badge rounded-pill text-bg-<?= $isSoar ? 'primary' : 'success' ?>"><?= SwotBoard::frameworkLabel($model->framework) ?></span>
        </div>
        <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์', 'javascript:window.print()', ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
    </div>

    <?= $this->render('_step_nav', ['model' => $model, 'active' => 'matrix']) ?>

<?php if ($isSoar): ?>
    <!-- ============ SOAR: Grid 2x2 (Internal/External × Present/Future) ============ -->
    <?php
    $cfgSoar = [
        'boardId' => (int) $model->id,
        'csrf' => Yii::$app->request->csrfToken,
        'saveUrl' => Url::to(['matrix-save', 'id' => $model->id]),
        'initiatives' => array_values((array) ($model->soar_matrix['initiatives'] ?? [])),
    ];
    $this->registerJsFile('@web/js/swot-soar.js', ['depends' => [\yii\web\JqueryAsset::class]]);
    $this->registerJs('window.SwotSoar = new SwotSoarApp(' . Json::encode($cfgSoar) . ');');
    // ตำแหน่งในกริด: S=ใน/ปัจจุบัน, O=นอก/ปัจจุบัน, A=ใน/อนาคต, R=นอก/อนาคต
    $grid = [
        'present' => ['strengths', 'opportunities'],
        'future' => ['aspirations', 'results'],
    ];
    ?>
    <div class="alert alert-light border small d-flex align-items-center gap-2 py-2">
        <i class="bi bi-info-circle text-primary"></i>
        SOAR เป็นทั้งการวิเคราะห์และกลยุทธ์ในตัวเดียว — อ่านตารางเป็น roadmap จาก <b>จุดแข็ง + โอกาส (ปัจจุบัน)</b> → <b>ความมุ่งมั่น</b> → <b>ผลลัพธ์ที่วัดได้ (อนาคต)</b>
    </div>

    <div class="row g-2 mb-2">
        <div class="col-2"></div>
        <div class="col-5 text-center soar-axis"><i class="bi bi-building"></i> ภายในองค์กร (Internal)</div>
        <div class="col-5 text-center soar-axis"><i class="bi bi-globe"></i> ภายนอกองค์กร (External)</div>
    </div>
    <?php foreach ($grid as $timeKey => $pair):
        $timeLabel = $timeKey === 'present' ? 'ปัจจุบัน<br>(Present)' : 'อนาคต<br>(Future)';
    ?>
    <div class="row g-2 mb-2">
        <div class="col-2 d-flex align-items-center justify-content-center text-center soar-axis"><span><?= $timeLabel ?></span></div>
        <?php foreach ($pair as $q):
            $info = $quadInfo[$q];
            $notes = $notesByQuadrant[$q] ?? [];
        ?>
        <div class="col-5">
            <div class="soar-grid-cell">
                <div class="soar-grid-head swot-tone-<?= $info['tone'] ?>">
                    <strong><?= $info['code'] ?></strong> <?= $info['short'] ?> <span class="opacity-75">(<?= count($notes) ?>)</span>
                </div>
                <ul class="small mb-0 py-2 ps-4 pe-2">
                    <?php foreach ($notes as $n): ?>
                        <li><?= Html::encode($n->content) ?> <span class="text-muted">[<?= $n->weight ?>]</span></li>
                    <?php endforeach; ?>
                    <?php if (empty($notes)): ?><li class="text-muted fst-italic">— ยังไม่มี —</li><?php endif; ?>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="text-center my-3"><i class="bi bi-arrow-down soar-flow-arrow"></i>
        <div class="small text-muted">แปลงเป็นแผนริเริ่มที่ลงมือทำได้</div>
    </div>

    <!-- รายการริเริ่มเชิงกลยุทธ์ -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex align-items-center">
            <span class="fw-semibold"><i class="bi bi-rocket-takeoff text-primary me-1"></i>ริเริ่มเชิงกลยุทธ์ (S+O → A → R)</span>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 ms-auto" id="swotInitAdd"><i class="bi bi-plus-lg"></i> เพิ่มริเริ่ม</button>
        </div>
        <div class="card-body">
            <div id="swotInitList"></div>
        </div>
    </div>

    <!-- Modal: ริเริ่มกลยุทธ์ SOAR -->
    <div class="modal fade" id="swotInitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="swotInitModalTitle">เพิ่มริเริ่มเชิงกลยุทธ์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="swotInitId">
                    <div class="mb-3">
                        <label class="form-label">ชื่อริเริ่ม/โครงการ <span class="text-danger">*</span></label>
                        <input type="text" id="swotInitTitle" class="form-control" placeholder="เช่น ยกระดับบริการผู้ป่วยนอกด้วยดิจิทัล">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ใช้จุดแข็ง + โอกาสอย่างไร (S+O)</label>
                        <textarea id="swotInitDesc" class="form-control" rows="2" placeholder="อธิบายว่านำจุดแข็งและโอกาสที่มีมาขับเคลื่อนอย่างไร"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">มุ่งสู่ความมุ่งมั่น (Aspiration)</label>
                        <input type="text" id="swotInitAspiration" class="form-control" placeholder="เป้าหมาย/วิสัยทัศน์ที่ต้องการไปถึง">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ตัวชี้วัดผลลัพธ์ (Result / KPI)</label>
                        <input type="text" id="swotInitMetric" class="form-control" placeholder="เช่น ลดเวลารอคอย 30% ภายในปีงบ">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">กรอบเวลา</label>
                            <select id="swotInitTimeframe" class="form-select">
                                <option value="">— ไม่ระบุ —</option>
                                <option value="quick-win">ทำได้ทันที (Quick win)</option>
                                <option value="short-term">ระยะสั้น</option>
                                <option value="medium-term">ระยะกลาง</option>
                                <option value="long-term">ระยะยาว</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">ความสำคัญ</label>
                            <select id="swotInitPriority" class="form-select">
                                <option value="high">สูง</option>
                                <option value="medium" selected>กลาง</option>
                                <option value="low">ต่ำ</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="swotInitSave"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ============ SWOT: TOWS Matrix (จับคู่ 4 ช่อง) ============ -->
    <?php
    $cellMeta = [
        'so' => ['code' => 'SO', 'title' => 'กลยุทธ์เชิงรุก', 'sub' => 'จุดแข็ง × โอกาส', 'tone' => 'success'],
        'wo' => ['code' => 'WO', 'title' => 'กลยุทธ์เชิงปรับปรุง', 'sub' => 'จุดอ่อน × โอกาส', 'tone' => 'info'],
        'st' => ['code' => 'ST', 'title' => 'กลยุทธ์เชิงป้องกัน', 'sub' => 'จุดแข็ง × อุปสรรค', 'tone' => 'warning'],
        'wt' => ['code' => 'WT', 'title' => 'กลยุทธ์เชิงตั้งรับ', 'sub' => 'จุดอ่อน × อุปสรรค', 'tone' => 'danger'],
    ];
    $matrix = is_array($model->tows_matrix) ? $model->tows_matrix : [];
    $cfgTows = [
        'boardId' => (int) $model->id,
        'csrf' => Yii::$app->request->csrfToken,
        'saveUrl' => Url::to(['matrix-save', 'id' => $model->id]),
        'isSoar' => false,
        'matrix' => (object) $matrix,
        'cells' => array_keys($cellMeta),
    ];
    $this->registerJsFile('@web/js/swot-matrix.js', ['depends' => [\yii\web\JqueryAsset::class]]);
    $this->registerJs('window.SwotMatrix = new SwotMatrixApp(' . Json::encode($cfgTows) . ');');
    ?>

    <div class="accordion mb-3" id="swotFactorRef">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#refBody">
                    <i class="bi bi-list-check me-2"></i>ปัจจัยอ้างอิง (จากขั้นตอนระดมประเด็น)
                </button>
            </h2>
            <div id="refBody" class="accordion-collapse collapse" data-bs-parent="#swotFactorRef">
                <div class="accordion-body">
                    <div class="row g-2">
                        <?php foreach ($model->quadrants() as $q):
                            $info = $quadInfo[$q];
                            $notes = $notesByQuadrant[$q] ?? [];
                        ?>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="fw-semibold small text-<?= $info['tone'] === 'teal' ? 'success' : $info['tone'] ?> mb-1"><?= $info['code'] ?> · <?= $info['short'] ?> (<?= count($notes) ?>)</div>
                            <ul class="small text-muted ps-3 mb-0">
                                <?php foreach ($notes as $n): ?><li><?= Html::encode(mb_substr($n->content, 0, 60)) ?></li><?php endforeach; ?>
                                <?php if (empty($notes)): ?><li class="fst-italic">—</li><?php endif; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 swot-matrix-grid">
        <?php foreach ($cellMeta as $key => $meta): ?>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex align-items-center gap-2 text-white swot-tone-<?= $meta['tone'] ?>">
                    <span class="fw-bold"><?= $meta['code'] ?></span>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= $meta['title'] ?></div>
                        <div style="font-size:.72rem;opacity:.9;"><?= $meta['sub'] ?></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-pill swot-cell-add" data-cell="<?= $key ?>"><i class="bi bi-plus-lg"></i></button>
                </div>
                <div class="card-body"><div class="swot-cell-items" data-cell="<?= $key ?>"></div></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal: เพิ่ม/แก้ กลยุทธ์ TOWS -->
    <div class="modal fade" id="swotStrategyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="swotStrategyModalTitle">เพิ่มกลยุทธ์</span> <span class="badge text-bg-secondary" id="swotStrategyCellBadge"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="swotStrategyCell">
                    <input type="hidden" id="swotStrategyId">
                    <div class="mb-3">
                        <label class="form-label">กลยุทธ์ <span class="text-danger">*</span></label>
                        <input type="text" id="swotStrategyTitle" class="form-control" placeholder="ระบุแนวทาง/กลยุทธ์">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด / วิธีดำเนินการ</label>
                        <textarea id="swotStrategyDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">กรอบเวลา</label>
                            <select id="swotStrategyTimeframe" class="form-select">
                                <option value="">— ไม่ระบุ —</option>
                                <option value="quick-win">ทำได้ทันที (Quick win)</option>
                                <option value="short-term">ระยะสั้น</option>
                                <option value="medium-term">ระยะกลาง</option>
                                <option value="long-term">ระยะยาว</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">ความสำคัญ</label>
                            <select id="swotStrategyPriority" class="form-select">
                                <option value="high">สูง</option>
                                <option value="medium" selected>กลาง</option>
                                <option value="low">ต่ำ</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="swotStrategySaveBtn"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>
