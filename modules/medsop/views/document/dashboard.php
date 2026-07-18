<?php
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use yii\helpers\Html;
use yii\helpers\Url;

MedSopAsset::register($this);
$this->title = 'บริหารจัดการ SOP /WI';
$total = array_sum(array_map(static fn($row) => (int) $row['total'], $statusCounts));
$published = (int) ($statusCounts[Document::STATUS_PUBLISHED]['total'] ?? 0);
$pending = (int) ($statusCounts[Document::STATUS_PENDING]['total'] ?? 0);
$draft = (int) ($statusCounts[Document::STATUS_DRAFT]['total'] ?? 0);
$maxOrganization = max(1, ...array_map(static fn($row) => (int) $row['total'], $organizationCounts ?: [['total' => 1]]));
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>สืบค้นขั้นตอนปฏิบัติงานมาตรฐาน (Standard Operating Procedure) และรับทราบหลักเกณฑ์ความปลอดภัยล่าสุดได้ทันที เพื่อรักษาระดับคุณภาพมาตรฐานขององค์กร<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav', ['access' => $access, 'active' => 'dashboard']) ?><?php $this->endBlock(); ?>

<div class="medsop-dashboard">
    <section class="row g-3 mb-3" aria-labelledby="dashboard-summary-title">
        <h2 id="dashboard-summary-title" class="visually-hidden">สรุปสถานะเอกสาร</h2>
        <?php foreach ([
            ['ทั้งหมด', $total, 'bi-journals', 'primary', ['index']],
            ['รออนุมัติ', $pending, 'bi-clock-history', 'warning', ['index', 'DocumentSearch[status]' => Document::STATUS_PENDING]],
            ['ฉบับร่าง', $draft, 'bi-file-earmark-text', 'secondary', ['index', 'DocumentSearch[status]' => Document::STATUS_DRAFT]],
            ['เผยแพร่แล้ว', $published, 'bi-check-circle', 'success', ['index', 'DocumentSearch[status]' => Document::STATUS_PUBLISHED]],
        ] as [$label, $value, $icon, $color, $url]): ?>
            <div class="col-6 col-xl-3"><a href="<?= Url::to($url) ?>" class="card border-0 shadow-sm h-100 text-decoration-none medsop-kpi"><div class="card-body d-flex align-items-center gap-3"><span class="medsop-kpi__icon text-<?= $color ?> bg-<?= $color ?> bg-opacity-10"><i class="bi <?= $icon ?>" aria-hidden="true"></i></span><span><small class="text-body-secondary d-block"><?= $label ?></small><strong class="fs-4 text-body medsop-tabular"><?= number_format($value) ?></strong></span></div></a></div>
        <?php endforeach; ?>
    </section>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <section class="card shadow-sm h-100" aria-labelledby="type-title">
                <div class="card-header bg-body-tertiary py-3"><h2 id="type-title" class="h6 fw-semibold mb-0">สัดส่วนประเภทเอกสาร</h2></div>
                <div class="card-body">
                    <?php foreach (Document::typeOptions() as $type => $label): $count = (int) ($typeCounts[$type]['total'] ?? 0); $percent = $total ? round($count * 100 / $total) : 0; ?>
                        <a class="medsop-meter" href="<?= Url::to(['index', 'DocumentSearch[document_type]' => $type]) ?>">
                            <span class="d-flex justify-content-between gap-3"><strong><?= Html::encode($label) ?></strong><span><b><?= number_format($count) ?></b> รายการ</span></span>
                            <span class="medsop-meter__track"><span style="width: <?= $percent ?>%"></span></span>
                            <small><?= number_format($percent) ?>% ของเอกสารทั้งหมด</small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
        <div class="col-12 col-xl-7">
            <section class="card shadow-sm h-100" aria-labelledby="organization-title">
                <div class="card-header bg-body-tertiary py-3"><h2 id="organization-title" class="h6 fw-semibold mb-0">หน่วยงานที่มีเอกสารมากที่สุด</h2></div>
                <div class="card-body">
                    <?php if (!$organizationCounts): ?><p class="text-body-secondary mb-0">ยังไม่มีข้อมูลหน่วยงาน เมื่อสร้างเอกสารแล้วจะแสดงสรุปที่นี่</p><?php endif; ?>
                    <ol class="medsop-ranking mb-0">
                        <?php foreach ($organizationCounts as $row): $organizationId = (int) $row['organization_id']; $count = (int) $row['total']; ?>
                            <li><span class="medsop-ranking__name"><?= Html::encode($organizations[$organizationId]->name ?? 'ไม่ระบุหน่วยงาน') ?></span><span class="medsop-ranking__bar"><span style="width: <?= round($count * 100 / $maxOrganization) ?>%"></span></span><strong><?= number_format($count) ?></strong></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </section>
        </div>
        <div class="col-12">
            <section class="card shadow-sm" aria-labelledby="recent-title">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center gap-2 py-3"><h2 id="recent-title" class="h6 fw-semibold mb-0">อัปเดตล่าสุด</h2><?= Html::a('ดูเอกสารทั้งหมด', ['index'], ['class' => 'btn btn-sm btn-outline-primary']) ?></div>
                <?php if (!$recentDocuments): ?><div class="card-body text-center py-5"><h3 class="h6 fw-semibold">ยังไม่มีเอกสาร</h3><p class="text-body-secondary mb-0">เริ่มสร้างเอกสาร SOP หรือ WI เพื่อให้ทีมใช้งานร่วมกัน</p></div><?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentDocuments as $document): $badge = Document::getStatusBadgeConfigFor($document->status); ?>
                        <a href="<?= Url::to(['view', 'id' => $document->id]) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <span class="medsop-document-icon"><?= Html::encode($document->document_type) ?></span>
                            <span class="flex-grow-1 overflow-hidden"><strong class="d-block text-truncate"><?= Html::encode($document->title) ?></strong><small class="text-body-secondary"><?= Html::encode($document->document_no) ?> · ปรับปรุง <?= Yii::$app->formatter->asRelativeTime($document->updated_at) ?></small></span>
                            <span class="<?= Html::encode($badge['class']) ?> d-none d-sm-inline-flex"><?= Html::encode($badge['label']) ?></span><i class="bi bi-chevron-right text-body-secondary" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
