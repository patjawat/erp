<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'โครงการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$fmt = Yii::$app->formatter;
$row = function ($no, $title, $body) {
    if ($body === null || $body === '' ) {
        $body = '<span class="text-muted">-</span>';
    }
    return '<div class="mb-3"><div class="fw-semibold">' . $no . '. ' . Html::encode($title) . '</div>'
        . '<div class="ps-3">' . $body . '</div></div>';
};
$text = function ($v) {
    return $v ? nl2br(Html::encode($v)) : null;
};
?>
<div class="projects-view container-fluid">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0"><?= Html::encode($model->name) ?></h4>
            <span class="badge <?= $model->statusBadgeClass() ?>"><?= Html::encode($model->statusLabel()) ?></span>
            <span class="text-muted ms-2">ปีงบประมาณ <?= Html::encode($model->thai_year) ?> · <?= Html::encode($model->departmentName()) ?></span>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์', '#', ['class' => 'btn btn-outline-secondary', 'onclick' => 'window.print();return false;']) ?>
            <?= Html::a('<i class="fa-solid fa-pen me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="project-doc">
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-1">โครงการ <?= Html::encode($model->name) ?></h5>
                <?php if ($model->code): ?><div class="text-muted">เลขที่ <?= Html::encode($model->code) ?></div><?php endif; ?>
            </div>

            <?= $row(1, 'หลักการและเหตุผล', $text($model->rationale)) ?>

            <?php
            $objBody = '<span class="text-muted">-</span>';
            if ($model->objectives) {
                $objBody = '<ol class="mb-0 ps-3">';
                foreach ($model->objectives as $o) {
                    $objBody .= '<li>' . nl2br(Html::encode($o->detail)) . '</li>';
                }
                $objBody .= '</ol>';
            }
            echo $row(2, 'วัตถุประสงค์', $objBody);

            $indBody = '<span class="text-muted">-</span>';
            if ($model->indicators) {
                $indBody = '<ol class="mb-0 ps-3">';
                foreach ($model->indicators as $ind) {
                    $pct = $ind->target_percent !== null ? ' <span class="text-primary">(ร้อยละ ' . $fmt->asDecimal($ind->target_percent, 2) . ')</span>' : '';
                    $indBody .= '<li>' . nl2br(Html::encode($ind->detail)) . $pct . '</li>';
                }
                $indBody .= '</ol>';
            }
            echo $row(3, 'เป้าหมาย/ตัวชี้วัดผลสำเร็จของโครงการ', $indBody);

            echo $row(4, 'กลุ่มเป้าหมาย', $text($model->target_group));
            echo $row(5, 'วิธีดำเนินการ (งานและกิจกรรม)', $text($model->method));

            $duration = $model->duration_text;
            if ($model->start_date || $model->end_date) {
                $duration = trim(($model->start_date ? $fmt->asDate($model->start_date, 'long') : '') . ' - ' . ($model->end_date ? $fmt->asDate($model->end_date, 'long') : ''), ' -')
                    . ($model->duration_text ? '<br>' . Html::encode($model->duration_text) : '');
            } elseif ($duration) {
                $duration = Html::encode($duration);
            }
            echo $row(6, 'ระยะเวลาการดำเนินการ', $duration);
            echo $row(7, 'สถานที่ดำเนินโครงการ', $text($model->location));
            echo $row(8, 'วิทยากร', $text($model->lecturer));
            echo $row(9, 'การประเมินผลโครงการ', $text($model->evaluation));
            echo $row(10, 'ผลที่คาดว่าจะได้รับ', $text($model->expected_result));

            $respBody = '<span class="text-muted">-</span>';
            if ($model->responsibles) {
                $respBody = '<ol class="mb-0 ps-3">';
                foreach ($model->responsibles as $r) {
                    $line = Html::encode($r->fullname);
                    if ($r->position) $line .= ' &mdash; ' . Html::encode($r->position);
                    if ($r->phone) $line .= ' <span class="text-muted">โทร ' . Html::encode($r->phone) . '</span>';
                    $respBody .= '<li>' . $line . '</li>';
                }
                $respBody .= '</ol>';
            }
            echo $row(11, 'ผู้รับผิดชอบโครงการ', $respBody);

            $budgetBody = '<div>งบประมาณรวม <strong>' . $fmt->asDecimal($model->budget_total, 2) . '</strong> บาท'
                . ($model->budget_source ? ' <span class="text-muted">(' . Html::encode($model->budget_source) . ')</span>' : '') . '</div>'
                . ($model->budget_detail ? '<div class="mt-1">' . nl2br(Html::encode($model->budget_detail)) . '</div>' : '')
                . '<div class="form-text">หมายเหตุ ค่าใช้จ่ายทุกรายการสามารถถัวเฉลี่ยจ่ายแทนกันได้</div>';
            echo $row(12, 'งบประมาณ', $budgetBody);
            ?>
        </div>
    </div>
</div>

<?php
$this->registerCss('@media print { .no-print, .breadcrumb, .navbar, .sidebar, footer { display:none !important; } #project-doc { font-size: 15px; } }');
?>
