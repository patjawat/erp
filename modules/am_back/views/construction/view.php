<?php
use yii\helpers\Html;
use app\components\AppHelper;

$this->title = 'ทะเบียนที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>



<div class="card property-card">
                            <div class="position-relative">
                                <div class="property-type-badge land-badge">ที่ดิน</div>
                                <svg class="card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#e8f5e9"></rect>
                                    <path d="M0,140 C50,120 100,150 150,130 C200,110 250,140 300,120 L300,180 L0,180 Z" fill="#81c784"></path>
                                    <path d="M50,120 C60,115 70,125 80,120 C90,115 100,125 110,120 L110,140 L50,140 Z" fill="#4caf50"></path>
                                    <path d="M150,130 C160,125 170,135 180,130 C190,125 200,135 210,130 L210,150 L150,150 Z" fill="#4caf50"></path>
                                    <circle cx="70" cy="100" r="5" fill="#33691e"></circle>
                                    <circle cx="90" cy="90" r="8" fill="#33691e"></circle>
                                    <circle cx="180" cy="110" r="7" fill="#33691e"></circle>
                                    <circle cx="210" cy="100" r="6" fill="#33691e"></circle>
                                </svg>
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1">ที่ดินเปล่า ติดถนนใหญ่</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 8,500,000</span>
                                    <span class="badge badge-success">3 ไร่ 2 งาน</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-map me-2"></i>ข้อมูลที่ดิน
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">โฉนดเลขที่:</span>
                                    <span class="info-value">67890</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">เลขที่ดิน:</span>
                                    <span class="info-value">4567</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">หน้าสำรวจ:</span>
                                    <span class="info-value">8901</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ที่ตั้ง:</span>
                                    <span class="info-value">ตำบลบางพระ อำเภอศรีราชา จังหวัดชลบุรี</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">หน้ากว้าง:</span>
                                    <span class="info-value">40 เมตร ติดถนนใหญ่</span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-water"></i>ใกล้แหล่งน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-lightning-charge"></i>มีไฟฟ้า</span>
                                    <span class="feature-badge"><i class="bi bi-signpost-2"></i>ถนนลาดยาง</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-danger">ขาย</span>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <button class="btn btn-outline-success btn-action">
                                    <i class="bi bi-eye me-1"></i> ดูรายละเอียด
                                </button>
                                <button class="btn btn-success btn-action">
                                    <i class="bi bi-pencil-square me-1"></i> แก้ไข
                                </button>
                            </div>
                        </div>

                        
<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <div class="position-relative p-2 d-flex">
                <?php // Html::img('@web/images/imac.png',['class' => 'img-fluid rounded-start p-5']);?>

                <?= Html::img($model->showImg()['image'],['class' => 'avatar-profile object-fit-cover rounded m-auto','style' =>'max-width:100%;min-width: 320px;']) ?>

            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-none h-75">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-item-middle">
                        <div>

                            <h5 class="card-title mb-0"><?=isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-'?></h5>
                            <i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เลขโฉนดที่ดิน</span>
                            <span class="text-danger"><?=isset($model->data_json['code']) ? $model->data_json['code'] : ''?><span>
                        </div>
                        <div>
                            <?=Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข',['update','id'=> $model->id],['class' => 'btn btn-warning rounded-pill shadow'])?>
                            <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], ['class' => 'btn btn-secondary rounded-pill shadow delete-asset']) ?>
                        </div>

                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                            <ul class="list-inline">
                            <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประเภท </span>
                                        <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?>
                                    <?=$model->type_name?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เลขระวาง</span> : <?=isset($model->data_json['tonnage_number']) ? $model->data_json['tonnage_number'] : ''?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">หมายเลขที่ดิน</span> <?=isset($model->data_json['land_number']) ? $model->data_json['land_number'] : ''?></li>
                            </ul>

                        </div>
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">

                            <ul class="list-inline">
                            <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">เนื้อที่</span>
                                    <?=isset($model->data_json['land_size']) ? $model->data_json['land_size'].' ' : ''?>
                                    <?=isset($model->data_json['land_size_ngan']) ? $model->data_json['land_size_ngan'].' งาน' : ''?>
                                    <?=isset($model->data_json['land_size_tarangwa']) ? $model->data_json['land_size_tarangwa'].' เนื้อที่ตารางวา' : ''?> 
                                    
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ผู้ถือครอง</span> <?=isset($model->data_json['land_owner']) ? $model->data_json['land_owner'].' ' : ''?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ที่ตั้งบ้านเลขที่</span> :
                                    <?=isset($model->data_json['land_add']) ? $model->data_json['land_add'].' ' : ''?>
                                </li>
                            </ul>
                        </div>

                    </div>


                </div>
                <!-- End Card body -->

            </div>
            <!-- End card -->

        </div>
    </div>
</div>