<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard ลงเวลา';
$this->params['breadcrumbs'][] = ['label' => 'ของฉัน', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
$name = $employee->fullname ?? trim($employee->fname . ' ' . $employee->lname);

/** status → [class, label] ด้วย token semantic */
$statusBadge = function ($status, $label) {
    $cls = $status === 'approved' ? 'is-ok' : ($status === 'rejected' ? 'is-no' : 'is-wait');
    return '<span class="att-badge ' . $cls . '">' . Html::encode($label) . '</span>';
};
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'checkin']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-1 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history"></i>
        ระบบลงเวลาเข้างาน
    </h4>
    <p class="text-muted small mb-0">สวัสดี <?= Html::encode($name) ?> — <?= (isset($isAdminOrHr) && $isAdminOrHr) ? 'สรุปการลงเวลาของฉันและทั้งหน่วยงาน' : 'สรุปการลงเวลาและทางลัดการใช้งาน' ?></p>
</div>
<?php $this->endBlock(); ?>

<div class="att-dash">
    <div class="att-shell">

        <!-- CTA หลัก -->
        <a href="<?= Url::to(['/attendance/default/checkin']) ?>" class="att-cta">
            <span class="att-cta__icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
            <span class="att-cta__body">
                <span class="att-cta__title">ลงเวลาเข้า-ออก</span>
                <span class="att-cta__sub">กดลงเวลาวันนี้ ระบบบันทึกพิกัดอัตโนมัติ</span>
            </span>
            <span class="att-cta__go"><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </a>

        <!-- สถิติของฉัน -->
        <div class="att-strip" aria-label="สถิติของฉัน">
            <div class="att-stat">
                <span class="att-stat__num"><?= (int)$todayCount ?></span>
                <span class="att-stat__label">วันนี้</span>
            </div>
            <div class="att-stat">
                <span class="att-stat__num"><?= (int)$weekCount ?></span>
                <span class="att-stat__label">สัปดาห์นี้</span>
            </div>
            <div class="att-stat">
                <span class="att-stat__num"><?= (int)$monthCount ?></span>
                <span class="att-stat__label">เดือนนี้</span>
            </div>
            <div class="att-stat att-stat--wait">
                <span class="att-stat__num"><?= (int)$pendingCount ?></span>
                <span class="att-stat__label">รออนุมัติ</span>
            </div>
        </div>

        <div class="att-grid">
            <!-- ลงเวลาล่าสุด -->
            <section class="att-card">
                <div class="att-card__head">
                    <h2 class="att-card__title">ลงเวลาล่าสุด</h2>
                    <a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="att-link">ดูทั้งหมด</a>
                </div>
                <div class="att-card__body att-card__body--flush">
                    <?php if ($lastCheckin): ?>
                        <div class="att-last">
                            <div class="att-last__row">
                                <span class="att-last__time"><?= Yii::$app->formatter->asDatetime($lastCheckin->checkin_at, 'php:d/m/Y H:i') ?></span>
                                <?= $statusBadge($lastCheckin->status, $lastCheckin->getStatusLabel()) ?>
                            </div>
                            <p class="att-last__meta"><?= Html::encode($lastCheckin->getCheckTypeLabel()) ?> · <?= Html::encode($lastCheckin->getMethodLabel()) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="att-empty">
                            <p class="att-empty__title">ยังไม่มีรายการลงเวลา</p>
                            <p class="att-empty__sub">กด «ลงเวลาเข้า-ออก» ด้านบนเพื่อเริ่มบันทึกครั้งแรก</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ทางลัด -->
            <section class="att-card">
                <div class="att-card__head">
                    <h2 class="att-card__title">ทางลัด</h2>
                </div>
                <div class="att-card__body">
                    <div class="att-shortcut">
                        <a href="<?= Url::to(['/attendance/default/checkin']) ?>" class="att-tile">
                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                            <span>ลงเวลา</span>
                        </a>
                        <a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="att-tile">
                            <i class="bi bi-list-ul" aria-hidden="true"></i>
                            <span>ประวัติของฉัน</span>
                        </a>
                        <?php if (isset($isAdminOrHr) && $isAdminOrHr): ?>
                        <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="att-tile">
                            <i class="bi bi-people" aria-hidden="true"></i>
                            <span>ทั้งหน่วยงาน</span>
                        </a>
                        <a href="<?= Url::to(['/attendance/checkin/monthly']) ?>" class="att-tile">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <span>สรุปรายเดือน</span>
                        </a>
                        <a href="<?= Url::to(['/attendance/location/index']) ?>" class="att-tile">
                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            <span>จุดลงเวลา</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <?php if (!empty($isAdminOrHr) && $statsAll !== null): ?>
        <!-- ผู้ดูแลระบบ: สรุปทั้งหน่วยงาน -->
        <div class="att-section-head">
            <h3 class="att-section-head__title">สรุปทั้งหน่วยงาน</h3>
            <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="att-btn att-btn--light att-btn--sm">รายงาน</a>
        </div>

        <div class="att-strip" aria-label="สถิติทั้งหน่วยงาน">
            <div class="att-stat">
                <span class="att-stat__num"><?= (int)$statsAll['todayCount'] ?></span>
                <span class="att-stat__label">วันนี้</span>
            </div>
            <div class="att-stat">
                <span class="att-stat__num"><?= (int)$statsAll['weekCount'] ?></span>
                <span class="att-stat__label">สัปดาห์นี้</span>
            </div>
            <div class="att-stat">
                <span class="att-stat__num"><?= (int)$statsAll['monthCount'] ?></span>
                <span class="att-stat__label">เดือนนี้</span>
            </div>
            <div class="att-stat att-stat--wait">
                <span class="att-stat__num"><?= (int)$statsAll['pendingCount'] ?></span>
                <span class="att-stat__label">รออนุมัติ</span>
            </div>
        </div>

        <section class="att-card">
            <div class="att-card__head">
                <h2 class="att-card__title">ลงเวลาล่าสุด (ทั้งหน่วยงาน)</h2>
                <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="att-link">ดูรายงาน</a>
            </div>
            <div class="att-card__body att-card__body--flush">
                <?php if (!empty($recentCheckinsAll)): ?>
                    <!-- Desktop -->
                    <table class="att-table d-none d-lg-table">
                        <thead>
                            <tr>
                                <th>พนักงาน</th>
                                <th>วันเวลา</th>
                                <th>ประเภท</th>
                                <th>วิธี</th>
                                <th>สถานะ</th>
                                <th class="att-table__right">คำสั่ง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCheckinsAll as $r): ?>
                            <tr>
                                <td class="att-table__strong"><?= Html::encode($r->employee ? $r->employee->fname . ' ' . $r->employee->lname : '—') ?></td>
                                <td class="att-table__num"><?= Yii::$app->formatter->asDatetime($r->checkin_at, 'php:d/m/Y H:i') ?></td>
                                <td><?= Html::encode($r->getCheckTypeLabel()) ?></td>
                                <td><?= Html::encode($r->getMethodLabel()) ?></td>
                                <td><?= $statusBadge($r->status, $r->getStatusLabel()) ?></td>
                                <td class="att-table__right">
                                    <div class="att-rowact">
                                        <?= Html::a('<i class="bi bi-eye"></i>', ['/attendance/checkin/view', 'id' => $r->id], ['class' => 'att-iconbtn open-modal', 'title' => 'ดู', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?= Html::a('<i class="bi bi-pencil"></i>', ['/attendance/checkin/update', 'id' => $r->id], ['class' => 'att-iconbtn', 'title' => 'แก้ไข']) ?>
                                        <?= Html::beginForm(['/attendance/checkin/delete', 'id' => $r->id], 'post', ['class' => 'att-del-form d-inline', 'data' => ['name' => ($r->employee ? $r->employee->fname . ' ' . $r->employee->lname : 'รายการนี้')]]) ?>
                                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                        <button type="submit" class="att-iconbtn att-iconbtn--danger" title="ลบ"><i class="bi bi-trash"></i></button>
                                        <?= Html::endForm() ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Mobile -->
                    <ul class="att-cardlist d-lg-none" role="list">
                        <?php foreach ($recentCheckinsAll as $r): ?>
                        <li class="att-rowcard">
                            <div class="att-rowcard__top">
                                <span class="att-rowcard__name"><?= Html::encode($r->employee ? $r->employee->fname . ' ' . $r->employee->lname : '—') ?></span>
                                <?= $statusBadge($r->status, $r->getStatusLabel()) ?>
                            </div>
                            <p class="att-rowcard__meta">
                                <span class="att-table__num"><?= Yii::$app->formatter->asDatetime($r->checkin_at, 'php:d/m/Y H:i') ?></span>
                                · <?= Html::encode($r->getCheckTypeLabel()) ?> · <?= Html::encode($r->getMethodLabel()) ?>
                            </p>
                            <div class="att-rowcard__actions">
                                <?= Html::a('<i class="bi bi-eye"></i> ดู', ['/attendance/checkin/view', 'id' => $r->id], ['class' => 'att-btn att-btn--light att-btn--sm open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                <?= Html::a('<i class="bi bi-pencil"></i> แก้ไข', ['/attendance/checkin/update', 'id' => $r->id], ['class' => 'att-btn att-btn--light att-btn--sm']) ?>
                                <?= Html::beginForm(['/attendance/checkin/delete', 'id' => $r->id], 'post', ['class' => 'att-del-form', 'data' => ['name' => ($r->employee ? $r->employee->fname . ' ' . $r->employee->lname : 'รายการนี้')]]) ?>
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                <button type="submit" class="att-btn att-btn--light att-btn--sm att-btn--danger"><i class="bi bi-trash"></i> ลบ</button>
                                <?= Html::endForm() ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="att-empty"><p class="att-empty__title">ยังไม่มีรายการลงเวลา</p></div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<style>
.att-dash {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);
    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.10);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.10);
    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    color: var(--ink-1);
}
.att-dash .att-shell {
    max-width: 960px;
    margin: 0 auto;
    padding: 1.25rem 0 2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ── CTA ── */
.att-dash .att-cta {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1rem 1.1rem;
    border: 1px solid var(--primary-line);
    border-radius: var(--radius);
    background: var(--surface);
    box-shadow: var(--shadow-1);
    text-decoration: none;
    color: var(--ink-1);
    transition: border-color 140ms var(--ease), box-shadow 140ms var(--ease);
}
.att-dash .att-cta:hover { border-color: var(--primary); box-shadow: 0 4px 14px rgba(13,110,253,0.12); }
.att-dash .att-cta:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--primary-soft); }
.att-dash .att-cta__icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 48px; height: 48px; flex: none;
    border-radius: 12px;
    background: var(--primary); color: #fff;
    font-size: 1.4rem;
}
.att-dash .att-cta__body { display: flex; flex-direction: column; min-width: 0; flex: 1; }
.att-dash .att-cta__title { font-size: 1.05rem; font-weight: 700; color: var(--ink-1); }
.att-dash .att-cta__sub { font-size: 0.82rem; color: var(--ink-3); }
.att-dash .att-cta__go { color: var(--primary); font-size: 1.2rem; flex: none; }

/* ── Stat strip ── */
.att-dash .att-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.65rem;
}
.att-dash .att-stat {
    display: flex; flex-direction: column; align-items: center;
    gap: 0.1rem;
    padding: 0.85rem 0.5rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface);
    box-shadow: var(--shadow-1);
}
.att-dash .att-stat__num {
    font-size: 1.7rem; font-weight: 700; line-height: 1.1;
    color: var(--ink-1); font-variant-numeric: tabular-nums;
}
.att-dash .att-stat__label { font-size: 0.76rem; color: var(--ink-3); }
.att-dash .att-stat--wait .att-stat__num { color: var(--warning); }

/* ── Two-col grid ── */
.att-dash .att-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 767px) {
    .att-dash .att-grid { grid-template-columns: 1fr; }
    .att-dash .att-strip { grid-template-columns: repeat(2, 1fr); }
}

/* ── Card ── */
.att-dash .att-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
}
.att-dash .att-card__head {
    display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
    padding: 0.7rem 1.1rem;
    border-bottom: 1px solid var(--line);
    background: var(--surface-2);
}
.att-dash .att-card__title { margin: 0; font-size: 0.9rem; font-weight: 600; color: var(--ink-2); }
.att-dash .att-card__body { padding: 1rem 1.1rem; }
.att-dash .att-card__body--flush { padding: 0; }
.att-dash .att-link { font-size: 0.82rem; font-weight: 600; color: var(--primary-ink); text-decoration: none; }
.att-dash .att-link:hover { text-decoration: underline; }

/* ── Last checkin ── */
.att-dash .att-last { padding: 0.9rem 1.1rem; }
.att-dash .att-last__row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
.att-dash .att-last__time { font-weight: 600; color: var(--ink-1); font-variant-numeric: tabular-nums; }
.att-dash .att-last__meta { margin: 0.3rem 0 0; font-size: 0.82rem; color: var(--ink-3); }

/* ── Empty ── */
.att-dash .att-empty { padding: 2rem 1.25rem; text-align: center; }
.att-dash .att-empty__title { margin: 0; font-weight: 600; color: var(--ink-2); }
.att-dash .att-empty__sub { margin: 0.3rem 0 0; font-size: 0.83rem; color: var(--ink-3); }

/* ── Shortcut tiles ── */
.att-dash .att-shortcut { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
.att-dash .att-tile {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;
    min-height: 74px; padding: 0.75rem;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--ink-2); text-decoration: none;
    transition: border-color 140ms var(--ease), background 140ms var(--ease), color 140ms var(--ease);
}
.att-dash .att-tile i { font-size: 1.3rem; color: var(--ink-3); transition: color 140ms var(--ease); }
.att-dash .att-tile span { font-size: 0.84rem; font-weight: 600; }
.att-dash .att-tile:hover { border-color: var(--primary-line); background: var(--surface-hover); color: var(--primary-ink); }
.att-dash .att-tile:hover i { color: var(--primary-ink); }
.att-dash .att-tile:focus-visible { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }

/* ── Section head ── */
.att-dash .att-section-head {
    display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--line);
}
.att-dash .att-section-head__title { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--ink-2); }

/* ── Badge ── */
.att-dash .att-badge {
    display: inline-flex; align-items: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.76rem; font-weight: 600; white-space: nowrap;
}
.att-dash .att-badge.is-ok   { background: var(--success-soft); color: var(--success); }
.att-dash .att-badge.is-wait { background: var(--warning-soft); color: var(--warning); }
.att-dash .att-badge.is-no   { background: var(--danger-soft);  color: var(--danger); }

/* ── Table (desktop) ── */
.att-dash .att-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.att-dash .att-table thead th {
    position: sticky; top: 0;
    padding: 0.6rem 0.9rem;
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
    font-size: 0.78rem; font-weight: 600; color: var(--ink-2); text-align: left;
}
.att-dash .att-table tbody td {
    padding: 0.6rem 0.9rem;
    border-bottom: 1px solid var(--line);
    color: var(--ink-2); vertical-align: middle;
}
.att-dash .att-table tbody tr:last-child td { border-bottom: none; }
.att-dash .att-table tbody tr:hover td { background: var(--surface-hover); }
.att-dash .att-table__strong { color: var(--ink-1); font-weight: 600; }
.att-dash .att-table__num { font-variant-numeric: tabular-nums; }
.att-dash .att-table__right { text-align: right; }
.att-dash .att-rowact { display: inline-flex; gap: 0.25rem; justify-content: flex-end; }

/* ── Icon button ── */
.att-dash .att-iconbtn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-xs);
    background: var(--surface);
    color: var(--ink-3);
    cursor: pointer;
    transition: background 140ms var(--ease), color 140ms var(--ease), border-color 140ms var(--ease);
}
.att-dash .att-iconbtn:hover { background: var(--surface-hover); color: var(--ink-1); border-color: var(--line-strong); }
.att-dash .att-iconbtn:focus-visible { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.att-dash .att-iconbtn--danger:hover { background: var(--danger-soft); color: var(--danger); border-color: var(--danger-soft); }

/* ── Card list (mobile) ── */
.att-dash .att-cardlist { list-style: none; margin: 0; padding: 0.6rem; display: flex; flex-direction: column; gap: 0.5rem; }
.att-dash .att-rowcard { border: 1px solid var(--line); border-radius: var(--radius-sm); padding: 0.7rem 0.8rem; background: var(--surface); }
.att-dash .att-rowcard__top { display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; }
.att-dash .att-rowcard__name { font-weight: 600; color: var(--ink-1); }
.att-dash .att-rowcard__meta { margin: 0.35rem 0 0; font-size: 0.8rem; color: var(--ink-3); }
.att-dash .att-rowcard__actions { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.6rem; }

/* ── Buttons ── */
.att-dash .att-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
    min-height: 40px; padding: 0.45rem 0.9rem;
    border: 1px solid transparent; border-radius: var(--radius-sm);
    font-size: 0.88rem; font-weight: 600; text-decoration: none; cursor: pointer;
    transition: background 140ms var(--ease), border-color 140ms var(--ease), color 140ms var(--ease);
}
.att-dash .att-btn--sm { min-height: 34px; padding: 0.35rem 0.7rem; font-size: 0.82rem; }
.att-dash .att-btn--light { background: var(--surface-2); color: var(--ink-1); border-color: var(--line-strong); }
.att-dash .att-btn--light:hover { background: var(--surface-hover); color: var(--ink-1); }
.att-dash .att-btn--light:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--primary-soft); }
.att-dash .att-btn--danger { color: var(--danger); }
.att-dash .att-btn--danger:hover { background: var(--danger-soft); color: var(--danger); border-color: var(--danger-soft); }

@media (prefers-reduced-motion: reduce) {
    .att-dash .att-cta,
    .att-dash .att-tile,
    .att-dash .att-iconbtn,
    .att-dash .att-btn { transition: none; }
}
</style>

<?php
$this->registerJs(<<<JS
$(document).on('submit', '.att-del-form', function(e){
    var form = this;
    if (form.dataset.confirmed === '1') return true;
    e.preventDefault();
    var name = form.getAttribute('data-name') || 'รายการนี้';
    if (!window.Swal) {
        if (confirm('ต้องการลบรายการลงเวลาของ ' + name + ' ใช่หรือไม่?')) { form.dataset.confirmed = '1'; form.submit(); }
        return;
    }
    Swal.fire({
        icon: 'warning',
        title: 'ลบรายการลงเวลา',
        html: 'ต้องการลบรายการของ <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?<br>การลบนี้ย้อนกลับไม่ได้',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash"></i> ลบรายการ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#b91c1c',
        reverseButtons: true,
        customClass: { popup: 'att-swal' }
    }).then(function(res){
        if (res.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
    });
});
JS
);
?>
<style>
.att-swal { border-radius: 12px !important; }
.att-swal .swal2-confirm, .att-swal .swal2-cancel { border-radius: 8px !important; }
@media (prefers-reduced-motion: reduce) {
    .att-swal.swal2-show, .att-swal.swal2-hide { animation: none !important; }
}
</style>
