<?php

use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotNote;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */
/** @var array $notesByQuadrant */

$this->title = $model->title;
$quadInfo = SwotNote::QUADRANT_INFO;
$colorStyles = SwotNote::COLOR_STYLES;

$config = [
    'boardId' => (int) $model->id,
    'csrf' => Yii::$app->request->csrfToken,
    'urls' => [
        'save' => Url::to(['note-save']),
        'delete' => Url::to(['note-delete']),
        'move' => Url::to(['note-move']),
    ],
    'colors' => $colorStyles,
    'quadInfo' => $quadInfo,
];
$this->registerJsFile('@web/js/swot-board.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJs('window.SwotBoard = new SwotBoardApp(' . Json::encode($config) . ');');

$aiConfig = [
    'csrf' => Yii::$app->request->csrfToken,
    'url' => Url::to(['ai-analyze', 'id' => $model->id]),
    'isSoar' => $model->isSoar(),
    'analysis' => is_array($model->ai_analysis) ? $model->ai_analysis : null,
];
$this->registerJsFile('@web/js/swot-ai.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJs('window.SwotAi = new SwotAiApp(' . Json::encode($aiConfig) . ');');
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->objective ?: 'กระดานวิเคราะห์เชิงกลยุทธ์') ?><?php $this->endBlock(); ?>

<div class="swot-board">

    <!-- แถบหัว: กลับ / ข้อมูลเรื่อง / แก้ไข -->
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-arrow-left"></i> คลัง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
            <span class="badge rounded-pill text-bg-<?= $model->isSoar() ? 'primary' : 'success' ?>">
                <?= SwotBoard::frameworkLabel($model->framework) ?>
            </span>
            <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>ปีงบ <?= $model->budget_year ?: '-' ?></span>
            <span class="small text-muted"><i class="bi bi-person me-1"></i><?= Html::encode($model->ownerName) ?></span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="swotAiBtn" data-bs-toggle="modal" data-bs-target="#swotAiModal">
                <i class="bi bi-robot me-1"></i> วิเคราะห์ด้วย AI
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#swotEditBoardModal">
                <i class="bi bi-gear me-1"></i> แก้ไขข้อมูลเรื่อง
            </button>
        </div>
    </div>

    <?= $this->render('_step_nav', ['model' => $model, 'active' => 'canvas']) ?>

    <!-- ตาราง 4 ช่อง -->
    <div class="row g-3 swot-grid">
        <?php foreach ($model->quadrants() as $q):
            $info = $quadInfo[$q];
            $notes = $notesByQuadrant[$q] ?? [];
        ?>
        <div class="col-12 col-lg-6">
            <div class="swot-quadrant" data-quadrant="<?= $q ?>">
                <div class="swot-quadrant-head swot-tone-<?= $info['tone'] ?>">
                    <div class="d-flex align-items-center gap-2">
                        <span class="swot-code"><?= $info['code'] ?></span>
                        <div>
                            <div class="fw-semibold"><?= $info['short'] ?></div>
                            <div class="swot-sub"><?= $info['sub'] ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-pill swot-add-btn" data-quadrant="<?= $q ?>">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <div class="swot-notes" data-quadrant="<?= $q ?>">
                    <?php foreach ($notes as $note): /** @var SwotNote $note */
                        $cs = $note->colorStyle();
                    ?>
                    <div class="swot-note" draggable="true"
                         data-id="<?= $note->id ?>" data-quadrant="<?= $note->quadrant ?>"
                         data-content="<?= Html::encode($note->content) ?>"
                         data-color="<?= $note->color ?>" data-priority="<?= $note->priority ?>"
                         data-weight="<?= $note->weight ?>" data-category="<?= Html::encode((string) $note->category) ?>"
                         style="background:<?= $cs['bg'] ?>;border-color:<?= $cs['border'] ?>;">
                        <div class="swot-note-content"><?= nl2br(Html::encode($note->content)) ?></div>
                        <div class="swot-note-foot">
                            <span class="swot-weight" title="ค่าน้ำหนักความสำคัญ">
                                <i class="bi bi-star-fill"></i> <?= $note->weight ?>
                            </span>
                            <span class="swot-note-actions">
                                <button type="button" class="swot-edit" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                                <button type="button" class="swot-del" title="ลบ"><i class="bi bi-trash"></i></button>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swot-empty text-center text-muted small py-3<?= empty($notes) ? '' : ' d-none' ?>">
                    ยังไม่มีประเด็น — กด <i class="bi bi-plus-lg"></i> เพื่อเพิ่ม
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: เพิ่ม/แก้ โพสต์อิท -->
<div class="modal fade" id="swotNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span id="swotNoteModalTitle">เพิ่มประเด็น</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="swotNoteId" value="">
                <input type="hidden" id="swotNoteQuadrant" value="">
                <div class="mb-3">
                    <label class="form-label">เนื้อหา <span class="text-danger">*</span></label>
                    <textarea id="swotNoteContent" class="form-control" rows="3" placeholder="พิมพ์ประเด็นที่ต้องการบันทึก..."></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">ค่าน้ำหนัก</label>
                        <select id="swotNoteWeight" class="form-select">
                            <option value="1">1 · น้อยที่สุด</option>
                            <option value="2">2 · น้อย</option>
                            <option value="3" selected>3 · ปานกลาง</option>
                            <option value="4">4 · มาก</option>
                            <option value="5">5 · สำคัญสูงสุด</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">ความสำคัญ</label>
                        <select id="swotNotePriority" class="form-select">
                            <option value="high">สูง</option>
                            <option value="medium" selected>กลาง</option>
                            <option value="low">ต่ำ</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">สีโพสต์อิท</label>
                    <div class="swot-color-picker" id="swotColorPicker">
                        <?php foreach ($colorStyles as $key => $cs): ?>
                            <button type="button" class="swot-color-dot" data-color="<?= $key ?>"
                                    style="background:<?= $cs['bg'] ?>;border-color:<?= $cs['border'] ?>;"
                                    title="<?= $cs['name'] ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="swotNoteSaveBtn"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ขยายดูโพสต์อิท -->
<div class="modal fade" id="swotZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" id="swotZoomCard">
            <div class="modal-header border-0 pb-0">
                <span class="badge rounded-pill" id="swotZoomQuad"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="swotZoomContent" style="font-size:1.05rem; line-height:1.5; white-space:pre-wrap; word-break:break-word; min-height:120px;"></div>
                <div class="d-flex gap-3 mt-3 pt-3 border-top small text-muted">
                    <span><i class="bi bi-star-fill text-warning"></i> ค่าน้ำหนัก <b id="swotZoomWeight"></b></span>
                    <span><i class="bi bi-flag"></i> ความสำคัญ <b id="swotZoomPriority"></b></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="swotZoomDel"><i class="bi bi-trash me-1"></i>ลบ</button>
                <button type="button" class="btn btn-primary" id="swotZoomEdit"><i class="bi bi-pencil me-1"></i>แก้ไข</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ผลวิเคราะห์ AI -->
<div class="modal fade" id="swotAiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-robot me-2 text-primary"></i>ผลวิเคราะห์ด้วย AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="swotAiLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary mb-3"></div>
                    <div class="text-muted">กำลังให้ AI วิเคราะห์... (อาจใช้เวลาสักครู่)</div>
                </div>
                <div id="swotAiEmpty" class="text-center py-4">
                    <i class="bi bi-robot d-block mb-2 text-primary" style="font-size:2.5rem;"></i>
                    <p class="mb-3">ให้ AI ช่วยสรุปภาพรวม ประเมินความสมดุล และแนะนำกลยุทธ์จากประเด็นที่ระดมไว้</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="swotAiRunBtn"><i class="bi bi-stars me-1"></i>เริ่มวิเคราะห์</button>
                </div>
                <div id="swotAiResult" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary rounded-pill px-3 d-none" id="swotAiRerunBtn"><i class="bi bi-arrow-clockwise me-1"></i>วิเคราะห์ใหม่</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: แก้ไขข้อมูลเรื่อง -->
<div class="modal fade" id="swotEditBoardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <?= Html::beginForm(['update-board', 'id' => $model->id], 'post', ['class' => 'modal-content']) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>แก้ไขข้อมูลเรื่อง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?= $this->render('_form', ['model' => $model]) ?>
                <div class="mt-2">
                    <label class="form-label">สถานะ</label>
                    <?= Html::activeDropDownList($model, 'status', [
                        'active' => 'กำลังใช้งาน', 'archived' => 'เก็บเข้าคลัง',
                    ], ['class' => 'form-select']) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>

<?php $this->registerCss(<<<CSS
.swot-quadrant { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: 1rem; overflow: hidden; height: 100%; display: flex; flex-direction: column; }
.swot-quadrant-head { display: flex; align-items: center; justify-content: space-between; padding: .6rem .85rem; color: #fff; }
.swot-tone-success { background: #198754; } .swot-tone-danger { background: #dc3545; }
.swot-tone-info { background: #0dcaf0; } .swot-tone-warning { background: #fd7e14; }
.swot-tone-primary { background: #0d6efd; } .swot-tone-teal { background: #0d9488; }
.swot-code { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.25); display: grid; place-items: center; font-weight: 700; font-size: 1.05rem; }
.swot-sub { font-size: .72rem; opacity: .9; }
.swot-notes { padding: .6rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(128px, 1fr)); gap: .65rem; min-height: 96px; flex-grow: 1; align-content: start; }
.swot-notes.swot-dragover { background: rgba(13,110,253,.06); outline: 2px dashed rgba(13,110,253,.35); outline-offset: -6px; }
/* โพสต์อิทมุมฉากแบบกระดาษจริง — จตุรัส auto-fit ข้อความยาวย่อไว้ คลิกเพื่อขยาย */
.swot-note { border: 1px solid; border-radius: 0; padding: .5rem .55rem; box-shadow: 2px 3px 6px rgba(0,0,0,.13); cursor: pointer; color: #111; aspect-ratio: 1 / 1; display: flex; flex-direction: column; overflow: hidden; transition: transform .12s ease, box-shadow .12s ease; }
/* เอียงเล็กน้อยสลับกันแบบกระดาษแปะจริง (hover/drag จะตั้งตรง) */
.swot-note:nth-child(5n+1) { transform: rotate(-1.6deg); }
.swot-note:nth-child(5n+2) { transform: rotate(1.1deg); }
.swot-note:nth-child(5n+3) { transform: rotate(-0.6deg); }
.swot-note:nth-child(5n+4) { transform: rotate(1.8deg); }
.swot-note:nth-child(5n+5) { transform: rotate(-1.1deg); }
.swot-note:hover { transform: rotate(0deg) translateY(-3px) scale(1.02); box-shadow: 3px 6px 13px rgba(0,0,0,.22); z-index: 2; }
.swot-note.swot-dragging { opacity: .5; transform: rotate(0deg); }
.swot-note-content { font-size: .82rem; line-height: 1.3; white-space: pre-wrap; word-break: break-word; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }
.swot-note-foot { display: flex; align-items: center; justify-content: space-between; margin-top: .35rem; flex-shrink: 0; }
.swot-weight { font-size: .72rem; color: #b8860b; }
.swot-note-actions button { border: 0; background: transparent; color: #555; padding: 0 .2rem; font-size: .8rem; }
.swot-note-actions button:hover { color: #000; }
.swot-color-picker { display: flex; flex-wrap: wrap; gap: .4rem; }
.swot-color-dot { width: 30px; height: 30px; border-radius: 50%; border: 2px solid; cursor: pointer; }
.swot-color-dot.selected { outline: 3px solid rgba(13,110,253,.5); outline-offset: 1px; }
CSS);
?>
