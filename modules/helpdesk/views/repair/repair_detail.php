<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;
?>
<tr>
            <td colspan="6" class="text-center bg-warning-subtle">
                <span class="fw-semibold"> <i class="fa-solid fa-file-pen"></i> บันทึกการแจ้งซ่อม : </span>
                <?=Yii::$app->thaiFormatter->asDateTime($repair->created_at,'medium')?>
            </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ผู้แจ้งซ่อม : </span></td>
            <td colspan="3">
                <span class="text-danger"><?=isset($repair->data_json['create_name']) ? $repair->data_json['create_name'] : ''?></span>
            </td>
            <td class="text-end"><span class="fw-semibold">ความเร่งด่วน : </span></td>
            <td colspan="3">
                <span class="text-danger"><?=isset( $repair->data_json['urgency'])  ? $repair->data_json['urgency'] : ''?></span>
            </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">อาการแจ้งซ่อม : </span></td>
            <td colspan="3">
                <span class="text-danger"><?=$repair->data_json['title']?></span>
            </td>
            <td class="text-end"><span class="fw-semibold">สภานะงานซ่อม : </span></td>
            <td colspan="3">
                <?php if($repair->data_json['repair_status'] == 'ร้องขอ'):?>
                <label class="badge rounded-pill text-danger-emphasis bg-danger-subtle py-2 fs-6 align-middle">
                    <i class="fa-regular fa-hourglass-half"></i> ร้องขอ</label>
                <?php else:?>
                <?=$repair->data_json['repair_status']?>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ข้อมูลเพิ่มเติม : </span></td>
            <td colspan="5">
                <span class="text-danger">
                    <?=$repair->data_json['note']?>
                </span>
            </td>
        </tr>
        <?php if(isset($repair->data_json['technician_name'])):?>
        <tr class="align-middle">
            <td class="text-end"><span class="fw-semibold">ผู้รับเรื่อง : </span></td>
            <td colspan="3"><?=$repair->data_json['technician_name'];?></td>
            <td class="text-end"><span class="fw-semibold">ช่างผู้ร่วมงาน : </span></td>
            <td colspan="3">

                <div class="avatar-stack">
                    <?php if(isset($repair->data_json['join']) && $repair->data_json['join'] != ""):?>
                    <?php foreach($repair->data_json['join'] as $key => $avatar):?>
                    <?php 
                                // print_r($avatar);
                               $emp = Employees::findOne(['user_id' => $avatar]);
                            //    print_r($emp->fullname);

                                ?>

                    <a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="" data-bs-title="<?=$emp->fullname?>">
                        <?=Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle'])?>

                    </a>
                    <?php endforeach;?>
                    <?php endif;?>
                </div>
            </td>
        </tr>
        <?php endif;?>