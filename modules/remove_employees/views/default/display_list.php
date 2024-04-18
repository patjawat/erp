<?php
use app\components\SiteHelper;
use frontend\models\Employee;;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use common\components\HrHelper;
use app\models\Employees;
// use app\themes\assets\AppAsset;
// $assets = AppAsset::register($this);

?>

<style>
/* table {
    border-collapse: separate;
    border-spacing: 0 15px;
}

th,
td {
    vertical-align: middle;
    padding: 5px;
}

tr {
    border-radius: 11px;
} */

.grid-view table thead {
    background-color: #FF0000;
}
</style>


<div class="d-flex  d-flex flex-column">
    <?php foreach($dataProvider->getModels() as $model):?>

    <div class="card border-0 mb-3">
        <div class="card-body d-flex justify-content-between">

            <!-- Start avatar -->
            <div class="avatar">
                <div class="d-flex justify-content-start align-items-center user-name">
                    <div class="avatar-wrapper">
                        <div class="avatar me-2"><img
                                src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/avatars/7.png"
                                alt="Avatar" class="rounded-circle"></div>
                    </div>
                    <div class="d-flex flex-column"><span class="emp_name text-truncate">Olivette Gudgin</span><small
                            class="emp_post text-truncate text-muted">Paralegal</small></div>
                </div>
            </div>
            <!-- End avatar -->

            <div class="emp-action">
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('<i class="fa-solid fa-trash-can"></i>', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
            </div>

        </div>
    </div>

    <?php endforeach;?>
</div>