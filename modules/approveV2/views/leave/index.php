  <?php

  use yii\helpers\Url;
  use app\modules\approve\models\Approve;

  $menu = '';
  $this->title = "รายการที่รออนุมัติ";
  $this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
  $this->params['breadcrumbs'][] = $this->title;
  ?>

  <?php $this->beginBlock('page-title'); ?>
     <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
      <i data-lucide="layout-grid"></i>
      <?= $this->title ?>
    </h4>
  <?php $this->endBlock(); ?>

  <?= $this->render('@app/modules/approveV2/views/default/card_summary') ?>

    <?= $this->render('@app/modules/approveV2/tab_menu', [
      'menu' => 'leave'
    ]) ?>