<?php
use yii\helpers\Html;
use app\modules\attendance\services\ScheduleDirectory;
use app\modules\attendance\services\WorkScheduleService;
$this->title = 'กำหนดเวลาทำงาน';
$targetName = $scope === 'employee' ? $target->fullname : $target->name;
$isDepartment = $scope === 'department';
$manager = WorkScheduleService::manager();
$this->registerJsFile('@web/js/attendance-schedule.js?v='.filemtime(Yii::getAlias('@webroot/js/attendance-schedule.js')),['depends'=>[\app\assets\AppAsset::class]]);
?>
<?= $this->render('_header', ['tab'=>$isDepartment ? 'departments' : 'employees']) ?>
<div class="attendance-settings">
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
            <div><h2 class="h5 fw-semibold mb-1"><?= Html::encode($targetName) ?></h2><div class="text-body-secondary small"><?= $isDepartment ? count($members).' คนที่ปฏิบัติงานอยู่ รวมหน่วยงานย่อย' : 'การกำหนดเวลารายบุคคล' ?></div></div>
            <?php if ($manager): ?><?= Html::a('กลับรายการ',['index','tab'=>$isDepartment?'departments':'employees'],['class'=>'btn btn-outline-secondary btn-sm']) ?><?php endif; ?>
        </div>
        <div class="card-body">
            <div class="mb-3 pb-3 border-bottom"><span class="text-body-secondary">เวลาที่ใช้วันนี้</span><div class="fw-semibold mt-1"><?= Html::encode(ScheduleDirectory::label($current)) ?></div></div>
            <?php if ($upcoming): ?><div class="alert alert-warning">มีการเปลี่ยนแปลงที่กำหนดไว้ เริ่ม <?= Html::encode($upcoming['effective_from']) ?> · <?= Html::encode($schedules[$upcoming['schedule_id']] ?? ($upcoming['mode'] === 'shift' ? 'ตามตารางเวร' : 'ตามหน่วยงาน')) ?> การบันทึกครั้งนี้ไม่ยกเลิกรายการวันอื่นที่กำหนดไว้</div><?php endif; ?>
            <?php if (!$schedules): ?><div class="alert alert-info">ยังไม่มีชุดเวลาปกติ <?= $manager ? Html::a('สร้างชุดเวลา',['create'],['class'=>'alert-link']) : 'กรุณาให้ผู้ดูแลสร้างชุดเวลาก่อน' ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger" role="alert" tabindex="-1" id="assignment-error"><?= Html::encode($error) ?></div><?php endif; ?>
            <?= Html::beginForm('', 'post', ['id'=>'assignment-form','data-scope'=>$scope]) ?>
            <?= Html::hiddenInput('member_count','0',['id'=>'member-expected-count']) ?>
            <?php if ($isDepartment): ?>
            <fieldset class="mb-4"><legend class="fs-6 fw-semibold">กำหนดเวลาให้</legend>
                <div class="d-flex flex-wrap gap-2">
                <?php foreach (['department'=>'ทั้งหน่วยงาน (เวลาปกติ)','members'=>'บุคลากรที่เลือก'] as $key=>$label): ?>
                    <?= Html::radio('Assignment[apply_to]',$values['apply_to'] === $key,['value'=>$key,'id'=>'apply-'.$key,'class'=>'btn-check']) ?>
                    <?= Html::label($label,'apply-'.$key,['class'=>'btn btn-outline-primary']) ?>
                <?php endforeach; ?>
                </div>
                <p class="small text-body-secondary mt-2 mb-0" id="department-hint">ใช้กับบุคลากรประเภทปกติที่รับเวลาจากหน่วยงาน รวมผู้เข้ามาใหม่ ไม่เปลี่ยนผู้ทำงานเป็นกะ ผู้มีเวลารายบุคคล หรือหน่วยงานย่อยที่กำหนดเวลาเอง</p>
                <p class="small text-body-secondary mt-2 mb-0" id="members-hint" hidden>เลือกหลายคนจากรายชื่อด้านล่าง ระบบบันทึกเป็นเวลารายบุคคลและแทนที่การตั้งค่าเดิมตั้งแต่วันที่มีผล</p>
            </fieldset>
            <?php endif; ?>
            <?= Html::hiddenInput('Assignment[mode]','normal') ?>
            <fieldset class="mb-4" id="assignment-modes"><legend class="fs-6 fw-semibold">รูปแบบการทำงาน</legend>
                <div class="d-flex flex-wrap gap-2">
                <?php foreach (['normal'=>'เวลาปกติรายบุคคล','shift'=>'ตามตารางเวร','inherit'=>'กลับไปใช้ค่าจากหน่วยงาน / โปรไฟล์'] as $key=>$label): ?>
                    <?= Html::radio('Assignment[mode]',$values['mode'] === $key,['value'=>$key,'id'=>'mode-'.$key,'class'=>'btn-check']) ?>
                    <?= Html::label($label,'mode-'.$key,['class'=>'btn btn-outline-primary']) ?>
                <?php endforeach; ?>
                </div>
                <p class="small text-body-secondary mt-2 mb-0">กรณีกลับไปใช้ค่าจากหน่วยงาน ระบบยังยึดประเภทเวรที่ระบุในโปรไฟล์ของแต่ละคน</p>
            </fieldset>
            <div class="row g-3">
                <div class="col-lg-8" id="schedule-choice">
                    <?= Html::label('ชุดเวลาปกติ','assignment-schedule',['class'=>'form-label fw-semibold']) ?>
                    <?= Html::hiddenInput('Assignment[schedule_id]','') ?>
                    <?= Html::dropDownList('Assignment[schedule_id]',$values['schedule_id'],$schedules,['id'=>'assignment-schedule','class'=>'form-select','prompt'=>'เลือกชุดเวลา']) ?>
                    <?php if ($manager): ?><div class="form-text">หากยังไม่มีเวลาที่ต้องการ <?= Html::a('สร้างชุดเวลา',['create']) ?></div><?php endif; ?>
                </div>
                <div class="col-lg-4">
                    <?= Html::label('วันที่เริ่มมีผล (ค.ศ.)','assignment-date',['class'=>'form-label fw-semibold']) ?>
                    <?= Html::input('date','Assignment[effective_from]',$values['effective_from'],['id'=>'assignment-date','required'=>true,'min'=>$today,'class'=>'form-control']) ?>
                </div>
                <div class="col-12">
                    <?= Html::label('เหตุผลในการกำหนดหรือเปลี่ยนแปลง','assignment-reason',['class'=>'form-label fw-semibold']) ?>
                    <?= Html::textarea('Assignment[reason]',$values['reason'],['id'=>'assignment-reason','required'=>true,'maxlength'=>2000,'rows'=>2,'placeholder'=>'เช่น เริ่มใช้เวลาปกติของหน่วยงาน หรือเปลี่ยนเวลาปฏิบัติงาน','class'=>'form-control']) ?>
                </div>
            </div>
            <?php if ($isDepartment): ?>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 mb-3">
                <h3 class="h6 fw-semibold mb-0">บุคลากรในหน่วยงาน</h3>
                <div class="member-tools d-flex flex-wrap gap-2" hidden>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-select-members="all">เลือกทั้งหมดในหน่วยงาน</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-select-members="none">ล้างที่เลือก</button>
                </div>
            </div>
            <div class="mb-3"><?= Html::label('ค้นหาในรายชื่อ','member-search',['class'=>'form-label small']) ?><?= Html::textInput(null,'',['id'=>'member-search','class'=>'form-control','placeholder'=>'พิมพ์ชื่อบุคลากรหรือหน่วยงาน']) ?></div>
            <p class="member-tools small fw-semibold" id="member-count" aria-live="polite" hidden>เลือก 0 คน</p>
            <div class="schedule-members">
            <table class="table table-hover align-middle schedule-table mb-0"><thead><tr><th class="member-select" hidden>เลือก</th><th>ชื่อ / หน่วยงาน</th><th>เวลาที่ใช้วันนี้</th><th>จัดการ</th></tr></thead><tbody>
            <?php foreach ($members as $member):
                $state = $directory->state($member,$today);
                $name = $member['fname'].' '.$member['lname'];
                $unitName = $directory->nodes[$member['department']]['name'] ?? '';
                $canAssign = WorkScheduleService::canAssign('employee',(int)$member['id']);
                $own = $state['source'] === 'รายบุคคล';
            ?>
                <tr data-member-name="<?= Html::encode($name.' '.$unitName) ?>">
                    <td class="member-select" data-label="เลือก" hidden><?= Html::checkbox('members[]',in_array((string)$member['id'],$selected,true),['value'=>$member['id'],'class'=>'form-check-input','aria-label'=>'เลือก '.$name,'disabled'=>!$canAssign,'data-eligible'=>$canAssign?'1':'0']) ?></td>
                    <td data-label="บุคลากร"><span class="fw-semibold"><?= Html::encode($name) ?></span><div class="small text-body-secondary"><?= Html::encode($unitName) ?></div></td>
                    <td data-label="เวลาปัจจุบัน"><?= Html::encode(ScheduleDirectory::label($state)) ?><div class="small text-body-secondary"><?= $own ? 'ตั้งค่ารายบุคคล' : ($state['mode'] === 'shift' ? 'ทำงานเป็นกะ' : ($state['mode'] !== 'normal' ? 'ยังไม่ระบุประเภทเวรในโปรไฟล์' : (($state['assignment'] && in_array((int)$id,$directory->chains[$state['assignment']['target_id']] ?? []) && (int)$state['assignment']['target_id'] !== (int)$id) ? 'หน่วยงานย่อยมีเวลาของตนเอง' : 'รับเวลาปกติจากหน่วยงาน'))) ?></div></td>
                    <td><?php if ($canAssign): ?><?= Html::a('แก้ไขรายบุคคล',['assign','scope'=>'employee','id'=>$member['id']],['class'=>'btn btn-outline-secondary btn-sm','aria-label'=>'แก้ไขเวลาของ '.$name]) ?><?php else: ?><span class="text-body-secondary small">ไม่มีสิทธิ์แก้ไขรายบุคคล</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$members): ?><tr><td colspan="4" class="py-4 text-center text-body-secondary">ยังไม่มีบุคลากรที่ปฏิบัติงานอยู่ในหน่วยงานนี้</td></tr><?php endif; ?>
            </tbody></table>
            </div>
            <p class="text-body-secondary mt-2" id="member-no-results" hidden>ไม่พบรายชื่อที่ตรงกับคำค้นหา</p>
            <?php endif; ?>
            <div class="d-grid d-sm-flex justify-content-sm-end gap-2 border-top pt-3 mt-4">
                <?= Html::submitButton('บันทึกการกำหนดเวลา',['class'=>'btn btn-primary','id'=>'assignment-submit']) ?>
                <?= Html::a('ยกเลิก',$manager ? ['index','tab'=>$isDepartment?'departments':'employees'] : ['/profile/index'],['class'=>'btn btn-outline-secondary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </section>
    <section class="card border-0 shadow-sm">
        <div class="card-header bg-body py-3"><h2 class="h6 fw-semibold mb-0">ประวัติการกำหนด<?= $isDepartment ? 'ระดับหน่วยงาน' : 'รายบุคคล' ?></h2></div>
        <div class="card-body p-0"><table class="table align-middle mb-0 schedule-table"><thead><tr><th>เริ่มมีผล</th><th>รูปแบบ / ชุดเวลา</th><th>เหตุผล</th><th>บันทึกเมื่อ</th></tr></thead><tbody>
        <?php foreach ($history as $row): ?><tr>
            <td data-label="เริ่มมีผล"><?= Html::encode($row['effective_from']) ?></td>
            <td data-label="ชุดเวลา"><?= Html::encode($row['mode']==='normal' ? ($schedules[$row['schedule_id']] ?? 'ไม่พบชุดเวลา') : ($row['mode']==='shift' ? 'ตามตารางเวร' : 'ตามหน่วยงาน / โปรไฟล์')) ?></td>
            <td data-label="เหตุผล"><?= Html::encode($row['reason']) ?></td>
            <td data-label="บันทึกเมื่อ"><?= Html::encode($row['created_at']) ?><div class="small text-body-secondary">ผู้ใช้ #<?= (int)$row['created_by'] ?></div></td>
        </tr><?php endforeach; ?>
        <?php if (!$history): ?><tr><td colspan="4" class="py-4 text-center text-body-secondary">ยังไม่มีประวัติการกำหนด<?= $isDepartment ? 'ระดับหน่วยงาน' : 'รายบุคคล' ?></td></tr><?php endif; ?>
        </tbody></table></div>
        <?php if ($isDepartment): ?><div class="card-footer bg-body small text-body-secondary">การบันทึกให้บุคลากรที่เลือก ดูประวัติได้จากปุ่มแก้ไขรายบุคคลของแต่ละคน</div><?php endif; ?>
    </section>
</div>
