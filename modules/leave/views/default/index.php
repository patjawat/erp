
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-8">
        <div class="card" style="height: 422px;">
            <div class="card-body">
                <h6>การลาของพนักงานในแต่ละเดือน</h6>
                <?=$this->render('leave_chart')?>
            </div>
        </div>
    </div>
    <div class="col-4">
        <?=$this->render('demo1')?>

    </div>


</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6>รายชื่อบุคลกรขออนุมัติการลา</h6>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the
                    card's content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>

    </div>
</div>