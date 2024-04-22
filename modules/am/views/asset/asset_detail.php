<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>


<div class="card">
    <div class="card-body">

        <!-- Nav pills -->
        <div class="d-flex justify-content-between">

            <ul class="nav nav-pills gap-2" role="tablist">

                <li class="nav-item">
                    <div class="btn-group">
                        <a class="nav-link active" data-bs-toggle="pill" href="#repairHistory">
                        <i
                            class="fa-solid fa-screwdriver-wrench"></i> ประวัติการซ่อม</a>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu" style="">

                            <a class="dropdown-item open-modal" href="/am/asset-detail?name=calibration_items"
                                data-size="modal-lg">
                                <i class="fa-solid fa-circle-plus me-2"></i>สร้างใหม่ </a>
                </li>
                <li><a class="dropdown-item open-modal" href="/am/asset-detail?name=calibration_items" data-size="modal-lg"><i class="fa-solid fa-gear fs-6 me-2"></i> ตั้งค่า</a> </li>
            </ul>
        </div>
        </li>
                <li class="nav-item">
                    <div class="btn-group">
                        <a class="nav-link" data-bs-toggle="pill" href="#ma">
                            <i class="fa-solid fa-brush"></i> การบำรุงรักษา</a>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu" style="">
<?=Html::a('<i class="fa-solid fa-circle-plus me-2"></i>สร้างใหม่',['/am/asset-detail/create','id' => $model->id,'name' =>'ma','code' => $model->code ],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                            <!-- <a class="dropdown-item open-modal" href=<?="/am/asset-detail?name=ma&title=เพิ่มการบำรุงรักษา&id=" . $model->id ?>
                                data-size="modal-xl">
                                 </a> -->
                </li>
                <li>
                <?=Html::a('<i class="fa-solid fa-gear fs-6 me-2"></i> ตั้งค่า',['/sm/asset-item/update','id' => $model->assetItem->id,'title' => '<i class="fa-solid fa-gear fs-6 me-2"></i> ตั้งค่า'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                <!-- <a class="dropdown-item open-modal" href=<?= "/am/asset-detail?name=calibration_items_system&title=ตั้งค่าการบำรุงรักษา&id=" . $model->id ?> data-size="modal-lg"><i class="fa-solid fa-gear fs-6 me-2"></i> ตั้งค่า</a> </li> -->
            </ul>
        </div>
        </li>
        <!-- ถ้าเป็นรถยนต์  -->
        <?php if ($model->isCar()): ?>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#assetItems"><i class="fa-solid fa-list-check"></i>
                ครุภัณฑ์ภายใน</a>
        </li>
        <?php endif;?>

        <!-- ถ้าเป็นเครื่องมือแพทย์ -->
        <?php if ($model->isMedical()): ?>
        <li class="nav-item">
                    <div class="btn-group">
                        <a class="nav-link" data-bs-toggle="pill" href="#calibration">
                        <i class="fa-solid fa-weight-scale"></i> สอบเทียบ</a>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu" style="">

                            <a class="dropdown-item open-modal" href="/am/asset-detail?name=calibration_items"
                                data-size="modal-lg">
                                <i class="fa-solid fa-circle-plus me-2"></i>สร้างใหม่ </a>
                </li>
                <li><a class="dropdown-item open-modal" href="/am/asset-detail?name=calibration_items" data-size="modal-lg"><i class="fa-solid fa-gear fs-6 me-2"></i> ตั้งค่า</a> </li>
            </ul>
        </div>
        </li>

        <?php endif;?>
        </ul>
        <div>

        </div>
    </div>

    <!-- Tab panes -->
    <div class="tab-content border-top mt-3">

        <div id="repairHistory" class="tab-pane active">

            <div id="viewRepairHistory" class="mt-4"></div>

        </div>
        <div id="ma" class="tab-pane">
            <div id="viewMa" class="mt-4"></div>

        </div>

        <!-- ถ้าเป็นรถยนต์  -->
        <?php if ($model->isCar()): ?>
        <div id="assetItems" class="tab-pane">
            <span> https://github.com/softark/yii2-dual-listbox</span>

            <div id="viewAssetItems" class="mt-4"></div>

        </div>
        <?php endif;?>
        <!-- ถ้าเป็นเครื่องมือแพทย์ -->
        <?php if ($model->isMedical()): ?>
        <div id="calibration" class="tab-pane">
            สอบเทียบ
            <div id="viewCalibration" class="mt-4"></div>
        </div>
        <?php endif;?>
    </div>

</div>
</div>
<?php
$url = Url::to(['/am/asset-detail']);
$js = <<< JS
loadCalibration();
loadRepairHostory()
loadMa()

//สอบเทียบ
function  loadCalibration(){
    $.ajax({
        type: "get",
        url: "$url",
        data:{
            "title":"สอบเทียบ",
            "name":"calibration"
        },
        dataType: "json",
        success: function (res) {
            $('#viewCalibration').html(res.content);
            console.log(res.content);
        }
    });
}
//ประวัติการซ่อม
function  loadRepairHostory(){
    $.ajax({
        type: "get",
        url: "$url",
        data:{
            "title":"ประวัติการซ่อม",
            "name":"repair"
        },
        dataType: "json",
        success: function (res) {
            $('#viewRepairHistory').html(res.content);
            console.log(res.content);
        }
    });
}

//การบำรุงรักษา
function  loadMa(){
    $.ajax({
        type: "get",
        url: "$url",
        data:{
            "title":"การบำรุงรักษา",
            "name":"ma",
            "id" : "$model->id",
            "code" : "$model->code",
        },
        dataType: "json",
        success: function (res) {
            $('#viewMa').html(res.content);
            console.log(res.content);
        }
    });
}

JS;
$this->registerJS($js, yii\web\View::POS_END);
?>