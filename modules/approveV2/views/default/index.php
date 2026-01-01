<?php

use yii\helpers\Url;
use app\modules\approve\models\Approve;

$model = new Approve();
$menu = '';
$this->title = "รายการที่รออนุมัติ";
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="layout-grid"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>



<?= $this->render('@app/modules/approveV2/views/default/card_summary') ?>



<div class="card border-0 shadow-sm p-2 mb-4 rounded-4 mt-4">
    <?= $this->render('@app/modules/approveV2/tab_menu', [
        'menu' => 'index'
    ]) ?>
</div>
<?= $this->render('@app/modules/approveV2/views/default/list', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]) ?>