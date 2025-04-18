<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
?>
<div class="card">
    <div class="card-body">
<h6><i class="bi bi-ui-checks"></i> อบรม/ประชุม/ดูงาน</h6>
<div class="d-flex justify-content-between">
<h6>Search</h6>
<?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มอบรม/ประชุม/ดูงาน',['/me/development/create','title' => '<i class="fa-solid fa-circle-plus"></i> เพิ่มอบรม/ประชุม/ดูงาน'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
</div>

<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th scope="col">Column 2</th>
                <th class="fw-semibold text-center">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <tr class="">
                <td scope="row">R1C1</td>
                <td>R1C2</td>
                <td>R1C3</td>
            </tr>
            <tr class="">
                <td scope="row">Item</td>
                <td>Item</td>
                <td>Item</td>
            </tr>
        </tbody>
    </table>
</div>

    </div>
</div>
