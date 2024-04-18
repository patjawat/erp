<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$title = '<i class="fa-solid fa-stamp"></i> หนังสือรับรอง';
?>
<?php Pjax::begin(['id' => 'family']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title;?></h5>
        </div>
        <div class="table-responsive mt-4">
            <table class="table table-striped" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                        <th style="width: 32px;">#</th>
                        <th>รายการ</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>หนังสือรับรองเงินเดือน</td>
                        <td><button class="btn btn-primary"><i class="fa-solid fa-file-arrow-down"></i></button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>หนังสือรับรองอายุงาน</td>
                        <td><button class="btn btn-primary"><i class="fa-solid fa-file-arrow-down"></i></button></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>หนังสือรับรองความประพฤติ</td>
                        <td><button class="btn btn-primary"><i class="fa-solid fa-file-arrow-down"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>


    </div>
</div>

<?php Pjax::end();?>