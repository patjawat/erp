<?php
use yii\helpers\Html;
?>
<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ทะเบียนยานพาหนะ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td>
                    <div class="d-flex">
<!-- <img src="" alt="" class="avatar avatar-sm bg-primary text-white lazyautosizes ls-is-cached lazyloaded"> -->
<?php echo Html::img($item->asset->showImg(),['class' => 'rounded-3','style' => 'max-width: 80px;max-height: 80px;'])?>
            <div class="avatar-detail">
                        <h6 class="fs-13">รถยนต์บรรทุก(ดีเซล)ขนาด 1 ตัน แบบขับเคลื่อน 2 ล้อ(บย4986)</h6>
                        <div class="d-flex justify-content-between">
                            <p class="text-muted mb-0 fs-13"><span class="badge rounded-pill badge-soft-danger text-success fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ว่าง</span></p>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                            >
                                เลือก
                            </button>
                            
                        </div>
                    </div>
                    </div>    
                    <?php // echo $item->Avatar()?>
                </td>
                <?php endforeach;?>
            </tbody>
        </table>