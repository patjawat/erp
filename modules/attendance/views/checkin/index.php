<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\widgets\DataSummaryWidget;

$this->title = 'ประวัติการลงเวลาของฉัน';
$this->params['breadcrumbs'][] = ['label' => 'ของฉัน', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;

$isAdminOrHr = $isAdminOrHr ?? false;

/** status → [class, label] ด้วย token semantic */
$statusBadge = function ($status, $label) {
    $cls = $status === 'approved' ? 'is-ok' : ($status === 'rejected' ? 'is-no' : 'is-wait');
    return '<span class="att-badge ' . $cls . '">' . Html::encode($label) . '</span>';
};

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'history']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-1 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">รายการลงเวลาเข้า-ออกของคุณ เรียงจากล่าสุด</p>
</div>
<?php $this->endBlock(); ?>

<div class="att-hist">
    <div class="att-shell">

        <div class="att-topbar">
            <a href="<?= Url::to(['/attendance/default/checkin']) ?>" class="att-btn att-btn--primary att-btn--sm">
                <i class="bi bi-plus-lg"></i> ลงเวลา
            </a>
            <?php if ($isAdminOrHr): ?>
            <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="att-btn att-btn--light att-btn--sm">
                <i class="bi bi-people"></i> ดูของทั้งหน่วยงาน
            </a>
            <?php endif; ?>
        </div>

        <!-- ตัวกรอง: เลือกช่วงเวลาสำเร็จรูป หรือกำหนดวันที่เอง -->
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
            'options' => ['class' => 'att-filter'],
            'fieldConfig' => ['template' => '{input}', 'options' => ['class' => 'att-field']],
        ]); ?>
            <div class="att-filter__grid">
                <div class="att-filter__cell">
                    <label class="att-filter__lbl">ช่วงเวลา</label>
                    <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                </div>
                <div class="att-filter__cell">
                    <label class="att-filter__lbl">ตั้งแต่</label>
                    <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                </div>
                <div class="att-filter__cell">
                    <label class="att-filter__lbl">ถึง</label>
                    <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                </div>
                <div class="att-filter__cell">
                    <label class="att-filter__lbl">สถานะ</label>
                    <?= $form->field($searchModel, 'status')->dropdownList([
                        '' => 'ทุกสถานะ',
                        'pending' => 'รออนุมัติ',
                        'approved' => 'อนุมัติแล้ว',
                        'rejected' => 'ไม่อนุมัติ',
                    ], ['class' => 'form-select']) ?>
                </div>
                <div class="att-filter__actions">
                    <?= Html::submitButton('<i class="bi bi-search"></i> ค้นหา', ['class' => 'att-btn att-btn--primary att-btn--sm']) ?>
                    <?= Html::a('ล้าง', ['index'], ['class' => 'att-btn att-btn--light att-btn--sm']) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>

        <!-- รายการ -->
        <div class="att-card">
            <?php if (empty($models)): ?>
                <div class="att-empty">
                    <p class="att-empty__title">ยังไม่มีประวัติการลงเวลา</p>
                    <p class="att-empty__sub">เมื่อคุณกดลงเวลาเข้า-ออก รายการจะแสดงที่นี่</p>
                    <a href="<?= Url::to(['/attendance/default/checkin']) ?>" class="att-btn att-btn--primary att-btn--sm mt-2"><i class="bi bi-plus-lg"></i> ลงเวลาครั้งแรก</a>
                </div>
            <?php else: ?>
                <!-- Desktop -->
                <table class="att-table d-none d-lg-table">
                    <thead>
                        <tr>
                            <th class="att-table__no">#</th>
                            <th>วันที่</th>
                            <th class="att-table__right">เวลา</th>
                            <th>ประเภท</th>
                            <th>วิธี</th>
                            <th>สถานะ</th>
                            <th class="att-table__right">คำสั่ง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($models as $idx => $m): ?>
                        <?php $no = $pagination ? ($pagination->offset + $idx + 1) : ($idx + 1); ?>
                        <tr>
                            <td class="att-table__no"><?= (int)$no ?></td>
                            <td class="att-table__num"><?= $m->checkin_at ? Yii::$app->formatter->asDate($m->checkin_at, 'php:d/m/Y') : '—' ?></td>
                            <td class="att-table__num att-table__right"><?= $m->checkin_at ? Yii::$app->formatter->asTime($m->checkin_at, 'php:H:i') : '—' ?></td>
                            <td><?= Html::encode($m->getCheckTypeLabel()) ?></td>
                            <td class="att-table__muted"><?= Html::encode($m->getMethodLabel()) ?></td>
                            <td><?= $statusBadge($m->status, $m->getStatusLabel()) ?></td>
                            <td class="att-table__right">
                                <?= Html::a('<i class="bi bi-eye"></i>', ['/attendance/checkin/view', 'id' => $m->id], ['class' => 'att-iconbtn open-modal', 'title' => 'ดูรายละเอียด', 'data' => ['size' => 'modal-lg']]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Mobile -->
                <ul class="att-cardlist d-lg-none" role="list">
                    <?php foreach ($models as $m): ?>
                    <li class="att-rowcard">
                        <div class="att-rowcard__top">
                            <span class="att-rowcard__date">
                                <?= $m->checkin_at ? Yii::$app->formatter->asDate($m->checkin_at, 'php:d/m/Y') : '—' ?>
                                <span class="att-rowcard__time"><?= $m->checkin_at ? Yii::$app->formatter->asTime($m->checkin_at, 'php:H:i') : '' ?></span>
                            </span>
                            <?= $statusBadge($m->status, $m->getStatusLabel()) ?>
                        </div>
                        <div class="att-rowcard__bottom">
                            <span class="att-rowcard__meta"><?= Html::encode($m->getCheckTypeLabel()) ?> · <?= Html::encode($m->getMethodLabel()) ?></span>
                            <?= Html::a('<i class="bi bi-eye"></i> ดู', ['/attendance/checkin/view', 'id' => $m->id], ['class' => 'att-btn att-btn--light att-btn--sm open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <div class="att-card__foot">
                    <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.att-hist{
    --ink-1:#1a202c;--ink-2:#4a5568;--ink-3:#718096;--ink-4:#a0aec0;
    --surface:#fff;--surface-2:#f7f9fc;--surface-3:#eef2f7;--surface-hover:#f1f5f9;
    --line:rgba(15,23,42,.08);--line-strong:rgba(15,23,42,.14);
    --primary:#0d6efd;--primary-ink:#0a58ca;--primary-soft:rgba(13,110,253,.08);--primary-line:rgba(13,110,253,.22);
    --success:#15803d;--success-soft:rgba(21,128,61,.1);--warning:#b45309;--warning-soft:rgba(180,83,9,.1);--danger:#b91c1c;--danger-soft:rgba(185,28,28,.1);
    --radius:10px;--radius-sm:8px;--radius-xs:6px;
    --shadow-1:0 1px 2px rgba(15,23,42,.04),0 1px 1px rgba(15,23,42,.03);
    --ease:cubic-bezier(.16,1,.3,1);color:var(--ink-1);
}
.att-hist .att-shell{max-width:900px;margin:0 auto;padding:1.25rem 0 2rem;display:flex;flex-direction:column;gap:1rem}
.att-hist .att-topbar{display:flex;flex-wrap:wrap;gap:.5rem}

/* filter */
.att-hist .att-filter{margin:0}
.att-hist .att-filter__grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr)) auto;gap:.6rem;align-items:end;padding:.9rem 1rem;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
@media (max-width:767px){.att-hist .att-filter__grid{grid-template-columns:1fr 1fr}.att-hist .att-filter__actions{grid-column:1/-1}}
.att-hist .att-filter__cell{min-width:0}
.att-hist .att-filter__lbl{display:block;font-size:.78rem;font-weight:600;color:var(--ink-2);margin-bottom:.3rem}
.att-hist .att-filter .att-field{margin:0}
.att-hist .att-filter .form-control,.att-hist .att-filter .form-select{min-height:40px;border:1px solid var(--line-strong);border-radius:var(--radius-sm);font-size:.88rem;color:var(--ink-1);width:100%;padding:.35rem .6rem;background:var(--surface)}
.att-hist .att-filter .form-control:focus,.att-hist .att-filter .form-select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
.att-hist .att-filter .help-block,.att-hist .att-filter .invalid-feedback{display:none}
/* Select2 (preset ช่วงเวลา) → token look */
.att-hist .att-filter .select2-container{width:100%!important}
.att-hist .att-filter .select2-container .select2-selection{min-height:40px;border:1px solid var(--line-strong)!important;border-radius:var(--radius-sm)!important;display:flex;align-items:center;box-shadow:none!important;background:var(--surface)}
.att-hist .att-filter .select2-selection__rendered{padding-left:.2rem;color:var(--ink-1);line-height:1.4}
.att-hist .att-filter .select2-selection__placeholder{color:var(--ink-4)}
.att-hist .att-filter .select2-selection__arrow{top:8px;right:8px}
.att-hist .att-filter__actions{display:flex;gap:.4rem;align-items:end}

/* card + table */
.att-hist .att-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow-1);overflow:hidden}
.att-hist .att-table{width:100%;border-collapse:collapse;font-size:.9rem}
.att-hist .att-table thead th{padding:.6rem .9rem;background:var(--surface-2);border-bottom:1px solid var(--line);font-size:.78rem;font-weight:600;color:var(--ink-2);text-align:left}
.att-hist .att-table tbody td{padding:.6rem .9rem;border-bottom:1px solid var(--line);color:var(--ink-1);vertical-align:middle}
.att-hist .att-table tbody tr:last-child td{border-bottom:none}
.att-hist .att-table tbody tr:hover td{background:var(--surface-hover)}
.att-hist .att-table__no{width:44px;color:var(--ink-3);font-variant-numeric:tabular-nums}
.att-hist .att-table__num{font-variant-numeric:tabular-nums}
.att-hist .att-table__muted{color:var(--ink-3)}
.att-hist .att-table__right{text-align:right}

/* badge */
.att-hist .att-badge{display:inline-flex;align-items:center;padding:.15rem .55rem;border-radius:999px;font-size:.76rem;font-weight:600;white-space:nowrap}
.att-hist .att-badge.is-ok{background:var(--success-soft);color:var(--success)}
.att-hist .att-badge.is-wait{background:var(--warning-soft);color:var(--warning)}
.att-hist .att-badge.is-no{background:var(--danger-soft);color:var(--danger)}

/* icon button */
.att-hist .att-iconbtn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid var(--line-strong);border-radius:var(--radius-xs);background:var(--surface);color:var(--ink-3);cursor:pointer;transition:background 140ms var(--ease),color 140ms var(--ease),border-color 140ms var(--ease)}
.att-hist .att-iconbtn:hover{background:var(--surface-hover);color:var(--ink-1)}
.att-hist .att-iconbtn:focus-visible{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}

/* mobile cards */
.att-hist .att-cardlist{list-style:none;margin:0;padding:.6rem;display:flex;flex-direction:column;gap:.5rem}
.att-hist .att-rowcard{border:1px solid var(--line);border-radius:var(--radius-sm);padding:.7rem .8rem;background:var(--surface)}
.att-hist .att-rowcard__top{display:flex;align-items:center;justify-content:space-between;gap:.6rem}
.att-hist .att-rowcard__date{font-weight:600;color:var(--ink-1);font-variant-numeric:tabular-nums}
.att-hist .att-rowcard__time{color:var(--ink-3);font-weight:500;margin-left:.35rem}
.att-hist .att-rowcard__bottom{display:flex;align-items:center;justify-content:space-between;gap:.6rem;margin-top:.5rem}
.att-hist .att-rowcard__meta{font-size:.82rem;color:var(--ink-3)}

/* empty */
.att-hist .att-empty{padding:3rem 1.5rem;text-align:center;display:flex;flex-direction:column;align-items:center}
.att-hist .att-empty__title{margin:0;font-weight:600;color:var(--ink-2);font-size:1.05rem}
.att-hist .att-empty__sub{margin:.3rem 0 0;font-size:.88rem;color:var(--ink-3)}

/* footer / pager */
.att-hist .att-card__foot{padding:.75rem 1rem;border-top:1px solid var(--line);background:var(--surface-2)}
.att-hist .att-card__foot .pagination{margin-bottom:0}

/* buttons */
.att-hist .att-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;min-height:40px;padding:.45rem .9rem;border:1px solid transparent;border-radius:var(--radius-sm);font-size:.9rem;font-weight:600;text-decoration:none;cursor:pointer;transition:background 140ms var(--ease),border-color 140ms var(--ease),color 140ms var(--ease)}
.att-hist .att-btn--sm{min-height:36px;padding:.35rem .75rem;font-size:.85rem}
.att-hist .att-btn--primary{background:var(--primary);color:#fff;border-color:var(--primary)}
.att-hist .att-btn--primary:hover{background:var(--primary-ink);border-color:var(--primary-ink);color:#fff}
.att-hist .att-btn--primary:focus-visible{outline:none;box-shadow:0 0 0 3px var(--primary-soft)}
.att-hist .att-btn--light{background:var(--surface-2);color:var(--ink-1);border-color:var(--line-strong)}
.att-hist .att-btn--light:hover{background:var(--surface-hover);color:var(--ink-1)}

@media (prefers-reduced-motion:reduce){.att-hist .att-iconbtn,.att-hist .att-btn{transition:none}}
</style>
