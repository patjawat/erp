<?php

use yii\web\View;
use yii\db\Expression;
use yii\helpers\Html;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $isMine */

$this->title = $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนงานซ่อม';

$isMine = !empty($isMine);

/** soft-tinted badge เหมือน convention ทั้ง helpdesk (Bootstrap utility) */
$soft = static function (string $color): string {
    return 'badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$statusMeta = Helpdesk::repairStatusMeta();

/* ---------- current status filter (multi) ---------- */
$currentStatusFilter = $searchModel->status ?? [];
if (!is_array($currentStatusFilter)) {
    $currentStatusFilter = $currentStatusFilter !== null && $currentStatusFilter !== '' ? [(string) $currentStatusFilter] : [];
}
$currentStatusFilter = array_values(array_filter(array_map('strval', $currentStatusFilter), static fn($v) => $v !== ''));
$currentStatusFilter = array_values(array_map(
    static fn(string $status): string => Helpdesk::normalizeRepairStatus($status),
    $currentStatusFilter
));

/* ---------- status counts — single GROUP BY (เดิม 5+1 COUNT ต่อโหลด) ---------- */
$statusCounts = array_fill_keys(array_keys($statusMeta), 0);
if (isset($dataProvider->query)) {
    try {
        $rows = (clone $dataProvider->query)
            ->limit(-1)->offset(-1)->orderBy([])
            ->select(['helpdesk.status', 'cnt' => new Expression('COUNT(*)')])
            ->groupBy('helpdesk.status')
            ->asArray()
            ->all();
        foreach ($rows as $r) {
            $code = Helpdesk::normalizeRepairStatus($r['status'] ?? '');
            if (isset($statusCounts[$code])) {
                $statusCounts[$code] = (int) $r['cnt'];
            }
        }
    } catch (\Throwable $e) {
        // เก็บค่า 0 ไว้ตามเดิม
    }
}

/* ---------- URL builders ---------- */
$buildStatusUrl = static function (?string $statusCode) use ($searchModel): array {
    $params = Yii::$app->request->queryParams;
    $formName = $searchModel->formName();
    $params[$formName] = is_array($params[$formName] ?? null) ? $params[$formName] : [];
    if ($statusCode === null || $statusCode === '') {
        unset($params[$formName]['status']);
    } else {
        $current = $params[$formName]['status'] ?? [];
        if (!is_array($current)) {
            $current = $current !== null && $current !== '' ? [(string) $current] : [];
        }
        $current = array_values(array_filter(array_map('strval', $current), static fn($v) => $v !== ''));
        if (in_array($statusCode, $current, true)) {
            $current = array_values(array_diff($current, [$statusCode]));
        } else {
            $current[] = $statusCode;
        }
        if (empty($current)) {
            unset($params[$formName]['status']);
        } else {
            $params[$formName]['status'] = $current;
        }
    }
    return array_merge(['/' . Yii::$app->controller->route], $params);
};
$buildMineUrl = static function (bool $mine): array {
    $params = Yii::$app->request->queryParams;
    if ($mine) {
        $params['mine'] = 1;
    } else {
        unset($params['mine']);
    }
    return array_merge(['/' . Yii::$app->controller->route], $params);
};

/* ---------- models + batch prefetch (ตัด N+1) ---------- */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$offset = $pagination ? $pagination->getOffset() : 0;

$creatorUserIds = [];
$repairIds = [];
foreach ($models as $m) {
    if (!empty($m->created_by)) {
        $creatorUserIds[] = (int) $m->created_by;
    }
    $repairIds[] = (int) $m->id;
}
$empsByUserId = $creatorUserIds
    ? Employees::find()->where(['user_id' => array_values(array_unique($creatorUserIds))])->indexBy('user_id')->all()
    : [];

$teamEmpIdsByRepair = [];
$allTeamEmpIds = [];
if ($repairIds) {
    foreach (HelpdeskDetail::find()->where(['name' => 'repair_team', 'helpdesk_id' => $repairIds])->all() as $tr) {
        $rid = (int) $tr->helpdesk_id;
        $eid = (int) $tr->emp_id;
        if ($eid <= 0) {
            continue;
        }
        $teamEmpIdsByRepair[$rid][] = $eid;
        $allTeamEmpIds[] = $eid;
    }
}
$teamEmpsById = $allTeamEmpIds
    ? Employees::find()->where(['id' => array_values(array_unique($allTeamEmpIds))])->indexBy('id')->all()
    : [];
$deviceTypeByCode = Categorise::find()->where(['name' => 'device_type'])->indexBy('code')->all();
$urgencyByCode = Categorise::find()->where(['name' => 'helpdesk_urgency'])->indexBy('code')->all();

/* ---------- caches สำหรับ method ที่อาจ query ซ้ำต่อ emp ---------- */
$personCache = [];
$resolvePerson = function ($emp) use (&$personCache) {
    if ($emp === null) {
        return ['name' => '', 'dept' => '', 'avatar' => null];
    }
    $key = (int) $emp->id;
    if (isset($personCache[$key])) {
        return $personCache[$key];
    }
    return $personCache[$key] = [
        'name' => (string) $emp->fullname,
        'dept' => (string) $emp->departmentName(),
        'avatar' => $emp->ShowAvatar(),
    ];
};

/* ---------- render helpers ---------- */
$renderPerson = function ($info) {
    $name = trim((string) ($info['name'] ?? ''));
    $dept = trim((string) ($info['dept'] ?? ''));
    if ($name === '' && $dept === '') {
        return '<span class="hd-empty">—</span>';
    }
    if (!empty($info['avatar'])) {
        $img = Html::img('@web/img/loading.gif', [
            'class' => 'hd-person__avatar lazyload',
            'data' => ['src' => $info['avatar'], 'expand' => '-20', 'sizes' => 'auto'],
            'alt' => '',
        ]);
    } else {
        $initial = $name !== '' ? mb_substr($name, 0, 1, 'UTF-8') : '?';
        $img = '<span class="hd-person__avatar hd-person__avatar--ph" aria-hidden="true">' . Html::encode($initial) . '</span>';
    }
    $out = '<div class="hd-person">' . $img . '<div class="hd-person__meta">';
    $out .= '<div class="hd-person__name" title="' . Html::encode($name) . '">' . Html::encode($name !== '' ? $name : '—') . '</div>';
    if ($dept !== '') {
        $out .= '<div class="hd-person__sub" title="' . Html::encode($dept) . '">' . Html::encode($dept) . '</div>';
    }
    return $out . '</div></div>';
};

$teamAvatarCache = [];
$renderTeam = function ($model) use ($teamEmpIdsByRepair, $teamEmpsById, &$teamAvatarCache) {
    $ids = $teamEmpIdsByRepair[(int) $model->id] ?? [];
    if (empty($ids)) {
        return '<span class="hd-empty">—</span>';
    }
    $out = '<div class="hd-avatar-stack">';
    foreach ($ids as $eid) {
        $emp = $teamEmpsById[$eid] ?? null;
        if (!$emp) {
            continue;
        }
        if (!isset($teamAvatarCache[$eid])) {
            $teamAvatarCache[$eid] = ['src' => $emp->ShowAvatar(), 'name' => (string) $emp->fullname];
        }
        $out .= Html::img('@web/img/loading.gif', [
            'class' => 'hd-avatar-stack__img lazyload',
            'data' => ['src' => $teamAvatarCache[$eid]['src'], 'expand' => '-20', 'sizes' => 'auto'],
            'alt' => '',
            'title' => $teamAvatarCache[$eid]['name'],
        ]);
    }
    return $out . '</div>';
};

$statusBadge = function ($model) use ($statusMeta, $soft) {
    $code = Helpdesk::normalizeRepairStatus($model->status ?? 'pending');
    $info = $statusMeta[$code] ?? [
        'label' => 'ไม่ทราบสถานะ',
        'color' => 'secondary',
        'icon' => 'fa-regular fa-circle-question',
    ];
    $content = Html::tag('i', '', [
        'class' => $info['icon'] . ' me-1',
        'aria-hidden' => 'true',
    ]) . Html::encode($info['label']);
    return Html::tag('span', $content, ['class' => $soft($info['color'])]);
};

$urgencyBadge = function ($model) use ($urgencyByCode, $soft) {
    $rawCode = is_array($model->data_json ?? null) ? ($model->data_json['urgency'] ?? '') : '';
    $code = Helpdesk::normalizeRepairUrgency($rawCode);
    $info = Helpdesk::repairUrgencyInfo($rawCode);
    $cat = $code !== '' ? ($urgencyByCode[$code] ?? null) : null;
    $label = trim((string) ($cat->title ?? $info['label']));
    $content = Html::tag('i', '', [
        'class' => $info['icon'] . ' me-1',
        'aria-hidden' => 'true',
    ]) . Html::encode($label);
    // ใช้เฉพาะ title สั้น (สูง / ปานกลาง / ต่ำ) — คำอธิบายเต็มอยู่หน้ารายละเอียด
    return Html::tag('span', $content, ['class' => $soft($info['color']), 'title' => $label]);
};

/** จำนวนวันนับจากวันแจ้ง — inline แบบ subtle (สีเฉพาะเมื่อค้างเกิน 7 วัน) */
$agingDays = function ($model): ?int {
    if (empty($model->created_at)) {
        return null;
    }
    try {
        $d0 = (new \DateTimeImmutable((string) $model->created_at))->setTime(0, 0, 0);
        $d1 = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
        return $d0 > $d1 ? 0 : (int) $d0->diff($d1)->days;
    } catch (\Throwable $e) {
        return null;
    }
};
$agingInline = function ($model) use ($agingDays) {
    $days = $agingDays($model);
    if ($days === null) {
        return '';
    }
    $cls = $days > 7 ? 'hd-aging hd-aging--over' : ($days > 3 ? 'hd-aging hd-aging--warn' : 'hd-aging');
    return '<span class="' . $cls . '" title="นับจากวันที่แจ้งซ่อม">' . $days . ' วัน</span>';
};
$agingBadge = function ($model) use ($agingDays, $soft) {
    $days = $agingDays($model);
    if ($days === null) {
        return '';
    }
    $color = $days <= 3 ? 'secondary' : ($days <= 7 ? 'warning' : 'danger');
    return Html::tag('span', 'ผ่านมาแล้ว ' . $days . ' วัน', ['class' => $soft($color), 'title' => 'นับจากวันที่แจ้งซ่อม']);
};

/* ---------- actions (คงคลาส/URL เดิมไว้ให้ handler ทำงาน) ---------- */
$backUrl = Yii::$app->request->url;
$viewReturn = (is_string($backUrl) && $backUrl !== '' && $backUrl[0] === '/' && strpos($backUrl, '://') === false
    && substr($backUrl, 0, 2) !== '//' && preg_match('#^/helpdesk/#', $backUrl)) ? $backUrl : null;

$renderActions = function ($model, bool $iconOnly) use ($viewReturn) {
    if (Helpdesk::normalizeRepairStatus($model->status) === 'pending') {
        $label = $iconOnly ? '<i class="fa-solid fa-circle-exclamation"></i>' : '<i class="fa-solid fa-circle-exclamation me-1"></i> รับเรื่อง';
        return Html::a($label, ['/helpdesk/service/receive', 'id' => $model->id], [
            'class' => 'receive-order btn btn-sm btn-outline-primary',
            'aria-label' => 'รับเรื่อง',
            'title' => 'รับเรื่อง',
        ]);
    }

    $viewParams = ['/helpdesk/service/view-v2', 'id' => $model->id];
    if ($viewReturn !== null) {
        $viewParams['returnUrl'] = $viewReturn;
    }
    $btns = [];
    $btns[] = Html::a(
        $iconOnly ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye me-1"></i> บันทึกงานซ่อม',
        $viewParams,
        ['class' => 'btn btn-sm btn-outline-primary', 'aria-label' => 'บันทึกงานซ่อม', 'title' => 'บันทึกงานซ่อม']
    );
    $btns[] = Html::a(
        $iconOnly ? '<i class="fa-solid fa-file-pdf"></i>' : '<i class="fa-solid fa-file-pdf me-1"></i> พิมพ์ใบส่งซ่อม',
        ['/helpdesk/service/print-send-repair-pdf', 'id' => $model->id],
        ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank', 'aria-label' => 'พิมพ์ใบส่งซ่อม', 'title' => 'พิมพ์ใบส่งซ่อม']
    );
    $btns[] = Html::a(
        $iconOnly ? '<i class="bi bi-pencil"></i>' : '<i class="bi bi-pencil me-1"></i> แก้ไข',
        ['/helpdesk/service/update', 'id' => $model->id, 'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],
        ['class' => 'btn btn-sm btn-outline-info open-modal', 'data' => ['size' => 'modal-lg'], 'aria-label' => 'แก้ไข', 'title' => 'แก้ไข']
    );
    $btns[] = Html::a(
        $iconOnly ? '<i class="fa-solid fa-ban"></i>' : '<i class="fa-solid fa-ban me-1"></i> ยกเลิก',
        ['/helpdesk/service/cancel', 'id' => $model->id, 'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],
        ['class' => 'btn btn-sm btn-outline-warning cancel-order', 'aria-label' => 'ยกเลิกงานซ่อม', 'title' => 'ยกเลิกงานซ่อม']
    );
    return implode('', $btns);
};

$hasFilter = !empty($currentStatusFilter) || $isMine
    || !empty($searchModel->q) || !empty($searchModel->urgency)
    || !empty($searchModel->date_start) || !empty($searchModel->date_end) || !empty($searchModel->q_department);
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <?= $icon ?> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>

<div class="helpdesk2-service-list">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="m-0"><i class="fa-solid fa-magnifying-glass me-1"></i> การค้นหา</h6>
        </div>
        <div class="card-body">
            <?= $this->render('@app/modules/helpdesk2/views/service/_search', ['model' => $searchModel]) ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="hd-list-head">
            <h2 class="hd-list-head__title">
                <i class="bi bi-ui-checks me-1"></i> ทะเบียนงานซ่อม
                <span class="hd-list-head__count"><?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ</span>
            </h2>
            <div class="hd-toolbar">
                <div class="btn-group" role="group" aria-label="สลับมุมมองงานซ่อม">
                    <?= Html::a('ทั้งหมด', $buildMineUrl(false), [
                        'class' => 'btn btn-sm ' . (!$isMine ? 'btn-primary' : 'btn-outline-primary'),
                        'title' => 'แสดงงานซ่อมทั้งหมด',
                    ]) ?>
                    <?= Html::a('งานซ่อมของฉัน', $buildMineUrl(true), [
                        'class' => 'btn btn-sm ' . ($isMine ? 'btn-primary' : 'btn-outline-primary'),
                        'title' => 'แสดงเฉพาะงานซ่อมที่ฉันแจ้ง/สร้าง',
                    ]) ?>
                </div>
                <div class="hd-status-filter" role="group" aria-label="กรองตามสถานะ">
                    <?php foreach ($statusMeta as $statusCode => $meta): ?>
                        <?php
                        $count = (int) ($statusCounts[$statusCode] ?? 0);
                        $isActiveStatus = in_array($statusCode, $currentStatusFilter, true);
                        $cls = $isActiveStatus ? $soft($meta['color']) : $soft('secondary') . ' opacity-75';
                        ?>
                        <?php
                        $statusFilterContent = Html::tag('i', '', [
                            'class' => $meta['icon'] . ' me-1',
                            'aria-hidden' => 'true',
                        ]) . Html::encode($meta['label'] . ' ' . number_format($count));
                        ?>
                        <?= Html::a(
                            $statusFilterContent,
                            $buildStatusUrl($statusCode),
                            [
                                'class' => 'hd-status-pill text-decoration-none ' . $cls,
                                'title' => 'คลิกเพื่อเลือก/ยกเลิกสถานะ ' . $meta['label'],
                                'aria-pressed' => $isActiveStatus ? 'true' : 'false',
                                'encode' => false,
                            ]
                        ) ?>
                    <?php endforeach; ?>
                    <?php if (!empty($currentStatusFilter)): ?>
                        <?= Html::a('ล้างตัวกรอง', $buildStatusUrl(null), [
                            'class' => 'hd-status-pill text-decoration-none ' . $soft('secondary'),
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (empty($models)): ?>
                <div class="hd-empty-state">
                    <?php if ($hasFilter): ?>
                        <h3 class="hd-empty-state__title">ไม่พบงานซ่อมตามเงื่อนไข</h3>
                        <p class="hd-empty-state__caption">ลองเปลี่ยนช่วงวันที่ ผู้แจ้ง หรือสถานะ หรือกดล้างตัวกรองเพื่อดูงานซ่อมทั้งหมด</p>
                        <?= Html::a('<i class="bi bi-eraser me-1"></i> ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary rounded-pill']) ?>
                    <?php else: ?>
                        <h3 class="hd-empty-state__title">ยังไม่มีงานซ่อมในทะเบียน</h3>
                        <p class="hd-empty-state__caption">เมื่อมีการแจ้งซ่อมเข้ามา รายการจะแสดงที่นี่ พร้อมให้ช่างรับเรื่องและติดตามสถานะ</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>

                <!-- ============ Desktop (≥992px) ============ -->
                <div class="hd-table-wrap d-none d-lg-block">
                    <table class="hd-table">
                        <thead>
                            <tr>
                                <th class="hd-table__no" scope="col">#</th>
                                <th scope="col">เลขที่ / วันที่แจ้ง</th>
                                <th scope="col">ผู้แจ้ง</th>
                                <th scope="col">ความเร่งด่วน</th>
                                <th scope="col">อุปกรณ์ / ครุภัณฑ์</th>
                                <th class="hd-table__problem" scope="col">รายละเอียดปัญหา</th>
                                <th class="hd-col-tech" scope="col">ช่าง</th>
                                <th scope="col">สถานะ</th>
                                <th class="hd-table__action" scope="col">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($models as $key => $item): ?>
                                <?php
                                $person = $resolvePerson(!empty($item->created_by) ? ($empsByUserId[$item->created_by] ?? null) : null);
                                $deviceLabel = $deviceTypeByCode[$item->device_type_id]->title ?? '-';
                                $problem = '-';
                                if (is_array($item->data_json ?? null) && !empty($item->data_json['problem_detail'])) {
                                    $problem = (string) $item->data_json['problem_detail'];
                                } else {
                                    $problem = (string) ($item->title ?? '-');
                                }
                                $channel = '-';
                                try {
                                    $channel = $item->viewRepairChannelLabel();
                                } catch (\Throwable $e) {
                                }
                                $created = $item->viewCreated()['full'] ?? '-';
                                $isClosed = in_array(Helpdesk::normalizeRepairStatus($item->status), ['success', 'cancel'], true);
                                ?>
                                <tr class="<?= $isClosed ? 'hd-row--closed' : '' ?>">
                                    <td class="hd-table__no"><?= $offset + $key + 1 ?></td>
                                    <td class="hd-table__doc">
                                        <?= Html::a(Html::encode($item->repair_number ?: '-'), ['/helpdesk/service/view-v2', 'id' => $item->id], ['class' => 'hd-doc-link']) ?>
                                        <div class="hd-doc-date">
                                            <?= Html::encode($created) ?><?php $aging = $agingInline($item); ?><?php if ($aging !== ''): ?> <span class="hd-dot" aria-hidden="true">·</span> <?= $aging ?><?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= $renderPerson($person) ?></td>
                                    <td><?= $urgencyBadge($item) ?></td>
                                    <td class="hd-table__device">
                                        <div class="hd-clamp-2 fw-medium" title="<?= Html::encode($deviceLabel) ?>"><?= Html::encode($deviceLabel) ?></div>
                                        <?php if (!empty($item->asset_number)): ?>
                                            <div class="hd-mono hd-sub"><?= Html::encode($item->asset_number) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="hd-table__problem">
                                        <div class="hd-clamp-2" title="<?= Html::encode($problem) ?>"><?= Html::encode($problem) ?></div>
                                        <?php if ($channel !== '' && $channel !== '-'): ?>
                                            <div class="hd-sub">ช่องทาง: <?= Html::encode($channel) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="hd-col-tech"><?= $renderTeam($item) ?></td>
                                    <td><?= $statusBadge($item) ?></td>
                                    <td class="hd-table__action">
                                        <div class="hd-row-actions"><?= $renderActions($item, true) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ============ Mobile (<992px) ============ -->
                <ul class="hd-cards d-lg-none" role="list">
                    <?php foreach ($models as $key => $item): ?>
                        <?php
                        $person = $resolvePerson(!empty($item->created_by) ? ($empsByUserId[$item->created_by] ?? null) : null);
                        $deviceLabel = $deviceTypeByCode[$item->device_type_id]->title ?? '-';
                        $problem = '-';
                        if (is_array($item->data_json ?? null) && !empty($item->data_json['problem_detail'])) {
                            $problem = (string) $item->data_json['problem_detail'];
                        } else {
                            $problem = (string) ($item->title ?? '-');
                        }
                        $created = $item->viewCreated()['full'] ?? '-';
                        $isClosed = in_array(Helpdesk::normalizeRepairStatus($item->status), ['success', 'cancel'], true);
                        ?>
                        <li class="hd-card <?= $isClosed ? 'hd-card--closed' : '' ?>">
                            <div class="hd-card__head">
                                <?= Html::a(Html::encode($item->repair_number ?: '-'), ['/helpdesk/service/view-v2', 'id' => $item->id], ['class' => 'hd-doc-link']) ?>
                                <?= $statusBadge($item) ?>
                            </div>
                            <div class="hd-card__meta">
                                <span><i class="bi bi-clock me-1" aria-hidden="true"></i><?= Html::encode($created) ?></span>
                                <span class="hd-sep" aria-hidden="true">·</span>
                                <span><?= Html::encode($deviceLabel) ?></span>
                                <?php if (!empty($item->asset_number)): ?>
                                    <span class="hd-sep" aria-hidden="true">·</span>
                                    <span class="hd-mono"><?= Html::encode($item->asset_number) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="hd-card__badges">
                                <?= $agingBadge($item) ?>
                                <?= $urgencyBadge($item) ?>
                            </div>
                            <div class="hd-card__problem"><?= Html::encode($problem) ?></div>
                            <div class="hd-card__people">
                                <div class="hd-card__person">
                                    <span class="hd-card__person-label">ผู้แจ้ง</span>
                                    <?= $renderPerson($person) ?>
                                </div>
                                <div class="hd-card__person">
                                    <span class="hd-card__person-label">ช่าง</span>
                                    <?= $renderTeam($item) ?>
                                </div>
                            </div>
                            <div class="hd-card__actions">
                                <?= $renderActions($item, false) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="card-footer bg-white">
                    <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.helpdesk2-service-list {
    --hd-ink-1: #1a202c;
    --hd-ink-2: #4a5568;
    --hd-ink-3: #5b6572; /* ≈5:1 บนพื้นขาว — ผ่าน WCAG AA สำหรับ caption เล็ก */
    --hd-ink-4: #8a94a3;
    --hd-surface-2: #f7f9fc;
    --hd-surface-3: #eef2f7;
    --hd-surface-hover: #f1f5f9;
    --hd-line: rgba(15, 23, 42, 0.08);
    --hd-primary-ink: #0a58ca;
}

/* list head */
.hd-list-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.6rem 0.9rem;
    padding: 0.75rem 0.9rem;
    border-bottom: 1px solid var(--hd-line);
}
.hd-list-head__title {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 600;
    color: var(--hd-ink-1);
    line-height: 1.3;
}
.hd-list-head__count {
    color: var(--hd-ink-3);
    font-size: 0.8rem;
    font-weight: 500;
    margin-left: 0.35rem;
}
.hd-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem 0.75rem;
}
.hd-status-filter {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
}
.hd-status-pill {
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    font-variant-numeric: tabular-nums;
    transition: filter 120ms cubic-bezier(0.16, 1, 0.3, 1);
}
.hd-status-pill:hover { filter: brightness(0.96); }

/* desktop table */
.hd-table-wrap { width: 100%; overflow-x: auto; }
.hd-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
    font-size: 0.88rem;
}
.hd-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: var(--hd-surface-2);
    color: var(--hd-ink-2);
    font-weight: 600;
    font-size: 0.78rem;
    text-align: left;
    padding: 0.6rem 0.9rem;
    border-bottom: 1px solid var(--hd-line);
    white-space: nowrap;
}
.hd-table tbody td {
    padding: 0.65rem 0.9rem;
    border-bottom: 1px solid var(--hd-line);
    vertical-align: middle;
    color: var(--hd-ink-1);
}
.hd-table tbody tr:hover td { background: var(--hd-surface-hover); }
.hd-table tbody tr:last-child td { border-bottom: none; }

/* งานที่ปิดแล้ว (เสร็จสิ้น/ยกเลิก): หรี่ให้แยกจากงานค้าง — badge สถานะยังสีเต็ม */
.hd-table tbody tr.hd-row--closed td { background: var(--hd-surface-2); }
.hd-table tbody tr.hd-row--closed:hover td { background: var(--hd-surface-hover); }
.hd-row--closed .hd-doc-link,
.hd-row--closed .hd-clamp-2,
.hd-row--closed .hd-person__name { color: var(--hd-ink-3); font-weight: 500; }
.hd-row--closed .hd-person__avatar,
.hd-row--closed .hd-avatar-stack__img,
.hd-row--closed .hd-mono { opacity: 0.7; }

.hd-table__no {
    width: 40px;
    text-align: center;
    color: var(--hd-ink-3);
    font-variant-numeric: tabular-nums;
    font-size: 0.82rem;
}
/* คอลัมน์แคบ = พอดีเนื้อหา, ให้ "รายละเอียดปัญหา" กินพื้นที่ที่เหลือ */
.hd-table__doc { width: 1%; white-space: nowrap; }
.hd-table__device { width: 12rem; max-width: 12rem; }
.hd-table__problem { width: auto; min-width: 13rem; }
.hd-table__action { width: 1%; white-space: nowrap; text-align: right; }

.hd-doc-link {
    color: var(--hd-primary-ink);
    font-weight: 600;
    text-decoration: none;
    font-variant-numeric: tabular-nums;
}
.hd-doc-link:hover { text-decoration: underline; }
.hd-doc-date {
    margin-top: 0.18rem;
    color: var(--hd-ink-3);
    font-size: 0.76rem;
    white-space: nowrap;
}
.hd-dot { color: var(--hd-ink-4); }
.hd-aging { color: var(--hd-ink-3); font-weight: 500; font-variant-numeric: tabular-nums; }
.hd-aging--warn { color: #b45309; font-weight: 600; }
.hd-aging--over { color: #b91c1c; font-weight: 600; }
.hd-sub {
    color: var(--hd-ink-3);
    font-size: 0.76rem;
    margin-top: 0.15rem;
}
.hd-mono { font-family: var(--bs-font-monospace, monospace); }
.hd-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    color: var(--hd-ink-1);
    line-height: 1.4;
}
.hd-table__device .hd-clamp-2 { font-size: 0.84rem; }
.hd-empty { color: var(--hd-ink-4); }

/* action เรียงแนวนอนเสมอ (ไม่ตกบรรทัด) */
.hd-row-actions {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: nowrap;
    gap: 0.25rem;
    white-space: nowrap;
}
.hd-row-actions .btn { flex: 0 0 auto; }

/* จอ 992–1199px: ซ่อนคอลัมน์ "ช่าง" ลดความแน่น (ยังเห็นช่างที่ ≥1200px และในการ์ดมือถือ) */
@media (min-width: 992px) and (max-width: 1199.98px) {
    .hd-col-tech { display: none; }
}

/* person */
.hd-person { display: flex; align-items: center; gap: 0.55rem; min-width: 0; }
.hd-person__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--hd-surface-3);
    border: 1px solid var(--hd-line);
    flex-shrink: 0;
}
.hd-person__avatar--ph {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--hd-ink-2);
    font-weight: 700;
    font-size: 0.82rem;
}
.hd-person__meta { min-width: 0; line-height: 1.25; }
.hd-person__name {
    color: var(--hd-ink-1);
    font-weight: 600;
    font-size: 0.86rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 12rem;
}
.hd-person__sub {
    color: var(--hd-ink-3);
    font-size: 0.74rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 12rem;
}

/* technician avatar stack */
.hd-avatar-stack { display: inline-flex; align-items: center; }
.hd-avatar-stack__img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    background: var(--hd-surface-3);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
}
.hd-avatar-stack__img + .hd-avatar-stack__img { margin-left: -10px; }

/* mobile cards */
.hd-cards {
    list-style: none;
    margin: 0;
    padding: 0.6rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.hd-card {
    background: #fff;
    border: 1px solid var(--hd-line);
    border-radius: 8px;
    padding: 0.8rem 0.9rem;
}
.hd-card--closed { background: var(--hd-surface-2); }
.hd-card--closed .hd-doc-link,
.hd-card--closed .hd-card__problem { color: var(--hd-ink-3); }
.hd-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.4rem;
}
.hd-card__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem;
    color: var(--hd-ink-2);
    font-size: 0.8rem;
}
.hd-sep { color: var(--hd-ink-4); }
.hd-card__badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.5rem;
}
.hd-card__problem {
    margin-top: 0.55rem;
    color: var(--hd-ink-1);
    font-size: 0.86rem;
    line-height: 1.45;
}
.hd-card__people {
    margin-top: 0.65rem;
    padding-top: 0.65rem;
    border-top: 1px dashed var(--hd-line);
    display: grid;
    gap: 0.55rem;
}
.hd-card__person { display: flex; flex-direction: column; gap: 0.25rem; }
.hd-card__person-label {
    font-size: 0.7rem;
    color: var(--hd-ink-3);
    font-weight: 600;
}
.hd-card__actions {
    margin-top: 0.75rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.hd-card__actions .btn { min-height: 44px; }
/* touch target มือถือ: status pill กดง่ายด้วยนิ้วโป้ง */
@media (max-width: 991.98px) {
    .hd-status-pill { min-height: 40px; padding-top: 0.4rem; padding-bottom: 0.4rem; }
}

/* empty state */
.hd-empty-state { padding: 3.5rem 1.5rem; text-align: center; }
.hd-empty-state__title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--hd-ink-1);
    margin: 0 0 0.4rem;
}
.hd-empty-state__caption {
    color: var(--hd-ink-3);
    font-size: 0.88rem;
    max-width: 28rem;
    margin: 0 auto 1.25rem;
    line-height: 1.55;
}

/* dark mode: flip surface/ink ให้เข้ากับ data-bs-theme=dark */
[data-bs-theme="dark"] .helpdesk2-service-list {
    --hd-ink-1: #e8ecf1;
    --hd-ink-2: #b6c0cc;
    --hd-ink-3: #8b95a3;
    --hd-ink-4: #6b7480;
    --hd-surface-2: #1b2027;
    --hd-surface-3: #262c34;
    --hd-surface-hover: #21272f;
    --hd-line: rgba(255, 255, 255, 0.1);
    --hd-primary-ink: #6ea8fe;
}
[data-bs-theme="dark"] .hd-card,
[data-bs-theme="dark"] .hd-avatar-stack__img { background: var(--bs-body-bg, #1a1d21); }
[data-bs-theme="dark"] .hd-avatar-stack__img { border-color: var(--bs-body-bg, #1a1d21); }

@media (prefers-reduced-motion: reduce) {
    .hd-status-pill { transition: none; }
}
</style>

<?php
$js = <<<JS
$('body').off('click.serviceReceiveOrder').on('click.serviceReceiveOrder', '.receive-order', function (e) {
    e.preventDefault();
    let action = $(this);
    if (action.data('request-pending')) {
        return;
    }
    let url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการรับงาน?',
        text: "คุณแน่ใจหรือไม่ว่าจะรับงานนี้?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'ใช่, รับงาน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            action
                .data('request-pending', true)
                .addClass('disabled')
                .attr({ 'aria-disabled': 'true', 'aria-busy': 'true' });

            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                data: (typeof yii !== 'undefined' && typeof yii.getCsrfParam === 'function')
                    ? { [yii.getCsrfParam()]: yii.getCsrfToken() }
                    : {},
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'รับงานสำเร็จ!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = response.url || window.location.href;
                        });
                    } else {
                        Swal.fire('ผิดพลาด', response.message || 'ไม่สามารถรับงานได้', 'error');
                    }
                },
                error: function () {
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                },
                complete: function () {
                    action
                        .removeData('request-pending')
                        .removeClass('disabled')
                        .removeAttr('aria-disabled aria-busy');
                }
            });
        }
    });
});
JS;
$this->registerJS($js, View::POS_END);
?>
