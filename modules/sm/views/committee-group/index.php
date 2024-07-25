<?php

use app\modules\sm\models\CommitteeGroup;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\purchase\models\CommitteeGroupSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กลุ่มคณะกรรมการ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<div class="committee-group-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">
            <?= app\components\AppHelper::Btn([
                'url' => ['create'],
                'modal' => true,
                'size' => 'lg',
            ]) ?>

        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary',
                        'title' => 'แสกงผลแบบรายการ',
                        'data' => [
                            'bs-placement' => 'top',
                            'bs-toggle' => 'tooltip',
                        ]]) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary',
                    'title' => 'แสดงผลแบบกลุ่ม',
                    'data' => [
                        'bs-placement' => 'top',
                        'bs-toggle' => 'tooltip',
                    ]]) ?>
            <?= Html::a('<i class="fa-solid fa-file-import me-1"></i>', ['/sm/vendor/import-csv'], [
                'class' => 'btn btn-outline-primary',
                'title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
                'data' => [
                    'bs-placement' => 'top',
                    'bs-toggle' => 'tooltip',
                ],
            ]) ?>
        </div>

    </div>
</div>
    
   <div
    class="table-responsive"
   >
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">รายการ</th>
                <th scope="col">คณะกรรมหาร</th>
                <th scope="col">กำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <tr class="">
                <td scope="row">R1C1</td>
                <td>R1C2</td>
                <td>R1C3</td>
            </tr>
            <tr class="">
                <td scope="row">Item</td>
                <td>Item</td>
                <td>Item</td>
            </tr>
        </tbody>
    </table>
   </div>
   

    <?php Pjax::end(); ?>

</div>
