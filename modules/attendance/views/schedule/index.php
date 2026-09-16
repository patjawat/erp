<?php
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;
use app\modules\attendance\services\ScheduleDirectory;
$this->title = 'ตั้งค่าเวลาทำงาน';
$labels = ['departments'=>'เวลาทำงานของหน่วยงาน','employees'=>'เวลาทำงานรายบุคคล','schedules'=>'ชุดเวลาปกติ'];
?>
<?= $this->render('_header', ['tab'=>$tab]) ?>
<?php if ($preset): ?><div class="alert alert-info">กำลังกำหนดชุด <strong><?= Html::encode($preset->name.' · '.$preset->start_time.'–'.$preset->end_time) ?></strong> เลือกหน่วยงานด้านล่าง ระบบจะเลือกชุดเวลานี้ไว้ให้ <?= Html::a('ยกเลิกการเลือกชุดเวลา',['index'],['class'=>'alert-link']) ?></div><?php endif; ?>
<section class="card border-0 shadow-sm attendance-settings">
    <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
        <div><h2 class="h5 fw-semibold mb-1"><?= $labels[$tab] ?></h2><span class="text-body-secondary small"><?= $provider->totalCount ?> รายการ</span></div>
        <?= Html::a('<i class="bi bi-plus-lg me-1" aria-hidden="true"></i> สร้างชุดเวลา', ['create'], ['class'=>'btn btn-primary']) ?>
    </div>
    <div class="card-body border-bottom">
        <p class="text-body-secondary"><?= $tab === 'departments' ? 'เลือกหน่วยงานครั้งเดียว เพื่อกำหนดเวลาปกติให้ทั้งหน่วยงาน หรือเลือกบุคลากรหลายคนพร้อมกัน' : ($tab === 'employees' ? 'ตรวจสอบเวลาที่ใช้อยู่และแก้ไขรายบุคคล เลือกหน่วยงานเพื่อกรองรายชื่อ' : 'สร้างชุดเวลาสำหรับนำไปใช้ร่วมกัน หากต้องการเปลี่ยนเวลา ให้ปรับเป็นชุดใหม่แล้วกำหนดวันที่เริ่มใช้งาน') ?></p>
        <?= Html::beginForm(['index'],'get',['class'=>'row g-3 align-items-end']) ?>
        <?= Html::hiddenInput('tab',$tab) ?>
        <?php if ($preset): ?><?= Html::hiddenInput('schedule_id',$preset->id) ?><?php endif; ?>
        <div class="<?= $tab === 'employees' ? 'col-lg-5' : 'col-lg-8' ?>">
            <?= Html::label('ค้นหา'.($tab === 'departments' ? 'หน่วยงาน' : ($tab === 'employees' ? 'ชื่อบุคลากร' : 'ชุดเวลา')),'schedule-search',['class'=>'form-label']) ?>
            <?= Html::textInput('q',$q,['id'=>'schedule-search','class'=>'form-control','placeholder'=>'พิมพ์ชื่อที่ต้องการค้นหา']) ?>
        </div>
        <?php if ($tab === 'employees'): ?><div class="col-lg-4">
            <?= Html::label('หน่วยงาน (รวมหน่วยงานย่อย)','schedule-department',['class'=>'form-label']) ?>
            <?= \kartik\select2\Select2::widget(['name'=>'department','value'=>$department,'data'=>\yii\helpers\ArrayHelper::map($directory->nodes,'id','name'),'options'=>['id'=>'schedule-department','placeholder'=>'ทุกหน่วยงาน'],'pluginOptions'=>['allowClear'=>true]]) ?>
        </div><?php endif; ?>
        <div class="col-lg-3 d-flex gap-2"><?= Html::submitButton('ค้นหา',['class'=>'btn btn-outline-primary']) ?><?= Html::a('ล้างตัวกรอง',['index','tab'=>$tab,'schedule_id'=>$preset->id ?? null],['class'=>'btn btn-outline-secondary']) ?></div>
        <?= Html::endForm() ?>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0 schedule-table">
            <thead><tr><th><?= $tab === 'departments' ? 'หน่วยงาน / บุคลากร' : ($tab === 'employees' ? 'บุคลากร / หน่วยงาน' : 'ชื่อชุดเวลา') ?></th><th><?= $tab === 'schedules' ? 'เวลา / วันทำงาน' : 'เวลาที่ใช้วันนี้' ?></th><th><?= $tab === 'schedules' ? 'เงื่อนไข' : 'การเปลี่ยนแปลงที่กำหนดไว้' ?></th><th class="text-lg-end">จัดการ</th></tr></thead>
            <tbody>
            <?php foreach ($provider->models as $row): ?>
                <?php if ($tab === 'schedules'): ?>
                <tr>
                    <td data-label="ชุดเวลา"><span class="fw-semibold"><?= Html::encode($row->name) ?></span></td>
                    <td data-label="เวลา"><div class="fw-semibold"><?= Html::encode($row->start_time.'–'.$row->end_time) ?></div><div class="small text-body-secondary"><?= Html::encode($row->weekdayLabel) ?></div></td>
                    <td data-label="เงื่อนไข">ผ่อนผัน <?= (int)$row->grace_minutes ?> นาที<div class="small text-body-secondary">ใช้วันหยุดส่วนกลาง<?= $row->holidays ? ' และวันหยุดเพิ่มเติม' : '' ?></div></td>
                    <td class="text-lg-end"><?= Html::a('ปรับชุดเวลา',['create','copy'=>$row->id],['class'=>'btn btn-outline-secondary btn-sm','aria-label'=>'ปรับชุดเวลา '.$row->name]) ?> <?= Html::a('กำหนดให้หน่วยงาน',['index','schedule_id'=>$row->id],['class'=>'btn btn-outline-primary btn-sm']) ?></td>
                </tr>
                <?php else:
                    $scope = $tab === 'departments' ? 'department' : 'employee';
                    $name = $scope === 'department' ? $row['name'] : $row['fname'].' '.$row['lname'];
                    $state = $scope === 'department' ? $directory->departmentState((int)$row['id'],$today) : $directory->state($row,$today);
                    $future = $directory->upcoming($scope,(int)$row['id'],$today);
                ?>
                <tr>
                    <td data-label="<?= $scope === 'department' ? 'หน่วยงาน' : 'บุคลากร' ?>"><span class="fw-semibold"><?= Html::encode($name) ?></span>
                        <div class="small text-body-secondary"><?= $scope === 'department' ? count($directory->members((int)$row['id'])).' คน รวมหน่วยงานย่อย' : Html::encode($directory->nodes[$row['department']]['name'] ?? 'ไม่ระบุหน่วยงาน') ?></div>
                    </td>
                    <td data-label="เวลาที่ใช้วันนี้"><div><?= Html::encode(ScheduleDirectory::label($state)) ?></div>
                        <?php if ($state['assignment']): ?><span class="small text-body-secondary"><?= $scope === 'department' && (int)$state['assignment']['target_id'] !== (int)$row['id'] ? 'รับจากหน่วยงานต้นสังกัด' : Html::encode($state['source']) ?> · ตั้งแต่ <?= Html::encode($state['assignment']['effective_from']) ?></span><?php endif; ?>
                    </td>
                    <td data-label="กำหนดไว้ล่วงหน้า"><?php if ($future): ?><span class="badge bg-warning-subtle text-warning-emphasis">เริ่ม <?= Html::encode($future['effective_from']) ?></span><div class="small mt-1"><?= Html::encode($directory->schedules[$future['schedule_id']]->name ?? ($future['mode'] === 'shift' ? 'ตามตารางเวร' : 'ตามหน่วยงาน')) ?></div><?php else: ?><span class="text-body-secondary">—</span><?php endif; ?></td>
                    <td class="text-lg-end"><?= Html::a($state['assignment'] ? 'ดู / แก้ไข' : 'กำหนดเวลา',['assign','scope'=>$scope,'id'=>$row['id'],'schedule_id'=>$preset->id ?? null],['class'=>'btn btn-outline-primary btn-sm','aria-label'=>'กำหนดหรือแก้ไขเวลาของ '.$name]) ?></td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$provider->models): ?><tr><td colspan="4" class="text-center py-5 text-body-secondary"><?= $q !== '' || $department !== '' ? 'ไม่พบข้อมูลตามตัวกรอง ลองเปลี่ยนคำค้นหาหรือล้างตัวกรอง' : ($tab === 'schedules' ? 'ยังไม่มีชุดเวลา เริ่มจากปุ่มสร้างชุดเวลา' : 'ยังไม่มีข้อมูล') ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($provider->pagination->pageCount > 1): ?><div class="card-footer bg-body py-3"><?= LinkPager::widget(['pagination'=>$provider->pagination,'maxButtonCount'=>5]) ?></div><?php endif; ?>
</section>
