<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Repairs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<style>
#ath_tabs_accordion .tab-style3 .nav-tabs .nav-item a.active {
    border-bottom-color: #4f30f9;
    color: #4f30f9;
}

#ath_tabs_accordion .nav-tabs {
    margin-bottom: 25px;
    border: 0;
}

#ath_tabs_accordion .tab-style3 .nav-tabs li.nav-item a {
    background-color: transparent;
    display: block;
    padding: .5rem 1rem;
    border-top: 0;
    border-left: 0;
    border-right: 0;
    border-bottom: 2px solid rgba(0, 0, 0, 0);
    text-align: center;
    text-transform: uppercase;
    border-radius: 0;
    color: #232323;
}

#ath_tabs_accordion .nav-tabs .nav-link:first-child {
    margin-left: 0 !important;
}
</style>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <?=Html::img($model->asset->showImg(), ['class' => 'avatar-profile object-fit-cover rounded m-auto mb-3 border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 320px;'])?>
            </div>
            <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
                <div class="d-flex justify-content-between">
                    <div></div>
                    <div>
                        <p>
                            <?php if($model->data_json['repair_status'] == 'ร้องขอ'):?>
                                <?= Html::a('<i class="fa-solid fa-user-pen"></i> รับเรื่อง', ['/helpdesk/repair/update', 'id' => $model->id,'title' => '<i class="fa-solid fa-hammer"></i> แก้ไขรายการส่งซ่อม'], ['class' => 'btn btn-warning open-modal','data' => ['size' => 'modal-lg']]) ?>
                                <?php else:?>
                                    <?= Html::a('<i class="fa-solid fa-hammer"></i> ลงบันทึกซ่อม/แก้ไข', ['/helpdesk/repair/update', 'id' => $model->id,'title' => '<i class="fa-solid fa-hammer"></i> แก้ไขรายการส่งซ่อม'], ['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']]) ?>
                                    <?php endif?>
                            <?= Html::a('<i class="fa-solid fa-circle-minus"></i> ยกเลิกงานซ่อม', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
                        </p>
                    </div>
                </div>
                <?=$this->render('@app/modules/am/views/asset/asset_detail_table',['model' => $asset,'repair' => $model])?>
            </div>
        </div>

    </div>
    <!-- End col-8 -->
</div>
<!-- End Row -->

<div class="card">
    <div class="card-body">
        <h4 class="card-title text-center"><i class="fa-regular fa-clipboard"></i> บันทึกซ่อม</h4>

        <div class="row" id="ath_tabs_accordion">
            <div class="col-md-12 p-3">
                <div class="tab-style3">
                    <ul class="nav nav-tabs text-uppercase">
                        <li class="nav-item">
                            <a class="nav-link active" id="Description-tab" data-bs-toggle="tab" href="#Description"><i
                                    class="fa-solid fa-handshake-simple"></i> เบิกอะไหล่</a>
                        </li>
                    </ul>
                    <div class="tab-content shop_info_tab entry-main-content">
                        <div class="tab-pane fade show active" id="Description">
                            <h2>รายการเบิกอะไหล่</h2>
                            <p>รายการที่ต้องเบอกอะไหล่เพื่อช้ในการเปลี่ยนเพื่อให้ใช้งานได้</p>
                            <?php //  Html::a('ลงบันทึกซ่อม', ['/helpdesk/repair/update', 'id' => $model->id,'title' => 'แก้ไขรายการส่งซ่อม'], ['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']]) ?>
                            <?php // isset($model->data_json['repair_note']) ? $model->data_json['repair_note'] : '-'?>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>