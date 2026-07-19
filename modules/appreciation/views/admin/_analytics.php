<?php

use yii\helpers\Html;
use app\modules\appreciation\models\AppreciationParticipation;
use app\modules\appreciation\services\AppreciationSnapshotService;

$ageLabels = AppreciationSnapshotService::ageBandLabels();
$statusLabels = AppreciationParticipation::statusLabels();
$valueTotals = [];
$thanksTotal = 0;
$activityTotal = 0;
foreach ($analyticsRows as $row) {
    $thanksTotal += (int) $row['thanks'];
    $activityTotal += (int) $row['activities'];
    foreach ($row['values'] as $code => $points) $valueTotals[$code] = ($valueTotals[$code] ?? 0) + (int) $points;
}
$maxValueTotal = $valueTotals ? max($valueTotals) : 0;

$departmentOptions = $positionOptions = [];
foreach ($analyticsRows as $row) {
    $employee = $employees[$row['emp_id']] ?? null;
    $department = $row['department_name'] ?: ($employee ? $employee->departmentName() : 'ไม่ระบุหน่วยงาน');
    $position = $row['position_group_name'] ?: $row['position_name'] ?: ($employee ? $employee->positionGroupName() : 'ไม่ระบุตำแหน่ง');
    $departmentOptions[$department] = $department;
    $positionOptions[$position] = $position;
}
ksort($departmentOptions);
ksort($positionOptions);
?>

<section class="tab-pane fade appreciation-analytics" id="analytics">
    <div class="appreciation-analytics__head">
        <div>
            <h2>วิเคราะห์การมีส่วนร่วมและค่านิยมองค์กร</h2>
            <p><?= $activeYear ? Html::encode($activeYear->name) : 'ยังไม่มีรอบกิจกรรมที่เปิดใช้งาน' ?> · ข้อมูลรายบุคคลสำหรับ HR และผู้ดูแลระบบเท่านั้น</p>
        </div>
        <span class="appreciation-privacy-badge"><i class="bi bi-shield-lock" aria-hidden="true"></i> ข้อมูลภายใน</span>
    </div>

    <?php if (!$activeYear): ?>
        <div class="appreciation-empty"><h3>ยังไม่มีข้อมูลสำหรับวิเคราะห์</h3><p>เปิดรอบกิจกรรมก่อนเพื่อเริ่มรวบรวมข้อมูล</p></div>
    <?php else: ?>
        <div class="appreciation-analytics-summary">
            <div><span>บุคลากรที่มีคะแนน</span><strong><?= number_format(count($analyticsRows)) ?></strong><small>คน</small></div>
            <div><span>คะแนนคำขอบคุณ</span><strong><?= number_format($thanksTotal) ?></strong><small>คะแนน</small></div>
            <div><span>คะแนนกิจกรรม</span><strong><?= number_format($activityTotal) ?></strong><small>คะแนน</small></div>
            <div><span>ผู้ร่วมกิจกรรม</span><strong><?= number_format($participantAnalytics['total']) ?></strong><small>คน</small></div>
        </div>

        <div class="appreciation-analytics-grid">
            <section class="appreciation-analysis-panel" aria-labelledby="value-analysis-title">
                <header><h3 id="value-analysis-title">คะแนนตามค่านิยมองค์กร</h3><p>ผลรวมคะแนนคำขอบคุณ ไม่รวมคะแนนกิจกรรม</p></header>
                <?php if (!$valueColumns): ?>
                    <div class="appreciation-quiet-state">ยังไม่มีการตั้งค่าค่านิยมองค์กร</div>
                <?php else: ?>
                    <div class="appreciation-breakdown-list">
                        <?php foreach ($valueColumns as $index => $value): $total=(int)($valueTotals[$value['code']]??0); $percent=$maxValueTotal ? round($total*100/$maxValueTotal) : 0; ?>
                            <div class="appreciation-breakdown appreciation-breakdown--<?= ($index % 4) + 1 ?>">
                                <div><span><?= Html::encode($value['name']) ?></span><strong><?= number_format($total) ?> คะแนน</strong></div>
                                <div class="appreciation-breakdown__track"><span style="width:<?= $percent ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="appreciation-analysis-panel" aria-labelledby="participant-analysis-title">
                <header><h3 id="participant-analysis-title">กลุ่มผู้เข้าร่วมกิจกรรม</h3><p>จำนวนบุคลากรแบบไม่นับซ้ำในแต่ละกลุ่ม</p></header>
                <div class="appreciation-analysis-columns">
                    <?php foreach ([
                        ['title'=>'หน่วยงาน','items'=>$participantAnalytics['departments'],'labels'=>[]],
                        ['title'=>'กลุ่มตำแหน่ง','items'=>$participantAnalytics['positions'],'labels'=>[]],
                        ['title'=>'ช่วงอายุ','items'=>$participantAnalytics['ages'],'labels'=>$ageLabels],
                        ['title'=>'สถานะ','items'=>$participantAnalytics['statuses'],'labels'=>$statusLabels],
                    ] as $group): ?>
                        <div><h4><?= $group['title'] ?></h4><ul role="list">
                            <?php foreach (array_slice($group['items'],0,5) as $item): $key=$item['label']?:'unknown'; ?>
                                <li><span><?= Html::encode($group['labels'][$key]??($item['label']?:'ไม่ระบุ')) ?></span><strong><?= number_format($item['count']) ?></strong></li>
                            <?php endforeach; ?>
                            <?php if (!$group['items']): ?><li><span>ยังไม่มีข้อมูล</span><strong>0</strong></li><?php endif; ?>
                        </ul></div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <section class="appreciation-analysis-panel appreciation-participant-detail" aria-labelledby="participant-detail-title">
            <header><h3 id="participant-detail-title">รายละเอียดผู้เข้าร่วมกิจกรรมล่าสุด</h3><p>แสดงข้อมูล snapshot ณ วันที่ลงทะเบียน สูงสุด 100 รายการล่าสุด</p></header>
            <?php if (!$participations): ?>
                <div class="appreciation-quiet-state">ยังไม่มีผู้เข้าร่วมกิจกรรม</div>
            <?php else: ?>
                <div class="d-none d-lg-block">
                    <table class="table appreciation-participant-table mb-0">
                        <thead><tr><th>บุคลากร</th><th>ตำแหน่งและหน่วยงาน</th><th>ช่วงอายุ</th><th>กิจกรรม</th><th>สถานะ</th><th class="text-end">คะแนน</th></tr></thead>
                        <tbody><?php foreach($participations as $participation): $employee=$employees[$participation->emp_id]??null; $name=$employee?$employee->fullname():'#'.$participation->emp_id; $department=$participation->department_name_snapshot?:($employee?$employee->departmentName():'ไม่ระบุหน่วยงาน'); $position=$participation->position_name_snapshot?:($employee?$employee->positionName():'ไม่ระบุตำแหน่ง'); $ageBand=$participation->age_band_snapshot?:AppreciationSnapshotService::ageBand($employee->age??null); ?>
                            <tr><td><strong><?= Html::encode($name) ?></strong><span><?= Yii::$app->formatter->asDatetime($participation->registered_at) ?></span></td><td><strong><?= Html::encode($position) ?></strong><span><?= Html::encode($department) ?></span></td><td><?= Html::encode($ageLabels[$ageBand]??'ไม่ระบุอายุ') ?></td><td><?= Html::encode($participation->activity?$participation->activity->title:'—') ?></td><td><span class="appreciation-status appreciation-status--<?= Html::encode($participation->status) ?>"><?= Html::encode($statusLabels[$participation->status]??$participation->status) ?></span></td><td class="text-end"><?= number_format($participation->points_awarded) ?></td></tr>
                        <?php endforeach; ?></tbody>
                    </table>
                </div>
                <ul class="appreciation-participant-cards d-lg-none" role="list">
                    <?php foreach($participations as $participation): $employee=$employees[$participation->emp_id]??null; $name=$employee?$employee->fullname():'#'.$participation->emp_id; $department=$participation->department_name_snapshot?:($employee?$employee->departmentName():'ไม่ระบุหน่วยงาน'); $position=$participation->position_name_snapshot?:($employee?$employee->positionName():'ไม่ระบุตำแหน่ง'); $ageBand=$participation->age_band_snapshot?:AppreciationSnapshotService::ageBand($employee->age??null); ?>
                        <li><header><strong><?= Html::encode($name) ?></strong><span class="appreciation-status appreciation-status--<?= Html::encode($participation->status) ?>"><?= Html::encode($statusLabels[$participation->status]??$participation->status) ?></span></header><p><?= Html::encode($position) ?> · <?= Html::encode($department) ?> · <?= Html::encode($ageLabels[$ageBand]??'ไม่ระบุอายุ') ?></p><footer><span><?= Html::encode($participation->activity?$participation->activity->title:'—') ?></span><strong><?= number_format($participation->points_awarded) ?> คะแนน</strong></footer></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="appreciation-analysis-panel appreciation-score-matrix" aria-labelledby="score-matrix-title">
            <header><h3 id="score-matrix-title">คะแนนรายบุคคลแยกตามค่านิยม</h3><p>ใช้เพื่อวิเคราะห์ภายใน ไม่ใช่การจัดอันดับบุคลากร</p></header>
            <div class="appreciation-analysis-filters">
                <label><span>ค้นหาบุคลากร</span><input type="search" class="form-control" id="analytics-search" placeholder="ชื่อหรือนามสกุล"></label>
                <label><span>หน่วยงาน</span><select class="form-select" id="analytics-department"><option value="">ทุกหน่วยงาน</option><?php foreach($departmentOptions as $option): ?><option value="<?= Html::encode($option) ?>"><?= Html::encode($option) ?></option><?php endforeach; ?></select></label>
                <label><span>กลุ่มตำแหน่ง</span><select class="form-select" id="analytics-position"><option value="">ทุกตำแหน่ง</option><?php foreach($positionOptions as $option): ?><option value="<?= Html::encode($option) ?>"><?= Html::encode($option) ?></option><?php endforeach; ?></select></label>
                <label><span>ช่วงอายุ</span><select class="form-select" id="analytics-age"><option value="">ทุกช่วงอายุ</option><?php foreach($ageLabels as $code=>$label): ?><option value="<?= Html::encode($code) ?>"><?= Html::encode($label) ?></option><?php endforeach; ?></select></label>
            </div>

            <?php if (!$analyticsRows): ?>
                <div class="appreciation-empty"><h3>ยังไม่มีคะแนนในรอบนี้</h3><p>ข้อมูลจะแสดงเมื่อมีคำขอบคุณหรือกิจกรรมที่ได้รับคะแนน</p></div>
            <?php else: ?>
                <div class="d-none d-lg-block appreciation-matrix-wrap">
                    <table class="table appreciation-matrix mb-0">
                        <thead><tr><th>บุคลากร</th><?php foreach($valueColumns as $value): ?><th class="text-end"><?= Html::encode($value['name']) ?></th><?php endforeach; ?><th class="text-end">กิจกรรม</th><th class="text-end">รวม</th></tr></thead>
                        <tbody>
                        <?php foreach($analyticsRows as $row): $employee=$employees[$row['emp_id']]??null; $name=$employee?$employee->fullname():'#'.$row['emp_id']; $department=$row['department_name']?:($employee?$employee->departmentName():'ไม่ระบุหน่วยงาน'); $position=$row['position_group_name']?:$row['position_name']?:($employee?$employee->positionGroupName():'ไม่ระบุตำแหน่ง'); $ageBand=$row['age_band']?:AppreciationSnapshotService::ageBand($employee->age??null); ?>
                            <tr data-analytics-row data-name="<?= Html::encode(mb_strtolower($name)) ?>" data-department="<?= Html::encode($department) ?>" data-position="<?= Html::encode($position) ?>" data-age="<?= Html::encode($ageBand) ?>">
                                <td><strong><?= Html::encode($name) ?></strong><span><?= Html::encode($position) ?> · <?= Html::encode($department) ?> · <?= Html::encode($ageLabels[$ageBand]??'ไม่ระบุอายุ') ?></span></td>
                                <?php foreach($valueColumns as $value): ?><td class="text-end"><?= number_format($row['values'][$value['code']]??0) ?></td><?php endforeach; ?>
                                <td class="text-end"><?= number_format($row['activities']) ?></td><td class="text-end fw-semibold"><?= number_format($row['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <ul class="appreciation-score-cards d-lg-none" role="list">
                    <?php foreach($analyticsRows as $row): $employee=$employees[$row['emp_id']]??null; $name=$employee?$employee->fullname():'#'.$row['emp_id']; $department=$row['department_name']?:($employee?$employee->departmentName():'ไม่ระบุหน่วยงาน'); $position=$row['position_group_name']?:$row['position_name']?:($employee?$employee->positionGroupName():'ไม่ระบุตำแหน่ง'); $ageBand=$row['age_band']?:AppreciationSnapshotService::ageBand($employee->age??null); ?>
                        <li data-analytics-row data-name="<?= Html::encode(mb_strtolower($name)) ?>" data-department="<?= Html::encode($department) ?>" data-position="<?= Html::encode($position) ?>" data-age="<?= Html::encode($ageBand) ?>">
                            <header><div><strong><?= Html::encode($name) ?></strong><span><?= Html::encode($position) ?> · <?= Html::encode($department) ?></span></div><b><?= number_format($row['total']) ?> คะแนน</b></header>
                            <dl><?php foreach($valueColumns as $value): ?><div><dt><?= Html::encode($value['name']) ?></dt><dd><?= number_format($row['values'][$value['code']]??0) ?></dd></div><?php endforeach; ?><div><dt>กิจกรรม</dt><dd><?= number_format($row['activities']) ?></dd></div></dl>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p id="analytics-no-results" class="appreciation-no-results" hidden>ไม่พบข้อมูลที่ตรงกับตัวกรอง</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>

<?php
$this->registerJs(<<<'JS'
function filterAppreciationAnalytics() {
    var search = ($('#analytics-search').val() || '').toLocaleLowerCase('th');
    var department = $('#analytics-department').val() || '';
    var position = $('#analytics-position').val() || '';
    var age = $('#analytics-age').val() || '';
    var visiblePeople = {};
    $('[data-analytics-row]').each(function() {
        var row = $(this);
        var match = (!search || (row.data('name') || '').indexOf(search) !== -1)
            && (!department || row.data('department') === department)
            && (!position || row.data('position') === position)
            && (!age || row.data('age') === age);
        row.toggle(match);
        if (match) visiblePeople[row.data('name')] = true;
    });
    $('#analytics-no-results').prop('hidden', Object.keys(visiblePeople).length > 0);
}
$(document).on('input change', '#analytics-search,#analytics-department,#analytics-position,#analytics-age', filterAppreciationAnalytics);
JS);
?>
