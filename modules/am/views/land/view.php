<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\AppHelper;

$this->title = 'ทะเบียนที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-map fs-3"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <div class="map-container">
                        <div class="text-center">
                            <i class="bi bi-map" style="font-size: 3rem; color: #6c757d;"></i>
                            <p class="mt-2">แผนที่แสดงตำแหน่งที่ดิน</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'ref',
            'code',
        ],
    ]) ?>
    
        <div class="card">
            <div class="card-body">
                <h5 id="modalPropertyId"><?=$model->code?></h5>
                <p><span class="badge bg-success" id="modalPropertyStatus">ว่าง</span> <span class="badge bg-info"
                        id="modalPropertyType">ที่อยู่อาศัย</span></p>
                <p><strong>ที่ตั้ง:</strong> <span id="modalAddress">123 ถ.สุขุมวิท แขวงคลองตัน เขตคลองเตย กรุงเทพมหานคร
                        10110</span></p>
                <p><strong>ขนาด:</strong> <span id="modalSize">2 ไร่ 1 งาน 50 ตารางวา</span></p>
                <p><strong>เอกสารสิทธิ์:</strong> <span id="modalDocument">โฉนด เลขที่ 12345</span></p>
                <p><strong>ราคา:</strong> <span id="modalPrice">5,000,000 บาท</span></p>
            </div>
        </div>
    </div>
</div>