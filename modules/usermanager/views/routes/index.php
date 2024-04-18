<?php

use yii\helpers\Html;
use yii\helpers\Json;

$this->params['breadcrumbs'][] = $this->title;


$opts = Json::htmlEncode([
    'routes' => $routes,
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>
<h2><i class="fas fa-traffic-light"></i>เส้นทาง</h2>
<?= $this->render('../default/navlink'); ?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-12">
                <div class="input-group">
                    <input id="inp-route" type="text" class="form-control" placeholder="<?= Yii::t('rbac-admin', 'New route(s)'); ?>">
                    <span class="input-group-btn">
                        <?= Html::a('<i class="fas fa-plus"></i> เพิ่ม' . $animateIcon, ['create'], [
                            'class' => 'btn btn-success',
                            'id' => 'btn-new',
                        ]); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <div class="input-group">
                    <input class="form-control search" data-target="available" placeholder="<?= Yii::t('rbac-admin', 'Search for available'); ?>">
                    <span class="input-group-btn">
                        <?= Html::a('<i class="fas fa-sync-alt"></i>', ['refresh'], [
                            'class' => 'btn btn-default',
                            'id' => 'btn-refresh',
                        ]); ?>
                    </span>
                </div>
                <select multiple size="20" class="form-control list" data-target="available"></select>
            </div>
            <div class="col-sm-1">
                <br><br>
                <?= Html::a('&gt;&gt;' . $animateIcon, ['assign'], [
                    'class' => 'btn btn-success btn-assign',
                    'data-target' => 'available',
                    'title' => Yii::t('rbac-admin', 'Assign'),
                ]); ?><br><br>
                <?= Html::a('&lt;&lt;' . $animateIcon, ['remove'], [
                    'class' => 'btn btn-danger btn-assign',
                    'data-target' => 'assigned',
                    'title' => Yii::t('rbac-admin', 'Remove'),
                ]); ?>
            </div>
            <div class="col-sm-5">
                <input class="form-control search" data-target="assigned" placeholder="<?= Yii::t('rbac-admin', 'Search for assigned'); ?>">
                <select multiple size="20" class="form-control list" data-target="assigned"></select>
            </div>
        </div>
    </div>
</div>