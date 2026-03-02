<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $config */
/** @var bool $hasTemplate */
/** @var string|null $templateUrl */

$this->title = 'อัปโหลดเทมเพลตใบลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'แบบฟอร์มใบลา', 'url' => ['/leave/setting/leave-template']];
$this->params['breadcrumbs'][] = $this->title;

$hasTemplate = $hasTemplate ?? false;
$templateUrl = $templateUrl ?? null;
$config = $config ?? [];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-cloud-upload"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <p class="text-muted mb-4">อัปโหลดเทมเพลต PDF ได้ที่หน้าหลักแบบฟอร์มใบลา</p>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ไปหน้าหลักแบบฟอร์มใบลา', ['/leave/setting/leave-template'], ['class' => 'btn btn-primary rounded-3']) ?>
    </div>
</div>
