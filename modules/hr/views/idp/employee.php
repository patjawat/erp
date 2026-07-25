<?php
use yii\helpers\Html;
$this->title = 'IDP: '.$employee->fullname;
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
if (Yii::$app->user->can('hr') || Yii::$app->user->can('admin')) {
    $this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu',['active'=>'idp']); $this->endBlock();
}
?>
<?= $this->render('_employee_panel', ['employee'=>$employee,'cycle'=>$cycle,'plan'=>$plan,'isSelfProfile'=>false]) ?>
