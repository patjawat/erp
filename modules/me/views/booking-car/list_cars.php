<?php
use yii\helpers\Html;
?>
    <?php foreach($dataProvider->getModels() as $item):?>

        <div class="card">
            <div class="card-body">
            <div class="d-flex">

            <?php echo Html::img($item->showImg(),['class' => 'rounded-3','style' => 'max-width: 80px;max-height: 80px;'])?>
            <div class="avatar-detail">
                        <h6 class="fs-13">รถยนต์บรรทุก(ดีเซล)ขนาด 1 ตัน แบบขับเคลื่อน 2 ล้อ(บย4986)</h6>
                        <div class="d-flex justify-content-between">
                            <p class="text-muted mb-0 fs-13"><span class="badge rounded-pill badge-soft-danger text-success fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ว่าง</span></p>
                            <button type="button" class="btn btn-sm btn-primary license_plate" data-license_plate="<?php echo $item->license_plate?>">
                                เลือก
                            </button>
                            
                        </div>
                    </div>
                    </div>  
            </div>
        </div>
        
        <?php endforeach;?>
