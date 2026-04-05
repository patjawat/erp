<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array{total:int,unread:int,bookmarked:int,urgent:int} $documentStats */
/** @var string|null $activeKpi */

$params = Yii::$app->request->queryParams;
unset($params['page']);

$kpiUrl = static function (?string $key) use ($params): string {
    $p = $params;
    if ($key === null || $key === 'total') {
        unset($p['kpi']);
    } else {
        $p['kpi'] = $key;
    }
    return Url::to(array_merge(['/me/documents/index'], $p));
};

$isKpiActive = static function (?string $key) use ($activeKpi): bool {
    if ($key === null) {
        return $activeKpi === null || $activeKpi === 'total';
    }
    return $activeKpi === $key;
};

/**
 * มาตรฐาน kpi_card: card + เส้นบน accent, body py-3, แถว flex ซ้ายตัวเลข+ป้าย / ขวาไอคอนใน pill
 *
 * @param string $theme primary|secondary|warning|danger
 */
$renderKpiCard = static function (
    string $url,
    int $value,
    string $label,
    string $iconBi,
    string $theme,
    bool $isActive
): string {
    $cardClass = 'card text-decoration-none text-body';
    if ($isActive) {
        $cardClass .= ' border border-' . $theme . ' border-3 border-top-3 border-start-0 border-end-0 border-bottom-0';
    } else {
        $cardClass .= ' border-0 shadow-sm';
    }
    $labelClass = $isActive ? 'text-' . $theme : 'text-muted small';
    $inner = '<div class="card-body py-2">'
        . '<div class="d-flex align-items-center justify-content-between gap-2 mb-2">'
        . '<div class="d-flex flex-column gap-3">'
        . '<span class="fw-bold fs-3">' . (int) $value . '</span>'
        . '<span class="' . Html::encode($labelClass) . '">' . Html::encode($label) . '</span>'
        . '</div>'
        . '<div class="bg-' . $theme . ' bg-opacity-10 text-' . $theme . ' p-3 rounded-pill">'
        . '<i class="bi ' . Html::encode($iconBi) . '" aria-hidden="true"></i>'
        . '</div>'
        . '</div></div>';

    return Html::a($inner, $url, ['class' => $cardClass, 'data-pjax' => 0]);
};

$items = [
    ['key' => null, 'kpi' => 'total', 'label' => 'หนังสือทั้งหมด (รายการ)', 'icon' => 'bi-inbox', 'theme' => 'primary'],
    ['key' => 'unread', 'kpi' => 'unread', 'label' => 'ยังไม่ได้อ่าน (ฉบับ)', 'icon' => 'bi-envelope-open', 'theme' => 'secondary'],
    ['key' => 'bookmarked', 'kpi' => 'bookmarked', 'label' => 'บันทึกไว้', 'icon' => 'bi-bookmark-fill', 'theme' => 'warning'],
    ['key' => 'urgent', 'kpi' => 'urgent', 'label' => 'ด่วนที่สุด (รายการ)', 'icon' => 'bi-lightning-charge-fill', 'theme' => 'danger'],
];
?>

<div class="row g-3 mt-1">
    <?php foreach ($items as $item): ?>
        <div class="col-6 col-xl-3">
            <?= $renderKpiCard(
                $kpiUrl($item['key']),
                (int) $documentStats[$item['kpi']],
                $item['label'],
                $item['icon'],
                $item['theme'],
                $isKpiActive($item['key'])
            ) ?>
        </div>
    <?php endforeach; ?>
</div>
