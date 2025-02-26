<?php
use yii\helpers\Url;
use yii\helpers\Html;
$this->title = 'จองห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<style>
.fc .fc-toolbar > * > :first-child {
    margin-left: 0;
    font-size: medium;
}
</style>
<?php // echo $this->render('list_room')?>
<div class="row">
    <div class="col-6">
    <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
<?php echo Html::a('วันก่อน',['/me/booking-metting'])?>
<h6><i class="fa-regular fa-calendar-plus"></i> <?php $time = time(); echo Yii::$app->thaiFormatter->asDate($time, 'full')."<br>";?> </h6>
<?php echo Html::a('วันถัดไป',['/me/booking-metting'])?>
                </div>
                <table class="table table-striped table-bordered">
                    <tbody class="align-middle table-group-divider">
                        <tr class="text-center" style="background:#dcdcdc;">
                            <th class="text-start">สถานที่</th>
                            <th width="150px">วันที่ และเวลา</th>
                            <th>หัวข้อการใช้ห้องปรุชุม</th>
                            <th width="80px">จำนวน</th>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                            14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>
                           
                            <td class="text-center"><span class="badge badge-success">10</span></td>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                            14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>
                           
                            <td class="text-center"><span class="badge badge-success">8</span></td>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                            14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>
                           
                            <td class="text-center"><span class="badge badge-success">13</span></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
            <h6><i class="fa-regular fa-calendar-plus"></i> ปฏิทินรวม </h6>
            

            </div>
        </div>
    </div>
</div>