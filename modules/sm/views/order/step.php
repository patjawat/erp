<style>
/* Steps */
.step {
    position: relative;
    min-height: 1em;
    color: gray;
}

.step+.step {
    margin-top: 1.5em
}

.step>div:first-child {
    position: static;
    height: 0;
}

.step>div:not(:first-child) {
    margin-left: 1.5em;
    padding-left: 1em;
}

.step.step-active {
    color: #4285f4
}

.step.step-active .circle {
    background-color: #4285f4;
}

/* Circle */
.circle {
    background: gray;
    position: relative;
    width: 1.5em;
    height: 1.5em;
    line-height: 1.5em;
    border-radius: 100%;
    color: #fff;
    text-align: center;
    box-shadow: 0 0 0 3px #fff;
}

/* Vertical Line */
.circle:after {
    content: ' ';
    position: absolute;
    display: block;
    top: 1px;
    right: 50%;
    bottom: 1px;
    left: 50%;
    height: 100%;
    width: 1px;
    transform: scale(1, 2);
    transform-origin: 50% -100%;
    background-color: rgba(0, 0, 0, 0.25);
    z-index: -1;
}

.step:last-child .circle:after {
    display: none
}

/* Stepper Titles */
.title {
    line-height: 1.5em;
    font-weight: 500;
}

.caption {
    font-size: 0.8em;
}
</style>
<div class="mt-3">
    <div class="step  <?= $model->status == 1 ? 'step-active' : '' ?>">
        <div>
            <div class="circle"><?= $model->status == 1 ? '<i class="fa fa-check"></i>' : '1' ?></div>
        </div>
        <div>
            <div class="title">ขอซื้อ-ขอจ้าง (PR)</div>
            <div class="caption"><?= $model->pr_number ?></div>
        </div>
    </div>
    <div class="step  <?= $model->status == 2 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 2 ? '<i class="fa fa-check"></i>' : '2' ?></div>
        </div>
        <div>
            <div class="title">หัวหน้าเห็นชอบ</div>
            <div class="caption">ตรวจสอบคำขอซื้อ/ขอจ้าง</div>
        </div>
    </div>
    <div class="step  <?= $model->status == 3 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 3 ? '<i class="fa fa-check"></i>' : '3' ?></div>
        </div>
        <div>
            <div class="title">ตรวจสอบคำขอซื้อ</div>
            <div class="caption">Some text about Third step. </div>
        </div>
    </div>
    <div class="step  <?= $model->status == 4 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 4 ? '<i class="fa fa-check"></i>' : '4' ?></div>
        </div>
        <div>
            <div class="title">ผู้อำนวยการอนุมัติ</div>
        </div>
    </div>
    <div class="step  <?= $model->status == 5 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 5 ? '<i class="fa fa-check"></i>' : '5' ?></div>
        </div>
        <div>
            <div class="title">ลงทะเบียนคุม</div>
        </div>
    </div>

    <div class="step  <?= $model->status == 6 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 6 ? '<i class="fa fa-check"></i>' : '6' ?></div>
        </div>
        <div>
            <div class="title">ออกใบสั่งซื้อ (PO)</div>
        </div>
    </div>

    <div class="step  <?= $model->status == 7 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 7 ? '<i class="fa fa-check"></i>' : '7' ?></div>
        </div>
        <div>
            <div class="title">ตรวจรับ</div>
        </div>
    </div>

    <div class="step  <?= $model->status == 8 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 8 ? '<i class="fa fa-check"></i>' : '8' ?></div>
        </div>
        <div>
            <div class="title">ยืนยันตรวจรับ</div>
        </div>
    </div>

    <div class="step  <?= $model->status == 9 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 9 ? '<i class="fa fa-check"></i>' : '9' ?></div>
        </div>
        <div>
            <div class="title">รับเข้าคลัง</div>
        </div>
    </div>

    <div class="step  <?= $model->status == 10 ? 'step-active' : '' ?>">
        <div>
        <div class="circle"><?= $model->status == 10 ? '<i class="fa fa-check"></i>' : '10' ?></div>
        </div>
        <div>
            <div class="title">เสร็จสิ้น-ส่งบัญชี</div>
        </div>
    </div>

</div>