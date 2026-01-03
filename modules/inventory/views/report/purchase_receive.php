<?php

use yii\web\View;
use yii\helpers\Url;

$this->title = 'รายงานจัดซื้อ+รับเข้าคลัง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu_dashbroad',['active' => 'report']) ?>
<?php $this->endBlock(); ?>

