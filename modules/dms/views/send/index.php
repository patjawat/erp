<?php
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'สารบรรณ DMS';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">



<div class="dms-default-index">
    <h1>หนังสือส่ง</h1>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
</div>

</div>
</div>
