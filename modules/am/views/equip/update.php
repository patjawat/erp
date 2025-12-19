<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'แก้ไขทะเบียนครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'บริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = 'ครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text">
            <path d="M13 16H8" />
            <path d="M14 8H8" />
            <path d="M16 12H8" />
            <path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>




    <?= $this->render('_form', [
        'model' => $model
    ]) ?>
<?php // Pjax::end();?>
