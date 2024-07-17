<?php
use yii\helpers\Html;

$this->title = 'ระบบจัดซื้อ';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>

<style>

</style>

<div class="row">
    <div class="col-4">
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="row height d-flex justify-content-center align-items-center">

                            <div class="col-md-12">

                                <div class="search">
                                    <!-- <i class="fa fa-search"></i> -->
                                    <input type="text" class="form-control" placeholder="ค้นหา">

                                </div>

                            </div>

                        </div>
                    </div>
                    <div>
                        <div>
                            <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
                                สร้างใบสั่งซื้อ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php for ($i = 0; $i < 10; $i++): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="d-flex"><img class="avatar avatar-sm bg-primary text-white"
                                src="/img/placeholder_cid.png" alt="">
                            <div class="avatar-detail">
                                <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม..."><a class=""
                                        href="/hr/employees/view?id=1">Administrator Lastname</a>
                                </h6>
                                <p class="text-muted mb-0 fs-13">วัสดุคอมพิวเตอร์</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div>
                            <span>2,300</span>
                            <span>PR-0001</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="col-8">

        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="process-tab" data-bs-toggle="tab"
                                data-bs-target="#process" type="button" role="tab" aria-controls="process"
                                aria-selected="true"><i class="fas fa-file-alt fa-fw"></i>
                                กระบวนการการขอซื้อขอจ้าง</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="board-tab" data-bs-toggle="tab" data-bs-target="#board"
                                type="button" role="tab" aria-controls="board" aria-selected="true"><i
                                    class="fas fa-file-alt fa-fw"></i> กรรมการตรวจรับ</button>
                        </li>
                    </ul>
                    <?= Html::a('<i class="fa-solid fa-print"></i> พิมพ์เอกสาร', ['/purchase/order/document', 'id' => 1, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-md']]) ?>
                </div>


                <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="process" role="tabpanel" aria-labelledby="process-tab">
                
              
            </div>
            <div class="tab-pane fade" id="board" role="tabpanel" aria-labelledby="board-tab">
              
            </div>
            <div class="tab-pane fade" id="additional" role="tabpanel" aria-labelledby="additional-tab">
            </div>
        </div>



            </div>
        </div>

    </div>
</div>