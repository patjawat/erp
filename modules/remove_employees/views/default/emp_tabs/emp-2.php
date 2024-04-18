<?php
use app\components\AppHelper;
use yii\bootstrap5\Html;
use yii\widgets\Pjax;

?>

<div class="tab-pane fade" id="emp-2">
        <div class="row">
            <div class="col-md-12 d-flex">
                <div class="card profile-box flex-fill">
                    <div class="card-body">
                        <h3 class="card-title">ข้อมูลการรับตำแหน่ง
                            <?=Html::a('<i class="fas fa-pencil-alt"></i>',['/employees/categorise/create','emp_id' =>$model->id,'title' => 'ข้อมูลการรับตำแหน่ง', 'name' => 'emp-position'],['class' => 'edit-icon open-modal','data'=> ['size' => 'modal-md']]);?>
                        </h3>
                        <?php Pjax::begin(['id' => 'emp-position-container']); ?>
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ตั้งแต่วันที่</th>
                                        <th>ถึงวันที่</th>
                                        <th>รายการเปลี่ยนแปลง</th>
                                        <th>เลขที่ตำแหน่ง</th>
                                        <th>เงินเดือน</th>
                                        <th>เอกสารอ้างอิง</th>
                                        <th>ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($model->empPosition as $key => $position):?>
                                    <tr>
                                        <td><?=$key+1?></td>
                                        <td><?=isset($position->data_json['date_begin']) ? $position->data_json['date_begin'] : '-'?></td>
                                        <td><?=isset($position->data_json['date_end']) ? $position->data_json['date_end'] : '-'?></td>
                                   
                                        <td>นักจัดการทั่วไป กลุ่มงานบริหารทัวไป</td>
                                        <td>66282</td>
                                        <!-- <td>วิชาการ</td>
                                        <td>ชำนาญการ</td> -->
                                        <td>55,540</td>
                                        <td>จ.เลย 1436/2566 ลว. 19 พ.ค.2566</td>
                                        <td class="text-end">
                                            <div class="dropdown dropdown-action">
                                                <a aria-expanded="false" data-bs-toggle="dropdown"
                                                    class="action-icon dropdown-toggle" href="#"><i
                                                        class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <?=html::a('<i class="fa fa-pencil m-r-5"></i> Edit',
                                                    ['/employees/categorise/update','id' => $position->id,'title' => 'ข้อมูลการรับตำแหน่ง', 'name' => 'emp-position'],
                                                    ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg'],
                                                    
                                                    ])?>
                                                    <!-- <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                        Edit</a> -->
                                                    <a href="#" class="dropdown-item"><i
                                                            class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
<?php endforeach;?>
                                </tbody>
                            </table>
                            <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>