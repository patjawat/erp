<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var array $summary ผลจาก Vehicle::driverStatusByDate() */

$prevDate = date('Y-m-d', strtotime($summary['date'] . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($summary['date'] . ' +1 day'));
$isToday  = $summary['date'] === date('Y-m-d');
?>

<div class="card shadow-sm border-0 driver-status-card">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0 fw-semibold small">
                <i class="bi bi-person-badge me-1 text-primary"></i>พขร. ไม่พร้อมปฏิบัติงาน
                <span class="text-muted fw-normal"><?= ThaiDateHelper::formatThaiDate($summary['date']) ?></span>
                <span class="text-muted fw-normal">·</span>
                <span class="fw-semibold <?= $summary['available'] > 0 ? 'text-success' : 'text-danger' ?>"><?= $summary['available'] ?></span><span class="text-muted fw-normal">/<?= $summary['total'] ?> พร้อม</span>
            </h6>
            <div class="btn-group btn-group-sm" role="group" aria-label="เลือกวันที่">
                <button type="button" class="btn btn-outline-secondary" data-driver-status-date="<?= $prevDate ?>" aria-label="วันก่อนหน้า">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn <?= $isToday ? 'btn-secondary' : 'btn-outline-secondary' ?>" data-driver-status-date="<?= date('Y-m-d') ?>">
                    วันนี้
                </button>
                <button type="button" class="btn btn-outline-secondary" data-driver-status-date="<?= $nextDate ?>" aria-label="วันถัดไป">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <ul class="list-group list-group-flush driver-status-list">
        <?php foreach ($summary['items'] as $item): ?>
            <?php
            $entry = $item['entries'][0];
            $more = count($item['entries']) - 1;

            // รายละเอียดทั้งหมดยุบไว้ใน tooltip เพื่อให้เหลือแถวเดียว
            $tooltip = array_filter(array_merge(
                [$item['fullname'], $item['position']],
                array_map(fn($e) => trim($e['label'] . ' ' . $e['date_range'] . ($e['detail'] !== '' ? ' — ' . $e['detail'] : '')), $item['entries'])
            ));
            ?>
            <li class="list-group-item d-flex align-items-center gap-2 px-3 py-1" title="<?= Html::encode(implode("\n", $tooltip)) ?>">
                <?= Html::img('@web/img/loading.gif', [
                    'class' => 'driver-status-avatar rounded-circle lazyload flex-shrink-0',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item['photo'],
                    ],
                    'alt' => $item['fullname'],
                ]) ?>
                <span class="small text-dark text-truncate flex-grow-1"><?= Html::encode($item['fullname']) ?></span>
                <span class="badge rounded-pill text-dark fw-medium text-nowrap flex-shrink-0" style="background-color:<?= Html::encode($entry['color']) ?>">
                    <?= $entry['icon'] ?> <?= Html::encode($entry['label']) ?><?= $more > 0 ? ' +' . $more : '' ?>
                </span>
                <span class="text-muted fs-12 text-nowrap flex-shrink-0"><?= Html::encode($entry['date_short']) ?></span>
                <?php if (!in_array($entry['status'], ['Approve', 'Pass'], true)): ?>
                    <i class="bi bi-hourglass-split text-warning" title="รออนุมัติ"></i>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

        <?php if (empty($summary['items'])): ?>
            <li class="list-group-item text-center text-muted small py-3">
                <i class="bi bi-check2-circle text-success me-1"></i>
                พขร. พร้อมปฏิบัติงานครบทุกคน
            </li>
        <?php endif; ?>
    </ul>
</div>

<style>
    .driver-status-list {
        max-height: 220px;
        overflow-y: auto;
    }

    .driver-status-avatar {
        width: 22px;
        height: 22px;
        object-fit: cover;
    }

    .driver-status-list .badge {
        font-size: .7rem;
        max-width: 50%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ให้ชื่อถูกตัดก่อน แทนที่จะไปบีบ badge/วันที่ */
    .driver-status-list .text-truncate {
        min-width: 0;
    }
</style>

<?php
$url = Url::to(['/booking/vehicle/driver-status']);
$js = <<<JS
    window.loadDriverStatus = function (date) {
        \$.ajax({
            type: "get",
            url: "$url",
            data: { date: date },
            dataType: "json",
            success: function (response) {
                \$('#showDriverStatus').html(response.content);
            }
        });
    };

    \$(document).off('click.driverStatus').on('click.driverStatus', '#showDriverStatus [data-driver-status-date]', function (e) {
        e.preventDefault();
        window.loadDriverStatus(\$(this).attr('data-driver-status-date'));
    });
JS;
$this->registerJs($js);
?>
