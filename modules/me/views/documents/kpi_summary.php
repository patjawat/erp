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

$cardClass = static function (?string $key) use ($activeKpi): string {
    $base = 'card border-0 shadow-sm h-100 text-decoration-none text-body';
    if ($key === null) {
        $isActive = $activeKpi === null || $activeKpi === 'total';
    } else {
        $isActive = ($activeKpi === $key);
    }
    return $base . ($isActive ? ' border border-primary border-2' : '');
};
?>

<div class="row g-3 mt-1">
    <div class="col-6 col-xl-3">
        <?= Html::a(
            '<div class="card-body py-3"><div class="d-flex justify-content-between align-items-start gap-2">'
            . '<div class="d-flex flex-column gap-2"><span class="fw-bold fs-3 mb-0">' . (int) $documentStats['total'] . '</span>'
            . '<span class="text-muted small">หนังสือทั้งหมด (รายการ)</span></div>'
            . '<div class="bg-primary bg-opacity-10 text-primary p-3 rounded-pill"><i class="bi bi-inbox" aria-hidden="true"></i></div>'
            . '</div></div>',
            $kpiUrl('total'),
            ['class' => $cardClass(null), 'data-pjax' => 0]
        ) ?>
    </div>
    <div class="col-6 col-xl-3">
        <?= Html::a(
            '<div class="card-body py-3"><div class="d-flex justify-content-between align-items-start gap-2">'
            . '<div class="d-flex flex-column gap-2"><span class="fw-bold fs-3 mb-0">' . (int) $documentStats['unread'] . '</span>'
            . '<span class="text-muted small">ยังไม่ได้อ่าน (ฉบับ)</span></div>'
            . '<div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-pill"><i class="bi bi-envelope-open" aria-hidden="true"></i></div>'
            . '</div></div>',
            $kpiUrl('unread'),
            ['class' => $cardClass('unread'), 'data-pjax' => 0]
        ) ?>
    </div>
    <div class="col-6 col-xl-3">
        <?= Html::a(
            '<div class="card-body py-3"><div class="d-flex justify-content-between align-items-start gap-2">'
            . '<div class="d-flex flex-column gap-2"><span class="fw-bold fs-3 mb-0">' . (int) $documentStats['bookmarked'] . '</span>'
            . '<span class="text-muted small">บันทึกไว้ </span></div>'
            . '<div class="bg-warning bg-opacity-10 text-warning p-3 rounded-pill"><i class="bi bi-bookmark-fill" aria-hidden="true"></i></div>'
            . '</div></div>',
            $kpiUrl('bookmarked'),
            ['class' => $cardClass('bookmarked'), 'data-pjax' => 0]
        ) ?>
    </div>
    <div class="col-6 col-xl-3">
        <?= Html::a(
            '<div class="card-body py-3"><div class="d-flex justify-content-between align-items-start gap-2">'
            . '<div class="d-flex flex-column gap-2"><span class="fw-bold fs-3 mb-0">' . (int) $documentStats['urgent'] . '</span>'
            . '<span class="text-muted small">ด่วนที่สุด (รายการ)</span></div>'
            . '<div class="bg-danger bg-opacity-10 text-danger p-3 rounded-pill"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></div>'
            . '</div></div>',
            $kpiUrl('urgent'),
            ['class' => $cardClass('urgent'), 'data-pjax' => 0]
        ) ?>
    </div>
</div>
