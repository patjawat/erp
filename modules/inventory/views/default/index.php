<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ระบบคลัง';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('./menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card" style="height: 208px;">
            <div class="card-body">
                <h4 class="card-title">ปริมาณรวม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>

    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณรวม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณรวม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-sm-12">

        <div class="card" style="height: 208px;">
            <div class="card-body">
            <div class="d-flex justify-content-between">
                    <h4 class="card-title">ปริมาณรวม</h4>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-6 col-sm-12">
        <div class="card" style="height: 308px;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title">ปริมาณรวม</h4>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>

                <p class="card-text">Text</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-sm-12">
        <div class="card" style="height: 308px;">
            <div class="card-body">
                <?php //  $this->render('order_show') ?>
            </div>
        </div>
    </div>
</div>