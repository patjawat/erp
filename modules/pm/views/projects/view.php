<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน/โครงการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>แผนงาน/โครงการ<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'projects']) ?><?php $this->endBlock();
\yii\web\YiiAsset::register($this);
app\assets\RichTextAsset::register($this);

?>
<div class="projects-view container-fluid">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="mb-0"><?= Html::encode($model->name) ?></h4>
            <span class="badge <?= $model->statusBadgeClass() ?>"><?= Html::encode($model->statusLabel()) ?></span>
            <span class="text-muted ms-2">ปีงบประมาณ <?= Html::encode($model->thai_year) ?> · <?= Html::encode($model->departmentPath()) ?></span>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ / แก้ไขเอกสาร', ['print', 'id' => $model->id], ['class' => 'btn btn-outline-secondary', 'target' => '_blank', 'data-pjax' => 0]) ?>
            <?= Html::a('<i class="fa-solid fa-pen me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="project-doc">
            <?= $this->render('_document', ['model' => $model]) ?>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
#project-doc .pdoc { max-width: 900px; margin: 0 auto; }
#project-doc .pdoc-title { font-weight: 700; font-size: 1.15rem; margin-bottom: 1rem; }
#project-doc .pdoc-section { margin-bottom: .9rem; }
#project-doc .pdoc-head { font-weight: 600; }
#project-doc .pdoc-body { padding-left: 1.5rem; }
#project-doc .pdoc-body p { margin-bottom: .25rem; }
#project-doc .pdoc-list { margin-bottom: 0; padding-left: 1.25rem; }
#project-doc .pdoc-blank { color: var(--bs-secondary-color); }
#project-doc .pdoc-blank::before { content: '-'; }
#project-doc .pdoc-note { font-size: .9rem; color: var(--bs-secondary-color); margin: -.5rem 0 .9rem 1.5rem; }
#project-doc .pdoc-sign { text-align: center; margin: 1.25rem 0; }
#project-doc .pdoc-sign p { margin-bottom: .15rem; }
CSS);
?>
