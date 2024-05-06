<?php
use yii\helpers\Html;
?>
<style>
.card.custom-card {
    border-radius: .688rem;
    border: 0;
    /* background-color: var(--custom-white); */
    box-shadow: 0 10px 30px 0 var(--primary005);
    position: relative;
    margin-block-end: 1.25rem;
    width: 100%;
}

/* .img-bg {
    background: url(../images/media/media-66.png);
    background-position: right;
    background-size: auto;
    background-repeat: no-repeat;
} */
.card-box img {
    position: absolute;
    inset-block-end: -3px;
    inset-inline-start: -17px;
    width: inherit;
}
</style>
<div class="row">
    <div class="col-8">

        <div class="row row-sm banner-img">
            <div class="col-sm-12 col-lg-12 col-xl-12">
                <div class="card custom-card card-box">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="offset-xl-3 offset-sm-6 col-xl-8 col-sm-6 col-12 img-bg ">
                                <h4 class="d-flex mb-3"> <span class="text-fixed-white ">Helpdesk ! ระบบงานซ่อม</span> </h4>
                                <p class="tx-white-7 mb-1">คุณมี 2 รายการที่ต้องทำให้เสร็จ คุทำเสร็จไปแล้ว <b
                                        class="text-warning">57%</b>
                                </p>
                            </div> 
                            <?=Html::img('@web/images/help.png');?>
                        </div>
                    </div>
                </div>
            </div>
            
            
        </div>
        <?=$this->render('task')?>

    </div>
    <div class="col-4">

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ความพึงพอใจของลูกค้า</h4>
                <p class="card-text">Text</p>
            </div>
        </div>

    </div>
</div>