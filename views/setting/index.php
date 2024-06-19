<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'ตั้งค่าระบบ';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ยินดีต้อนรับ โปรแกรมบริหารองค์กร
<?php $this->endBlock(); ?>


<style>
.menu-box>li {
    width: 150px;
}

.menu-box>li :hover {
    background-color: #d8dff2;
    border-radius: 10px;
}
</style>

<?= $this->render('color') ?>
<div class="card" style="height:400px">
    <div class="card-body">

        <h4 class="card-title"><i class="bi bi-gear"></i> <?= $this->title ?></h4>

        <ul class="nav col-12 col-lg-auto  justify-content-center  text-small menu-box">
            <li data-aos="fade-up" data-aos-delay="200">
                <?= Html::a('
                        <span data-aos="fade-up" data-aos-delay="400"><i class="bi bi-building-fill-check fs-1"></i></span>
                        <span data-aos="fade-up" data-aos-delay="300">ข้อมูลองค์กร</span>',
                    ['/setting/company'], ['class' => 'nav-link text-secondary d-flex flex-column text-center justify-content-center text-truncate', 'style' => '']) ?>
            </li>

            <!-- <li data-aos="fade-up" data-aos-delay="300">
                <?php Html::a('
                        <span data-aos="fade-up" data-aos-delay="500"><i class="bi bi-window fs-1"></i></span>
                        <span data-aos="fade-up" data-aos-delay="400">Layout</span>',
                    ['/setting/layout'], ['class' => 'nav-link text-secondary d-flex flex-column text-center justify-content-center text-truncate', 'style' => '']) ?>
            </li> -->
            <li data-aos="fade-up" data-aos-delay="400">
                <?= Html::a('
                        <span data-aos="fade-up" data-aos-delay="500"><i class="fa-solid fa-user-gear fs-1 mb-3"></i></span>
                        <span data-aos="fade-up" data-aos-delay="400">จัดการผู้ใช้งาน</span>',
                    ['/usermanager/user'], ['class' => 'nav-link text-secondary d-flex flex-column text-center justify-content-center text-truncate', 'style' => '']) ?>
            </li>
            <li data-aos="fade-up" data-aos-delay="500">
                <?= Html::a('
                        <span data-aos="fade-up" data-aos-delay="500"><i class="fa-brands fa-line fs-1 mb-3"></i></span>
                        <span data-aos="fade-up" data-aos-delay="400">Line Group</span>',
                    ['/line-group'], ['class' => 'nav-link text-secondary d-flex flex-column text-center justify-content-center text-truncate', 'style' => '']) ?>
            </li>
        </ul>

        <!-- <button type="button" class="btn btn-indigo" id="btnSwitch">
            Button
        </button> -->

    </div>
</div>
