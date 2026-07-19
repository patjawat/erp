<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplate $model */
$this->title = 'แก้ไขข้อมูล Template';
$this->params['breadcrumbs'][] = ['label' => 'คลัง Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?><div><h4 class="fw-semibold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted small"><?= Html::encode($model->name) ?></div></div><?php $this->endBlock(); ?>
<div class="container-fluid py-3 py-md-4 px-3 px-md-4"><div class="card shadow-sm border-0 mx-auto" style="max-width:980px;border-radius:10px"><div class="card-header bg-white border-bottom px-3 py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>ข้อมูล Template</h6></div><div class="card-body p-3 p-md-4"><?= $this->render('_library_form', ['model' => $model]) ?></div></div></div>
