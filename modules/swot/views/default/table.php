<?php

use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotNote;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */
/** @var array $notesByQuadrant */

$this->title = $model->title . ' — จัดหมวด & น้ำหนัก';
$quadInfo = SwotNote::QUADRANT_INFO;
$categories = $model->categories();

$this->registerJsFile('@web/js/swot-table.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJs('window.SwotTable = new SwotTableApp(' . Json::encode([
    'csrf' => Yii::$app->request->csrfToken,
    'url' => Url::to(['note-field']),
]) . ');');
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ขั้นตอนที่ 2 · จัดกลุ่มหมวดหมู่และให้ค่าน้ำหนักความสำคัญ<?php $this->endBlock(); ?>

<div class="swot-table-view">

    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-arrow-left"></i> คลัง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
            <span class="badge rounded-pill text-bg-<?= $model->isSoar() ? 'primary' : 'success' ?>"><?= SwotBoard::frameworkLabel($model->framework) ?></span>
            <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>ปีงบ <?= $model->budget_year ?: '-' ?></span>
        </div>
    </div>

    <?= $this->render('_step_nav', ['model' => $model, 'active' => 'table']) ?>

    <div class="alert alert-light border small d-flex align-items-center gap-2 py-2">
        <i class="bi bi-info-circle text-primary"></i>
        แก้หมวดหมู่และค่าน้ำหนักได้ทันที (บันทึกอัตโนมัติ) — ค่าน้ำหนัก 1 = น้อยที่สุด, 5 = สำคัญสูงสุด
    </div>

    <?php foreach ($model->quadrants() as $q):
        $info = $quadInfo[$q];
        $notes = $notesByQuadrant[$q] ?? [];
    ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header d-flex align-items-center gap-2 text-white swot-tone-<?= $info['tone'] ?>">
            <span class="fw-bold"><?= $info['code'] ?></span>
            <span class="fw-semibold"><?= $info['short'] ?></span>
            <span class="badge bg-white text-dark ms-auto"><?= count($notes) ?> ประเด็น</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 swot-tbl">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:240px;">ประเด็น</th>
                        <th style="width:230px;">หมวดหมู่</th>
                        <th style="width:150px;">ค่าน้ำหนัก</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notes)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3 small">— ยังไม่มีประเด็นในช่องนี้ —</td></tr>
                    <?php else: foreach ($notes as $note): /** @var SwotNote $note */ ?>
                    <tr>
                        <td><?= nl2br(Html::encode($note->content)) ?></td>
                        <td>
                            <input type="text" class="form-control form-control-sm swot-cat"
                                   list="swotCatList" data-id="<?= $note->id ?>"
                                   value="<?= Html::encode((string) $note->category) ?>" placeholder="— เลือก/พิมพ์ —">
                        </td>
                        <td>
                            <select class="form-select form-select-sm swot-wt" data-id="<?= $note->id ?>">
                                <?php for ($w = 1; $w <= 5; $w++): ?>
                                    <option value="<?= $w ?>" <?= (int) $note->weight === $w ? 'selected' : '' ?>><?= $w ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <datalist id="swotCatList">
        <?php foreach ($categories as $cat): ?>
            <option value="<?= Html::encode($cat) ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <div class="d-flex justify-content-end">
        <?= Html::a('ไปดูเรดาร์สรุป <i class="bi bi-arrow-right"></i>', ['radar', 'id' => $model->id], ['class' => 'btn btn-primary rounded-pill px-4']) ?>
    </div>
</div>

<?php $this->registerCss(<<<CSS
.swot-tone-success { background: #198754; } .swot-tone-danger { background: #dc3545; }
.swot-tone-info { background: #0dcaf0; } .swot-tone-warning { background: #fd7e14; }
.swot-tone-primary { background: #0d6efd; } .swot-tone-teal { background: #0d9488; }
.swot-tbl td { font-size: .86rem; }
.swot-cat.swot-saved, .swot-wt.swot-saved { border-color: #198754; box-shadow: 0 0 0 .12rem rgba(25,135,84,.2); transition: box-shadow .3s ease; }
CSS);
?>
