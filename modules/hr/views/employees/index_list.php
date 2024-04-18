<?php
use yii\helpers\Html;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\LinkPager;
use yii\widgets\Pjax;
$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body d-flex justify-content-between">
        <div class="d-flex justify-content-start">
            
            <?=Html::a('<i class="bi bi-plus"></i> สร้างใหม่',['create'],['class' => 'btn btn-outline-primary open-modal','data' => ['size' => 'modal-xl']])?>
        </div>
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<?php Pjax::begin(['timeout' => 50000 ]); ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" style="width:300px">ชื่อ-นามสกุล</th>
                        <th scope="col">ประเภท/สถานะ</th>
                        <th scope="col">แผนก</th>
                        <th scope="col">เกษียณ/สิ้นสุดสัญญาจ่้าง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr class="">
                        <td><?=$model->getAvatar()?></td>
                        <td>
                            <div class="d-flex flex-column">

                                <span>
                                    <?=$model->positionTypeName()?>
                                </span>
                                
                                <span>
                                <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-2"><?=$model->statusName()?></label>
                                    
                                </span>
                            </div>
                        </td>
                        <td><?=$model->departmentName()?></td>
                        <td>
                            <i class="bi bi-calendar2-event"></i> <?=$model->Retire();?>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 65%"
                                    aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            
            <div class="text-muted">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>
        
        
    </div>
</div>
<div class="text-muted">
    ทั้งหมด
    <?=$totalCount = $dataProvider->getTotalCount();?>
</div>

<?php Pjax::end(); ?>
