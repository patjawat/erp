<?php

use yii\db\Expression;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\am\models\AssetDetail;

/** @var yii\web\View $this */

// จำนวนวันที่ถือว่า "ใกล้หมดอายุ" และเงื่อนไขย้อนหลังของรายการหมดอายุไปแล้ว
$thresholdDays = 60;
$expiredLookbackDays = 365;
$today = new DateTime(date('Y-m-d'));
$upperBound = (clone $today)->modify("+{$thresholdDays} days")->format('Y-m-d');
$lowerBound = (clone $today)->modify("-{$expiredLookbackDays} days")->format('Y-m-d');
$headingId = 'expiring-insurance-heading';

// Push date filter ลง SQL: กรองที่ระดับฐานข้อมูลด้วย พ.ร.บ. (date_end1)
// อ่านค่า JSON ใน data_json ผ่าน JSON_UNQUOTE(JSON_EXTRACT(...)) — ตัด rows ที่ไม่เข้าเกณฑ์ก่อนถึง PHP
$pordororDateExpr = new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.date_end1'))");

$rows = AssetDetail::find()
    ->with('asset')
    ->where(['name' => 'vehicle_tax'])
    ->andWhere(['between', $pordororDateExpr, $lowerBound, $upperBound])
    ->all();

// คำนวณจำนวนวันคงเหลือจากวันที่ในรูปแบบ Y-m-d
$daysLeft = function ($dateEnd) use ($today) {
    if (empty($dateEnd) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd)) {
        return null;
    }
    $end = DateTime::createFromFormat('Y-m-d', $dateEnd);
    if (!$end) {
        return null;
    }
    return (int) $today->diff($end)->format('%r%a');
};

$alerts = [];
foreach ($rows as $r) {
    $dataJson = is_array($r->data_json) ? $r->data_json : [];

    // ใช้ พ.ร.บ. (date_end1) เป็นเกณฑ์หลัก ถ้าไม่ใกล้หมดอายุ ข้ามคันนี้ไป (รวมหมดอายุไปแล้วด้วย)
    $pordororDateEnd = $dataJson['date_end1'] ?? null;
    $pordororDays = $daysLeft($pordororDateEnd);
    if ($pordororDays === null || $pordororDays > $thresholdDays) {
        continue;
    }

    $asset = $r->asset;
    $license = $asset->license_plate ?? $r->code;
    $assetName = $asset->asset_name ?? '-';

    $items = [
        ['type' => 'พ.ร.บ.', 'date_end' => $pordororDateEnd, 'days_left' => $pordororDays],
    ];

    $taxDays = $daysLeft($r->date_end);
    if ($taxDays !== null && $taxDays <= $thresholdDays) {
        $items[] = ['type' => 'ภาษีรถ', 'date_end' => $r->date_end, 'days_left' => $taxDays];
    }

    $insureDays = $daysLeft($dataJson['date_end2'] ?? null);
    if ($insureDays !== null && $insureDays <= $thresholdDays) {
        $items[] = ['type' => 'ประกันภัย', 'date_end' => $dataJson['date_end2'], 'days_left' => $insureDays];
    }

    foreach ($items as $it) {
        $alerts[] = $it + [
            'license' => $license,
            'asset_name' => $assetName,
            'asset_code' => $r->code,
            'detail_id' => $r->id,
        ];
    }
}

// เรียงตามวันคงเหลือจากน้อยไปมาก (หมดอายุไปแล้ว/ใกล้หมดสุดขึ้นก่อน) ถ้าวันเท่ากันให้จัดกลุ่มตามทะเบียนและประเภท
$typeOrder = ['พ.ร.บ.' => 0, 'ภาษีรถ' => 1, 'ประกันภัย' => 2];
usort($alerts, function ($a, $b) use ($typeOrder) {
    if ($a['days_left'] !== $b['days_left']) {
        return $a['days_left'] <=> $b['days_left'];
    }
    if ($a['license'] !== $b['license']) {
        return strcmp($a['license'], $b['license']);
    }
    return ($typeOrder[$a['type']] ?? 99) <=> ($typeOrder[$b['type']] ?? 99);
});
?>

<section class="card border-0 shadow-sm expiring-insurance-card" aria-labelledby="<?= $headingId ?>">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="<?= $headingId ?>" class="h6 text-white mb-0">
            <i class="bi bi-shield-exclamation me-1" aria-hidden="true"></i>พ.ร.บ./ประกัน ใกล้หมดอายุ
            <span class="badge rounded-pill bg-white bg-opacity-25 text-white fw-medium ms-1 vd-num"
                aria-label="<?= count($alerts) ?> รายการใกล้หมดอายุ"><?= count($alerts) ?></span>
        </h4>
        <p class="small text-white-50 mb-0">ภายใน <?= $thresholdDays ?> วันข้างหน้า</p>
    </div>
    <div class="card-body">
        <?php if (empty($alerts)): ?>
            <div class="text-muted text-center py-4 small">ไม่มีรายการที่ใกล้หมดอายุภายใน <?= $thresholdDays ?> วัน</div>
        <?php else: ?>
            <ul class="expiring-list d-flex flex-column gap-2 list-unstyled mb-0" aria-label="รายการ พ.ร.บ./ประกัน ใกล้หมดอายุ">
                <?php foreach ($alerts as $alert): ?>
                    <?php
                    $endDateThai = ThaiDateHelper::formatThaiDate($alert['date_end'], 'short');
                    $isExpired = $alert['days_left'] <= 0;

                    if ($isExpired || $alert['days_left'] <= 15) {
                        $colorClass = 'text-danger';
                    } elseif ($alert['days_left'] <= 30) {
                        $colorClass = 'text-warning';
                    } else {
                        $colorClass = 'text-success';
                    }

                    $viewUrl = Url::to(['/am/vehicle-tax/view', 'id' => $alert['detail_id'], 'title' => '<i class="fa-solid fa-tag"></i> รายละเอียดการต่อภาษี/พ.ร.บ./ประกัน']);
                    $statusText = $isExpired ? 'หมดอายุแล้ว' : 'คงเหลืออีก ' . $alert['days_left'] . ' วัน';
                    $linkLabel = sprintf(
                        '%s ทะเบียน %s · %s · %s · ดูรายละเอียด',
                        $alert['type'],
                        $alert['license'],
                        $alert['asset_name'],
                        $statusText
                    );
                    ?>
                    <li>
                        <a href="<?= $viewUrl ?>"
                            data-size="modal-lg"
                            aria-label="<?= Html::encode($linkLabel) ?>"
                            class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 rounded text-decoration-none text-body expiring-item border open-modal">
                            <div class="d-flex align-items-center gap-3 flex-grow-1 expiring-item-main">
                                <span class="badge bg-light text-dark border rounded expiring-license text-nowrap" aria-hidden="true">
                                    <?= Html::encode($alert['license']) ?>
                                </span>
                                <div class="expiring-item-text">
                                    <div class="fw-semibold" aria-hidden="true"><?= Html::encode($alert['type']) ?></div>
                                    <div class="text-muted small text-truncate" aria-hidden="true">
                                        <?= Html::encode($alert['asset_name']) ?> · หมด <?= $endDateThai ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end <?= $colorClass ?> flex-shrink-0 ms-auto" aria-hidden="true">
                                <?php if ($isExpired): ?>
                                    <div class="fw-bold">หมดอายุแล้ว</div>
                                <?php else: ?>
                                    <div class="fw-bold">อีก <?= $alert['days_left'] ?> วัน</div>
                                    <div class="text-muted small">คงเหลือ</div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
