<?php

use yii\helpers\Html;
use app\components\RichText;

app\assets\RichTextAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\pm\models\StrategyPlan $model */

$this->title = $model->name;
$canManage = Yii::$app->user->can('pmStrategyManage');
$editable = $canManage && $model->isEditable();

$years = range((int) $model->start_year, (int) $model->end_year);

/**
 * แถวเดียวของผังโครงสร้าง — ทุกระดับใช้โครงเดียวกัน ต่างกันที่ระดับการเยื้องและสีพื้น
 *
 * ซ้าย  : chevron + ไอคอน + ประเภท + รหัส
 * กลาง  : ชื่อรายการ (กินพื้นที่ที่เหลือทั้งหมด)
 * ขวา   : จำนวนลูก + ปุ่มเพิ่ม + เมนูจุดสามจุด
 *
 * @param array $c level,icon,type,code,name,nameHtml,count,node,collapsible,add[],addTitle,menu[]
 */
/**
 * สร้างรายการ "ลบ" ในเมนู — ลบได้ต่อเมื่อไม่มีรายการย่อยเหลืออยู่แล้ว
 * เงื่อนไขเดียวกันนี้ถูกบังคับซ้ำที่คอนโทรลเลอร์ ตรงนี้แค่บอกผู้ใช้ล่วงหน้า
 */
$stDelete = function (array $url, int $childCount, string $what, string $childLabel) {
    if ($childCount > 0) {
        return [
            'label' => 'ลบ',
            'disabled' => true,
            'reason' => sprintf('ลบไม่ได้ ยังมี%s %d รายการ ต้องลบให้หมดก่อน', $childLabel, $childCount),
        ];
    }
    return [
        'label' => 'ลบ',
        'url' => $url,
        'options' => ['class' => 'text-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบ' . $what . 'นี้?'],
    ];
};

$stRow = function (array $c): string {
    $node = $c['node'] ?? null;
    $out = '<div class="sh-row sh-row--' . $c['level'] . '">';

    if (!empty($c['collapsible'])) {
        $out .= Html::button('<i class="bi bi-chevron-down"></i>', [
            'type' => 'button',
            'class' => 'sh-chev',
            'aria-expanded' => 'true',
            'aria-controls' => 'kids-' . $node,
            'aria-label' => 'ย่อ/ขยาย ' . $c['type'] . ' ' . $c['name'],
        ]);
    } else {
        $out .= '<span class="sh-chev sh-chev--none" aria-hidden="true"></span>';
    }

    $out .= '<span class="sh-icon"><i class="bi ' . $c['icon'] . '"></i></span>';
    $out .= '<span class="sh-type">' . Html::encode($c['type']) . '</span>';
    if (!empty($c['code'])) {
        $out .= '<span class="sh-code">' . Html::encode($c['code']) . '</span>';
    }
    $out .= '<span class="sh-name">' . ($c['nameHtml'] ?? Html::encode($c['name'])) . '</span>';
    if (!empty($c['count'])) {
        $out .= '<span class="sh-count">' . Html::encode($c['count']) . '</span>';
    }

    $add = $c['add'] ?? [];
    $menu = $c['menu'] ?? [];
    if ($add || $menu) {
        $out .= '<div class="sh-act">';

        // ปุ่มเพิ่ม — ปลายทางเดียวคือลิงก์ตรง หลายปลายทางเปิดเป็นเมนูให้เลือกชนิด
        if (count($add) === 1) {
            $out .= Html::a('<i class="bi bi-plus-lg"></i>', $add[0]['url'], [
                'class' => 'sh-btn sh-btn--add',
                'title' => $c['addTitle'] ?? ('เพิ่ม' . $add[0]['label']),
                'aria-label' => $c['addTitle'] ?? ('เพิ่ม' . $add[0]['label']),
            ]);
        } elseif (count($add) > 1) {
            $items = '';
            foreach ($add as $a) {
                $items .= '<li>' . Html::a(Html::encode($a['label']), $a['url'], ['class' => 'dropdown-item']) . '</li>';
            }
            $out .= '<div class="dropdown">'
                . Html::button('<i class="bi bi-plus-lg"></i>', [
                    'type' => 'button',
                    'class' => 'sh-btn sh-btn--add',
                    'data-bs-toggle' => 'dropdown',
                    'aria-expanded' => 'false',
                    'title' => $c['addTitle'] ?? 'เพิ่มรายการย่อย',
                    'aria-label' => $c['addTitle'] ?? 'เพิ่มรายการย่อย',
                ])
                . '<ul class="dropdown-menu dropdown-menu-end">' . $items . '</ul>'
                . '</div>';
        }

        // เมนูจุดสามจุด — แก้ไข/ลบ และคำสั่งอื่นที่ระบบเดิมมี
        if ($menu) {
            $items = '';
            foreach ($menu as $m) {
                // รายการที่ยังลบไม่ได้ แสดงเป็นตัวจาง ๆ พร้อมเหตุผล ดีกว่าซ่อนแล้วผู้ใช้หาไม่เจอ
                if (!empty($m['disabled'])) {
                    $items .= '<li><span class="dropdown-item disabled" title="' . Html::encode($m['reason'] ?? '') . '">'
                        . Html::encode($m['label']) . '</span></li>';
                    continue;
                }
                $opts = $m['options'] ?? [];
                $opts['class'] = trim('dropdown-item ' . ($opts['class'] ?? ''));
                $items .= '<li>' . Html::a(Html::encode($m['label']), $m['url'], $opts) . '</li>';
            }
            $out .= '<div class="dropdown">'
                . Html::button('<i class="bi bi-three-dots-vertical"></i>', [
                    'type' => 'button',
                    'class' => 'sh-btn sh-btn--more',
                    'data-bs-toggle' => 'dropdown',
                    'aria-expanded' => 'false',
                    'title' => 'ตัวเลือกเพิ่มเติม',
                    'aria-label' => 'ตัวเลือกเพิ่มเติมของ ' . $c['type'] . ' ' . $c['name'],
                ])
                . '<ul class="dropdown-menu dropdown-menu-end">' . $items . '</ul>'
                . '</div>';
        }

        $out .= '</div>';
    }

    return $out . '</div>';
};

$this->beginBlock('page-title'); ?>แผนยุทธศาสตร์<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
?>

<?php /* เลย์เอาต์ไม่ได้แสดง flash ให้ ต้องแสดงเอง ไม่งั้นกดลบไม่ผ่านแล้วจะเงียบสนิท */ ?>
<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
            <?= Html::encode(Yii::$app->session->getFlash($key)) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge <?= $model->status === 'published' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>"><?= Html::encode($model::statusList()[$model->status]) ?></span>
            <span class="small text-body-secondary"><?= Html::encode($model->code) ?> · รุ่น <?= (int) $model->version ?></span>
        </div>
        <h1 class="h3 mb-1"><?= Html::encode($model->name) ?></h1>
        <p class="text-body-secondary mb-0">พ.ศ. <?= (int) $model->start_year ?>–<?= (int) $model->end_year ?></p>
    </div>
    <?php if ($canManage): ?>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($editable): ?>
                <?= Html::a('ดาวน์โหลด Template', ['/pm/strategy-import/template'], ['class' => 'btn btn-outline-secondary', 'data-pjax' => 0]) ?>
                <?= Html::a('นำเข้าจาก Excel', ['/pm/strategy-import/upload', 'planId' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('แก้ไขข้อมูลหลัก', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
                <?= Html::a('ประกาศใช้', ['publish', 'id' => $model->id], ['class' => 'btn btn-primary', 'data-method' => 'post', 'data-confirm' => 'เมื่อประกาศใช้แล้ว ข้อมูลชุดนี้จะถูกล็อก ยืนยันหรือไม่?']) ?>
            <?php else: ?>
                <?= Html::a('สร้างรุ่นใหม่', ['clone', 'id' => $model->id], ['class' => 'btn btn-primary', 'data-method' => 'post', 'data-confirm' => 'สร้างฉบับร่างรุ่นใหม่จากชุดแผนนี้?']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="small text-body-secondary fw-semibold mb-2">วิสัยทัศน์</div>
        <div class="fs-5 erp-richtext"><?= $model->vision ? RichText::render($model->vision) : 'ยังไม่ได้ระบุ' ?></div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <?= Html::a('ทะเบียนตัวชี้วัด', ['/pm/strategy-catalog/index', 'type' => 'indicator', 'planId' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
    <?= Html::a('ปัจจัยความสำเร็จ/RCA', ['/pm/strategy-catalog/index', 'type' => 'factor', 'planId' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::a('มาตรการ', ['/pm/strategy-catalog/index', 'type' => 'measure', 'planId' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::a('แผนงาน/โครงการ', ['/pm/projects/index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="mb-3">
    <h2 class="h5 mb-1">โครงสร้างยุทธศาสตร์</h2>
    <p class="small text-body-secondary mb-0">พันธกิจ → ประเด็นยุทธศาสตร์ → เป้าประสงค์ → ตัวชี้วัด → กลยุทธ์ → มาตรการ/โครงการ</p>
</div>

<?php if (!$model->missions): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="text-body-secondary mb-3">ยังไม่มีพันธกิจในชุดแผนนี้</div>
            <?php if ($editable): ?>
                <?= Html::a('เพิ่มพันธกิจแรก', ['/pm/strategy-structure/create', 'type' => 'mission', 'parentId' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($model->missions): ?>
<div class="st-plan" data-plan="<?= (int) $model->id ?>">

    <?php /* แถบควบคุมมุมมอง — ย่อ/ขยายทั้งผัง และกรองปีของมาตรการ/โครงการ ซึ่งเป็นชั้นที่โตขึ้นทุกปี */ ?>
    <div class="st-toolbar">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group" aria-label="ย่อหรือขยายทั้งผัง">
                <button type="button" class="btn btn-outline-secondary" data-st-all="expand">ขยายทั้งหมด</button>
                <button type="button" class="btn btn-outline-secondary" data-st-all="collapse">ย่อทั้งหมด</button>
            </div>
            <?php if ($editable): ?>
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่มพันธกิจ', ['/pm/strategy-structure/create', 'type' => 'mission', 'parentId' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
            <?php endif; ?>
        </div>
        <div class="st-toolbar__years" role="group" aria-label="กรองตามปีงบประมาณ">
            <span class="small text-body-secondary">ปีงบประมาณ</span>
            <button type="button" class="btn btn-sm btn-primary" data-st-year="all" aria-pressed="true">ทุกปี</button>
            <?php foreach ($years as $y): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-st-year="<?= $y ?>" aria-pressed="false"><?= $y ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach ($model->missions as $mission): ?>
        <section class="card border-0 shadow-sm st-mission">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-start gap-3 p-3">
                <div>
                    <span class="badge bg-primary me-2"><?= Html::encode($mission->code) ?></span>
                    <span class="fw-semibold"><?= Html::encode($mission->name) ?></span>
                </div>
                <?php if ($editable): ?>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <?= Html::a('เพิ่มประเด็น', ['/pm/strategy-structure/create', 'type' => 'issue', 'parentId' => $mission->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= Html::a('แก้ไข', ['/pm/strategy-structure/update', 'type' => 'mission', 'id' => $mission->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?php if ($mission->issues): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                title="ลบไม่ได้ ยังมีประเด็นยุทธศาสตร์ <?= count($mission->issues) ?> รายการ ต้องลบให้หมดก่อน">ลบ</button>
                        <?php else: ?>
                            <?= Html::a('ลบ', ['/pm/strategy-structure/delete', 'type' => 'mission', 'id' => $mission->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบพันธกิจนี้?']) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-3 sh-tree">

                <?php if (!$mission->issues): ?>
                    <div class="small text-body-secondary">ยังไม่มีประเด็นยุทธศาสตร์</div>
                <?php endif; ?>

                <?php foreach ($mission->issues as $issue): ?>
                    <?php $issueId = 'iss-' . $issue->id; ?>
                    <div class="sh-node" data-node="<?= $issueId ?>">
                        <?= $stRow([
                            'level' => 'issue',
                            'icon' => 'bi-flag-fill',
                            'type' => 'ประเด็นยุทธศาสตร์',
                            'code' => $issue->code,
                            'name' => $issue->name,
                            'count' => $issue->goals ? count($issue->goals) . ' เป้าประสงค์' : null,
                            'node' => $issueId,
                            'collapsible' => (bool) $issue->goals,
                            'add' => $editable ? [['label' => 'เป้าประสงค์', 'url' => ['/pm/strategy-structure/create', 'type' => 'goal', 'parentId' => $issue->id]]] : [],
                            'addTitle' => 'เพิ่มเป้าประสงค์',
                            'menu' => $editable ? [
                                ['label' => 'แก้ไข', 'url' => ['/pm/strategy-structure/update', 'type' => 'issue', 'id' => $issue->id]],
                                $stDelete(['/pm/strategy-structure/delete', 'type' => 'issue', 'id' => $issue->id], count($issue->goals), 'ประเด็นยุทธศาสตร์', 'เป้าประสงค์'),
                            ] : [],
                        ]) ?>

                        <?php if ($issue->goals): ?>
                        <div class="sh-kids" id="kids-<?= $issueId ?>">
                            <?php foreach ($issue->goals as $goal): ?>
                                <?php
                                $goalId = 'goal-' . $goal->id;
                                $primaries = array_filter($goal->indicators, fn ($ind) => !$ind->parent_id);
                                $goalHasKids = $primaries || $goal->factors || $goal->orphanTactics;
                                ?>
                                <div class="sh-node" data-node="<?= $goalId ?>">
                                    <?= $stRow([
                                        'level' => 'goal',
                                        'icon' => 'bi-bullseye',
                                        'type' => 'เป้าประสงค์',
                                        'code' => $goal->code,
                                        'name' => $goal->name,
                                        'count' => $primaries ? count($primaries) . ' ตัวชี้วัด' : null,
                                        'node' => $goalId,
                                        'collapsible' => (bool) $goalHasKids,
                                        'add' => $editable ? [
                                            ['label' => 'ตัวชี้วัด', 'url' => ['/pm/strategy-structure/create', 'type' => 'indicator', 'parentId' => $goal->id]],
                                            ['label' => 'ปัจจัยความสำเร็จ/RCA', 'url' => ['/pm/strategy-catalog/create', 'type' => 'factor', 'parentId' => $goal->id]],
                                        ] : [],
                                        'addTitle' => 'เพิ่มตัวชี้วัด',
                                        'menu' => $editable ? [
                                            ['label' => 'แก้ไข', 'url' => ['/pm/strategy-structure/update', 'type' => 'goal', 'id' => $goal->id]],
                                            $stDelete(['/pm/strategy-structure/delete', 'type' => 'goal', 'id' => $goal->id], count($goal->indicators) + count($goal->factors) + count($goal->tactics), 'เป้าประสงค์', 'ตัวชี้วัด/ปัจจัย/กลยุทธ์'),
                                        ] : [],
                                    ]) ?>

                                    <?php if ($goalHasKids): ?>
                                    <div class="sh-kids" id="kids-<?= $goalId ?>">

                                        <?php foreach ($primaries as $indicator): ?>
                                            <?php
                                            $indId = 'ind-' . $indicator->id;
                                            $indHasKids = $indicator->tactics || $indicator->children;
                                            ?>
                                            <div class="sh-node" data-node="<?= $indId ?>">
                                                <?= $stRow([
                                                    'level' => 'indicator',
                                                    'icon' => 'bi-speedometer2',
                                                    'type' => 'ตัวชี้วัด',
                                                    'code' => $indicator->code,
                                                    'name' => $indicator->name,
                                                    'count' => $indicator->tactics ? count($indicator->tactics) . ' กลยุทธ์' : null,
                                                    'node' => $indId,
                                                    'collapsible' => (bool) $indHasKids,
                                                    'add' => $editable ? [
                                                        ['label' => 'ตัวชี้วัดรอง', 'url' => ['/pm/strategy-structure/create', 'type' => 'sub-indicator', 'parentId' => $indicator->id]],
                                                        ['label' => 'กลยุทธ์', 'url' => ['/pm/strategy-structure/create', 'type' => 'tactic', 'parentId' => $indicator->id]],
                                                    ] : [],
                                                    'addTitle' => 'เพิ่มตัวชี้วัดรอง หรือ กลยุทธ์',
                                                    'menu' => $editable ? [
                                                        ['label' => 'แก้ไข', 'url' => ['/pm/strategy-structure/update', 'type' => 'indicator', 'id' => $indicator->id]],
                                                        $stDelete(['/pm/strategy-structure/delete', 'type' => 'indicator', 'id' => $indicator->id], count($indicator->children) + count($indicator->tactics), 'ตัวชี้วัด', 'ตัวชี้วัดรอง/กลยุทธ์'),
                                                    ] : [],
                                                ]) ?>

                                                <?php if ($indHasKids): ?>
                                                <div class="sh-kids" id="kids-<?= $indId ?>">
                                                    <?= $this->render('_tactics', ['owner' => $indicator, 'editable' => $editable, 'stRow' => $stRow, 'stDelete' => $stDelete]) ?>

                                                    <?php foreach ($indicator->children as $child): ?>
                                                        <?php $childId = 'ind-' . $child->id; ?>
                                                        <div class="sh-node" data-node="<?= $childId ?>">
                                                            <?= $stRow([
                                                                'level' => 'subindicator',
                                                                'icon' => 'bi-speedometer',
                                                                'type' => 'ตัวชี้วัดรอง',
                                                                'code' => $child->code,
                                                                'name' => $child->name,
                                                                'count' => $child->tactics ? count($child->tactics) . ' กลยุทธ์' : null,
                                                                'node' => $childId,
                                                                'collapsible' => (bool) $child->tactics,
                                                                'add' => $editable ? [['label' => 'กลยุทธ์', 'url' => ['/pm/strategy-structure/create', 'type' => 'tactic', 'parentId' => $child->id]]] : [],
                                                                'addTitle' => 'เพิ่มกลยุทธ์',
                                                                'menu' => $editable ? [
                                                                    ['label' => 'แก้ไข', 'url' => ['/pm/strategy-structure/update', 'type' => 'sub-indicator', 'id' => $child->id]],
                                                                    $stDelete(['/pm/strategy-structure/delete', 'type' => 'sub-indicator', 'id' => $child->id], count($child->tactics), 'ตัวชี้วัดรอง', 'กลยุทธ์'),
                                                                ] : [],
                                                            ]) ?>
                                                            <?php if ($child->tactics): ?>
                                                                <div class="sh-kids" id="kids-<?= $childId ?>">
                                                                    <?= $this->render('_tactics', ['owner' => $child, 'editable' => $editable, 'stRow' => $stRow, 'stDelete' => $stDelete]) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php foreach ($goal->factors as $factor): ?>
                                            <div class="sh-node">
                                                <?= $stRow([
                                                    'level' => 'factor',
                                                    'icon' => 'bi-exclamation-triangle',
                                                    'type' => $factor->factor_type === 'rca' ? 'RCA' : 'ปัจจัยความสำเร็จ',
                                                    'code' => $factor->code,
                                                    'name' => RichText::plain($factor->name, 200),
                                                    'nameHtml' => '<span class="erp-richtext d-inline">' . RichText::render($factor->name) . '</span>',
                                                    'menu' => $editable ? [
                                                        ['label' => 'แก้ไข', 'url' => ['/pm/strategy-catalog/update', 'type' => 'factor', 'id' => $factor->id]],
                                                        ['label' => 'ลบ', 'url' => ['/pm/strategy-catalog/delete', 'type' => 'factor', 'id' => $factor->id, 'backTo' => 'plan'], 'options' => ['class' => 'text-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบรายการนี้?']],
                                                    ] : [],
                                                ]) ?>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php /* กลยุทธ์เก่าที่ยังไม่ได้ผูกตัวชี้วัด — แสดงไว้ให้ย้ายหรือลบ ไม่ปล่อยให้หายไปเงียบ ๆ */ ?>
                                        <?php foreach ($goal->orphanTactics as $orphan): ?>
                                            <div class="sh-node">
                                                <?= $stRow([
                                                    'level' => 'orphan',
                                                    'icon' => 'bi-exclamation-circle',
                                                    'type' => 'กลยุทธ์ยังไม่ผูกตัวชี้วัด',
                                                    'code' => $orphan->code,
                                                    'name' => $orphan->label(),
                                                    'menu' => $editable ? [
                                                        ['label' => 'ย้ายไปใต้ตัวชี้วัด', 'url' => ['/pm/strategy-structure/update', 'type' => 'tactic', 'id' => $orphan->id]],
                                                        ['label' => 'ลบ', 'url' => ['/pm/strategy-structure/delete', 'type' => 'tactic', 'id' => $orphan->id], 'options' => ['class' => 'text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบกลยุทธ์นี้?']],
                                                    ] : [],
                                                ]) ?>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>
                                    <?php endif; ?>

                                    <?php if (!$primaries && $editable): ?>
                                        <div class="sh-hint">ยังไม่มีตัวชี้วัดภายใต้เป้าประสงค์นี้ — กลยุทธ์และโครงการต้องผูกกับตัวชี้วัด</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            </div>
        </section>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
/**
 * ผังโครงสร้างยุทธศาสตร์แบบ accordion
 * ลำดับชั้นอ่านจากระยะเยื้องและสีพื้นของแถว ไม่มีเส้นเชื่อมแบบ directory tree
 * สีทั้งหมดมาจากตัวแปรของ Bootstrap จึงเปลี่ยนตามธีมของระบบเอง
 */
$this->registerCss(<<<'CSS'
.st-plan{max-width:82rem}
.st-toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1rem}
.st-toolbar__years{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem}
.st-mission+.st-mission{margin-top:1rem}

/* แต่ละชั้นเยื้องเข้าไป 24px — ลำดับชั้นมาจากตำแหน่ง ไม่ใช่เส้น */
.sh-tree{--sh-indent:24px}
.sh-kids{padding-left:var(--sh-indent);margin-top:4px}
.sh-node+.sh-node{margin-top:4px}
.sh-node.is-collapsed>.sh-kids{display:none}

.sh-row{display:flex;align-items:center;gap:.5rem;min-height:44px;padding:.35rem .6rem;border-radius:8px;background-color:var(--sh-bg,transparent)}
.sh-row:hover{box-shadow:inset 0 0 0 1px var(--bs-border-color)}

/* สีพื้นแยกระดับ ใช้โทน subtle ของธีมจึงไม่ขัดกับสีหลักของระบบ */
.sh-row--issue{--sh-bg:var(--bs-primary-bg-subtle);font-weight:600}
.sh-row--goal{--sh-bg:var(--bs-info-bg-subtle)}
.sh-row--tactic{--sh-bg:var(--bs-success-bg-subtle)}
.sh-row--orphan{--sh-bg:var(--bs-warning-bg-subtle)}

.sh-chev{flex:0 0 auto;width:1.5rem;height:1.5rem;display:inline-flex;align-items:center;justify-content:center;padding:0;border:0;background:none;color:var(--bs-secondary-color);cursor:pointer}
.sh-chev>i{transition:transform 140ms ease}
.sh-node.is-collapsed>.sh-row>.sh-chev>i{transform:rotate(-90deg)}
.sh-chev--none{cursor:default}
.sh-chev:focus-visible{outline:2px solid var(--bs-primary);outline-offset:1px;border-radius:6px}

.sh-icon{flex:0 0 auto;display:inline-flex;width:1.25rem;justify-content:center;color:var(--bs-secondary-color)}
.sh-row--issue .sh-icon{color:var(--bs-primary)}
.sh-row--goal .sh-icon{color:var(--bs-info-text-emphasis)}
.sh-row--indicator .sh-icon{color:var(--bs-body-color)}
.sh-row--subindicator .sh-icon{color:var(--bs-secondary-color)}
.sh-row--tactic .sh-icon{color:var(--bs-success-text-emphasis)}
.sh-row--factor,.sh-row--orphan{color:var(--bs-warning-text-emphasis)}
.sh-row--factor .sh-icon,.sh-row--orphan .sh-icon{color:var(--bs-warning-text-emphasis)}

.sh-type{flex:0 0 auto;white-space:nowrap;color:var(--bs-secondary-color)}
.sh-code{flex:0 0 auto;white-space:nowrap;color:var(--bs-secondary-color);font-variant-numeric:tabular-nums}
.sh-name{flex:1 1 auto;min-width:0}
.sh-link{color:inherit;text-decoration:none}
.sh-link:hover{text-decoration:underline}
.sh-meta{margin-left:.5rem;color:var(--bs-secondary-color);font-size:.85em;font-variant-numeric:tabular-nums}
.sh-count{flex:0 0 auto;white-space:nowrap;color:var(--bs-secondary-color);font-size:.85em}
.sh-hint{padding:.25rem 0 .25rem calc(var(--sh-indent) + .6rem);color:var(--bs-secondary-color);font-size:.9em}

/* คำสั่งของแถวอยู่ในแถวของตัวเอง ไม่ใช่คอลัมน์ปุ่มยาวลงมาทางขวา */
.sh-act{flex:0 0 auto;display:flex;align-items:center;gap:.15rem}
.sh-btn{display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;padding:0;border:0;background:none;border-radius:6px;color:var(--bs-secondary-color);text-decoration:none}
.sh-btn:hover,.sh-btn[aria-expanded="true"]{background-color:var(--bs-secondary-bg);color:var(--bs-body-color)}
.sh-btn:focus-visible{outline:2px solid var(--bs-primary);outline-offset:1px}
.sh-act .dropdown-menu{font-size:.9rem}

/* บนอุปกรณ์ที่มีเมาส์ ให้เนื้อหาเด่นกว่าคำสั่งจนกว่าจะชี้ที่แถว */
@media (hover:hover){
    .sh-act{opacity:.45;transition:opacity 120ms ease}
    .sh-row:hover .sh-act,.sh-row:focus-within .sh-act{opacity:1}
}

/* กรองปี: ซ่อนเฉพาะแถวที่ผูกกับปี ถ้ากลยุทธ์ไม่เหลือรายการเลยจะขึ้นบรรทัดบอกแทน */
.sh-node.is-year-hidden{display:none}
.sh-year-empty{display:none;padding:.25rem 0 .25rem .6rem;color:var(--bs-secondary-color);font-size:.9em}
.sh-kids.is-year-empty>.sh-year-empty{display:block}

@media (max-width:575.98px){
    .sh-tree{--sh-indent:14px}
    .sh-type{display:none}
}

@media (prefers-reduced-motion:reduce){
    .sh-chev>i,.sh-act{transition:none}
}

@media print{
    .st-toolbar,.sh-act{display:none !important}
    .st-plan{max-width:none}
    .sh-node.is-collapsed>.sh-kids{display:block !important}
    .sh-node.is-year-hidden{display:revert !important}
}
CSS);

/**
 * ย่อ/ขยายโหนด จำสถานะไว้ต่อชุดแผน และกรองปีของมาตรการ/โครงการ
 * ทำงานฝั่งเบราว์เซอร์ล้วน ไม่ยิงเซิร์ฟเวอร์ซ้ำ
 */
$this->registerJs(<<<'JS'
(function () {
    var root = document.querySelector('.st-plan');
    if (!root) { return; }

    var storeKey = 'pm.strategy.collapsed.' + root.dataset.plan;
    var collapsed = {};
    try { collapsed = JSON.parse(localStorage.getItem(storeKey) || '{}') || {}; } catch (e) { collapsed = {}; }

    function persist() {
        try { localStorage.setItem(storeKey, JSON.stringify(collapsed)); } catch (e) { /* โควตาเต็ม — ไม่ต้องขัดจังหวะผู้ใช้ */ }
    }

    function setCollapsed(node, isCollapsed) {
        node.classList.toggle('is-collapsed', isCollapsed);
        var chev = node.querySelector(':scope > .sh-row > .sh-chev');
        if (chev && chev.tagName === 'BUTTON') { chev.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true'); }
        var id = node.dataset.node;
        if (!id) { return; }
        if (isCollapsed) { collapsed[id] = 1; } else { delete collapsed[id]; }
    }

    root.querySelectorAll('.sh-node[data-node]').forEach(function (node) {
        if (collapsed[node.dataset.node]) { setCollapsed(node, true); }
    });

    root.addEventListener('click', function (e) {
        var chev = e.target.closest('.sh-chev');
        if (chev && chev.tagName === 'BUTTON' && root.contains(chev)) {
            var node = chev.closest('.sh-node');
            setCollapsed(node, !node.classList.contains('is-collapsed'));
            persist();
            return;
        }
        var all = e.target.closest('[data-st-all]');
        if (all) {
            var wantCollapsed = all.dataset.stAll === 'collapse';
            root.querySelectorAll('.sh-node[data-node]').forEach(function (node) {
                if (node.querySelector(':scope > .sh-kids')) { setCollapsed(node, wantCollapsed); }
            });
            persist();
        }
    });

    // กรองปี
    var yearBtns = root.querySelectorAll('[data-st-year]');
    yearBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var year = btn.dataset.stYear;
            yearBtns.forEach(function (b) {
                var on = b === btn;
                b.classList.toggle('btn-primary', on);
                b.classList.toggle('btn-outline-secondary', !on);
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            root.querySelectorAll('.sh-node[data-year]').forEach(function (node) {
                node.classList.toggle('is-year-hidden', year !== 'all' && node.dataset.year !== year);
            });
            root.querySelectorAll('.sh-kids').forEach(function (kids) {
                var scoped = kids.querySelectorAll(':scope > .sh-node[data-year]');
                var visible = kids.querySelectorAll(':scope > .sh-node[data-year]:not(.is-year-hidden)');
                kids.classList.toggle('is-year-empty', scoped.length > 0 && visible.length === 0);
            });
        });
    });
})();
JS);
