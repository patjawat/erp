<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItems $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="card">
    <div class="card-body">
        <?=$this->render('@app/modules/am/views/asset/asset_detail_group_3',['model' => $model->asset])?>

        <div class="row">
            <div class="col-3">
                <?php echo $model->AvatarXl()?>
            </div>
            <div class="col-9">


                
                    <div class="d-flex justify-content-between">




                        <p>
                            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],
                    ]) ?>
                        </p>


                    </div>

                    <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'car_type',
                        'asset_item_id',
                        'license_plate',
                        'active',
                    ],
                ]) ?>

                </div>


            </div>
        </div>




    </div>


</div>



</div>
</div>

<div class="card">
    <div class="card-body">



    <ul class="nav nav-pills mb-3 d-flex justify-content-start mt-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                    type="button" role="tab" aria-controls="pills-home" aria-selected="true"><i
                        class="fa-solid fa-boxes-stacked fs-5"></i> อุปกรณ์ภายใน</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                    type="button" role="tab" aria-controls="pills-profile" aria-selected="false"
                    tabindex="-1">ครุภัณฑ์ภายใน</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact"
                    type="button" role="tab" aria-controls="pills-contact" aria-selected="false" tabindex="-1"><i
                        class="fa-solid fa-toolbox fs-5"></i> ตรวจเช็คบำรุงรักษา</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled"
                    type="button" role="tab" aria-controls="pills-disabled" aria-selected="false" disabled=""
                    tabindex="-1"><i class="fa-solid fa-wrench fs-5"></i> ประวัติการซ่อม</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled"
                    type="button" role="tab" aria-controls="pills-disabled" aria-selected="false" disabled=""
                    tabindex="-1">ข้อมูล พ.ร.บ.รถ</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled"
                    type="button" role="tab" aria-controls="pills-disabled" aria-selected="false" disabled=""
                    tabindex="-1">ข้อมูลต่อภาษีรถ</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled"
                    type="button" role="tab" aria-controls="pills-disabled" aria-selected="false" disabled=""
                    tabindex="-1">ข้อมูลกรมธรรม์</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled"
                    type="button" role="tab" aria-controls="pills-disabled" aria-selected="false" disabled=""
                    tabindex="-1">แผนบำรุงรักษา</button>
            </li>
        </ul>



        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade active show" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                <?php echo $this->render('in_part_items')?>
            </div>
            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab"
                tabindex="0">
                <p>This is some placeholder content the <strong>Profile tab's</strong> associated content. Clicking
                    another tab will toggle the visibility of this one for the next. The tab JavaScript swaps classes to
                    control the content visibility and styling. You can use it with tabs, pills, and any other
                    <code>.nav</code>-powered navigation.</p>
            </div>

        </div>
        
    </div>
</div>
