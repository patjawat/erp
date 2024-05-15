<?php
use yii\helpers\Html;
?>
<div class="d-flex justify-content-between total font-weight-bold mt-1 bg-secondary-subtle rounded p-2">
    <div class="d-flex">
        <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white border border-1 border-white'])?>
        <div class="avatar-detail">
            <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม..."><a class=""
                    href="/hr/employees/view?id=152">นายปัจวัฒน์ ศรีบุญเรือง</a>
            </h6>
            <p class="text-muted mb-0 fs-13">นักวิชาการคอมพิวเตอร์ (ระดับปฏิบัติการ)
                <code>(ข้าราชการ)</code>
            </p>
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-label="Example with label" style="width: 14%;"
                    aria-valuenow="14" aria-valuemin="0" aria-valuemax="100">25%</div>
            </div>
        </div>
    </div>
</div>