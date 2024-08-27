<?php
use yii\helpers\Html;

$this->title = 'การตั้งค่าทะเบีบนทรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gear"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
หมวดหมู่ทรัพย์สิน
<?php $this->endBlock(); ?>

<div class="card">
            <div class="card-body">
            
                    <div class="card-title">รายการหมวดหมู่ทรัพย์สิน</div>
            </div>
        </div>


<div class="row justify-content-center">
<div class="col-xl-6 col-lg-6 col-md-9 col-md-12">
<div class="card">
    <div class="card-body">
    <?= app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างกลุ่มครุภัณฑ์',
                    'url' =>['/am/asset-item/create','name' => 'asset_type','category_id'  => $code ],
                    'modal' => true,
                    'size' => 'lg'])?>
                    <br>
                    <br>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr class="">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php if($model->code == Yii::$app->request->get('code')):?>
                                    <i class="fa-regular fa-folder-open fs-2 text-primary"></i>
                                    <?php else:?>
                                    <i class="bi bi-folder-check fs-2"></i>
                                    <?php endif;?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    
                                    <?=Html::a($model->title,['/am/asset-item','code' => $model->code,'name' => 'asset_type','title' => $model->title])?><br>
                                    จำนวน
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                         <?=$model->CountTypeOnGroup()?>
                                    </label>
                                    ประเภท |  
                                    <label class="badge rounded-pill text-primary-emphasis bg-danger-subtle me-1">
                                         <?=$model->CountItemOnType()?>
                                    </label>
                                    รายการ
                                </div>
                            </div>
                        </td>
                        <!-- <td style="width: 90px;">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this item?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </td> -->
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>




</div>
</div>