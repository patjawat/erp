<?php
use yii\helpers\Html;
use app\modules\inventoryV2\services\MonthlySnapshotRestoreService;
$this->title='คืนยอดยกไปจาก Excel';
$this->params['breadcrumbs'][]=['label'=>'ตรวจไฟล์ยอดปิดเดือน','url'=>['index']];
$this->params['breadcrumbs'][]=$this->title;
$decisions=json_decode($draft['decisions_json'] ?: '{}',true);
$chosen=$decisions['rows'] ?? [];
$money=static fn($v)=>number_format((float)$v,2);
$source=json_decode($draft['source_json'],true);
$rowErrors=[]; $otherErrors=[];
foreach ($plan['errors'] ?? [] as $message) {
    if (preg_match('/^แถว (\d+):/u',$message,$match)) $rowErrors[(int)$match[1]][]=$message;
    else $otherErrors[]=$message;
}
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main',['active'=>'report-monthly-snapshot']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-4">
    <h1 class="h4"><?= Html::encode($this->title) ?> · <?= (int)$draft['report_month'] ?>/<?= (int)$draft['report_year']+543 ?></h1>
    <p class="text-body-secondary">ชุดที่ <?= (int)$draft['id'] ?> · ยอดรับรอง <?= $money($source['source_total']) ?> บาท · <?= Html::encode(['draft'=>'กำลังเตรียม','committed'=>'คืนยอดและล็อกแล้ว','reverted'=>'ย้อนคืนแล้ว'][$draft['status']] ?? $draft['status']) ?></p>
    <?php foreach (['success'=>'success','error'=>'danger'] as $key=>$tone): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?><div class="alert alert-<?= $tone ?>" role="status"><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= Html::encode($error) ?></div><?php endif; ?>
    <div class="alert alert-info">คืนเฉพาะจำนวนและมูลค่า <strong>ยอดยกไป</strong> เพื่อเป็นยอดยกมาเดือนถัดไป โดยยึดตัวเลขใน Excel แม้ไม่ตรงสูตร รายการรับ–จ่ายและการแยกจ่าย รพ.สต./โรงพยาบาลยังคงเดิม ไม่ปรับสต็อกจริง</div>
    <?php if ($result): ?>
        <?php if ($plan && $plan['errors']): ?>
            <div class="alert alert-warning" role="status"><strong>ยังคืนยอดไม่ได้ · ต้องตรวจ <?= count($rowErrors) ?> รายการ</strong>
                <p class="mb-2">แก้รายการตามลิงก์ด้านล่าง แล้วกด “บันทึกการจับคู่และตรวจตัวอย่าง” อีกครั้ง ยอดปิดเดือนยังไม่ถูกเปลี่ยน</p>
                <details><summary>เปิดรายการที่ต้องแก้ <?= count($rowErrors) ?> รายการ</summary><ul class="mb-0">
                    <?php foreach ($rowErrors as $n=>$messages): ?><li><?= Html::a(implode(' / ',$messages),'#restore-row-'.$n) ?></li><?php endforeach; ?>
                </ul></details>
                <?php if ($otherErrors): ?><details class="mt-2"><summary>เงื่อนไขรวมและยอดที่ยังจับคู่ไม่ครบ</summary><p class="small">ให้แก้รายการด้านบนก่อน แล้วตรวจใหม่ หากยังมียอดเดิมไม่ครอบคลุม ต้องตรวจคลังและรหัสที่เหลือ ห้ามข้ามยอดเหล่านั้น</p><ul><?php foreach ($otherErrors as $e): ?><li><?= Html::encode($e) ?></li><?php endforeach; ?></ul></details><?php endif; ?>
                <?php if ($rowErrors): ?><?= Html::a('ไปยังรายการแรกที่ต้องแก้','#restore-row-'.array_key_first($rowErrors),['class'=>'btn btn-outline-dark mt-3']) ?><?php endif; ?>
            </div>
        <?php endif; ?>
        <?= Html::beginForm(['draft','id'=>$draft['id']],'post') ?>
        <h2 class="h5 mt-4">เลือกเฉพาะรายการที่ยังระบุคลังไม่ได้</h2>
        <p>ระบบใช้คลังจากยอดปิดเดือนเดิมให้เมื่อพบเพียงคลังเดียว รายการที่ผ่านแล้วซ่อนไว้ให้ ส่วนรหัสที่พบหลายคลังหรือซ้ำใน Excel ต้องระบุให้ตรงแถว</p>
        <label class="d-block mb-3"><input type="checkbox" id="show-resolved-rows"> แสดงรายการที่ผ่านแล้วเพื่อทบทวน</label>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th scope="col">แถว / รายการ</th><th scope="col">จำนวน / มูลค่ารับรอง</th><th scope="col">คลัง</th><th scope="col">รหัสปลายทาง / เหตุผล</th></tr></thead><tbody>
        <?php foreach ($result['rows'] as $r): ?>
            <?php
                $n=$r['row']; $d=$chosen[$n] ?? [];
                $needsReview=$r['issues'] || isset($chosen[$n]) || isset($rowErrors[$n]);
                // Also show unit differences, even when the initial reconciliation found one candidate.
                $unitCheck=false;
                foreach ($r['current_snapshots'] as $snap) if (isset($snap['unit_name']) && trim($snap['unit_name'])!==trim($r['unit'])) $unitCheck=true;
                if (!$needsReview && !$unitCheck) continue;
                $default=MonthlySnapshotRestoreService::defaultWarehouse($r,$result['duplicates']) ?: '';
                $resolved=$plan && !isset($rowErrors[$n]);
                $options=[];
                foreach ($r['current_snapshots'] as $snapshot) $options[$snapshot['warehouse_id']]=$result['warehouses'][$snapshot['warehouse_id']];
                if (!$options) $options=$r['candidates'] ?: $result['warehouses'];
                if (!empty($d['warehouse_id']) && isset($result['warehouses'][$d['warehouse_id']])) $options[$d['warehouse_id']]=$result['warehouses'][$d['warehouse_id']];
                $allZero=true;
                foreach (['opening_qty','opening_value','in_qty','in_value','out_qty','out_value','closing_qty','closing_value'] as $field) {
                    if (abs($r[$field]) > .000001) $allZero=false;
                }
            ?>
            <tr id="restore-row-<?= (int)$n ?>" <?= $resolved ? 'data-resolved="1" hidden' : '' ?>><td>แถว <?= (int)$n ?> · <?= Html::encode($r['item_code']) ?><div><?= Html::encode($r['item_name']) ?></div>
                <?php if (isset($rowErrors[$n])): ?><div class="text-danger small mt-2"><strong>ต้องแก้:</strong> <?= Html::encode(implode(' / ',$rowErrors[$n])) ?></div>
                <?php elseif ($plan): ?><div class="small text-success">รายการนี้ผ่านการตรวจแล้ว</div><?php endif; ?>
                <?php if (in_array('จำนวนไม่ตรงสูตร',$r['issues'],true)): ?><div class="small text-body-secondary">จำนวนไม่ตรงสูตร: ใช้ยอดยกไป Excel ตามที่รับรองแล้ว</div><?php endif; ?>
                <?php if ($r['current_snapshots']): ?><div class="mt-2 small"><strong>ยอดปิดเดือนเดิม · <?= count($r['current_snapshots']) ?> คลัง/รายการ</strong><?php foreach ($r['current_snapshots'] as $s): ?><div><?= Html::encode($result['warehouses'][$s['warehouse_id']]) ?>: <?= $money($s['closing_qty']) ?> <?= Html::encode($s['unit_name'] ?? '') ?> / <?= $money($s['closing_value']) ?> บาท</div><?php endforeach; ?></div><?php endif; ?>
            </td><td class="text-end"><?= $money($r['closing_qty']) ?> <?= Html::encode($r['unit']) ?><div><?= $money($r['closing_value']) ?> บาท</div></td>
            <td><?= Html::dropDownList("rows[$n][warehouse_id]",$d['warehouse_id'] ?? $default,$options,['prompt'=>'เลือกจากคลังที่พบ','class'=>'form-select','aria-label'=>"คลังแถว $n"]) ?>
                <?php if ($default && (int)($d['warehouse_id'] ?? $default)===(int)$default): ?><div class="small text-body-secondary">คลังตรงกับข้อเสนอจากข้อมูลเดิม</div><?php endif; ?></td>
            <td><details <?= (!$resolved && preg_match('/รหัส|ทะเบียน|snapshot/u',implode(' ',$rowErrors[$n] ?? []))) ? 'open' : '' ?>><summary>แก้รหัสหรือระบุเหตุผลเพิ่มเติม</summary>
                <?= Html::textInput("rows[$n][item_code]",$d['item_code'] ?? $r['item_code'],['class'=>'form-control mb-2','aria-label'=>"รหัสปลายทางแถว $n",'maxlength'=>50]) ?>
                <?= Html::textInput("rows[$n][note]",$d['note'] ?? '',['class'=>'form-control mb-2','placeholder'=>'เหตุผลการจับคู่ / ยกเว้น','aria-label'=>"เหตุผลแถว $n"]) ?>
                </details>
                <?php if ($allZero || !empty($d['skip'])): ?>
                    <label class="d-block small"><?= Html::checkbox("rows[$n][skip]",!empty($d['skip']),['value'=>'1']) ?> ข้ามรายการนี้ (ต้องระบุเหตุผล)</label>
                    <div class="small text-body-secondary"><?= $allZero ? 'ตรวจ Excel แล้ว: ยกมา รับ จ่าย และยกไปเป็นศูนย์ทุกช่อง จึงเลือกข้ามได้' : 'ข้ามไม่ได้: Excel มีตัวเลขที่ไม่ใช่ศูนย์ ให้เอาเครื่องหมายข้ามออก' ?></div>
                <?php else: ?><div class="small text-body-secondary">รายการนี้มีตัวเลขใน Excel ต้องจับคู่คลังและรหัส ไม่สามารถข้ามได้</div><?php endif; ?>
                <?php if ($unitCheck || !empty($d['unit_confirmed'])): ?><label class="d-block small"><?= Html::checkbox("rows[$n][unit_confirmed]",!empty($d['unit_confirmed']),['value'=>'1']) ?> ตรวจแล้ว ใช้หน่วย <?= Html::encode($r['unit']) ?> ตาม Excel</label><?php endif; ?>
            </td></tr>
        <?php endforeach; ?></tbody></table></div>
        <?php $newTargets=array_filter($plan['targets'] ?? [],static fn($t)=>!empty($t['is_new'])); ?>
        <?php if ($newTargets): ?><div class="alert alert-info"><strong>จะเพิ่มยอดรับรองที่ไม่มีข้อมูลปิดเดือนเดิม <?= count($newTargets) ?> รายการ</strong>
            <ul><?php foreach ($newTargets as $t): ?><li><?= Html::encode($result['warehouses'][$t['warehouse_id']].' · '.$t['item_code']) ?>: <?= $money($t['closing_qty']) ?> <?= Html::encode($t['unit_name']) ?> / <?= $money($t['closing_value']) ?> บาท</li><?php endforeach; ?></ul>
            <p class="mb-0">สร้างเฉพาะยอดยกไปจาก Excel เมื่อยืนยันคืนยอด ช่องยกมาและรับ–จ่ายของแถวใหม่ยังเป็นศูนย์ ไม่ได้สร้างเอกสารรับ–จ่ายย้อนหลัง และจะเก็บประวัติเพื่อย้อนคืนได้</p>
        </div><?php endif; ?>
        <?php if ($result['later_periods']): ?><div class="alert alert-warning mt-3"><p>มีข้อมูลปิดเดือนหลังงวดนี้ <?= count($result['later_periods']) ?> คู่คลัง/งวด การคืนยอดต้องสำรองและยกเลิกข้อมูลปิดเหล่านั้น เพื่อให้ปิดใหม่จากยอดรับรอง</p>
            <label><?= Html::checkbox('clear_later',!empty($decisions['clear_later']),['value'=>'1']) ?> ยืนยันสำรองและยกเลิกรายงานปิดเดือนงวดถัดไป เพื่อให้ปิดใหม่จากยอดรับรอง</label><p class="small mt-2 mb-0">ไม่ลบเอกสารรับ–จ่าย ขั้นนี้บันทึกเพียงตัวเลือก จะสำรองและยกเลิกรายงานเมื่อกด “คืนยอดและล็อกงวด” ในขั้นสุดท้าย</p></div><?php endif; ?>
        <label for="restore-reason" class="form-label mt-3">เหตุผลการคืนยอด</label>
        <?= Html::textarea('reason',$draft['reason'] ?: 'คืนยอดยกไปตาม Excel ฉบับที่ส่งบัญชี โดยยึดจำนวนและมูลค่าปลายงวดตามต้นฉบับ',['id'=>'restore-reason','class'=>'form-control','required'=>true,'minlength'=>10,'rows'=>2]) ?>
        <button type="submit" class="btn btn-primary mt-3">บันทึกการจับคู่และตรวจตัวอย่าง</button>
        <?= Html::endForm() ?>
        <?php if ($plan && !$plan['errors'] && isset($plan['hash'])): ?>
            <div class="border rounded-3 p-3 mt-4"><h2 class="h5">ตัวอย่างก่อนคืนยอด</h2>
                <p><?= count($plan['targets']) ?> รายการ · <?= count($plan['warehouses']) ?> คลัง · ยอดยกไป <?= $money($plan['source_total']) ?> บาท · ยกเลิก snapshot งวดถัดไป <?= count($plan['later']) ?> แถว</p>
                <div class="row g-3 mb-3"><div class="col-12 col-lg-6"><h3 class="h6">ยอดรับรองแยกประเภท</h3><ul>
                    <?php foreach ($plan['by_category'] as $name=>$value): ?><li><?= Html::encode($name) ?>: <?= $money($value) ?> บาท</li><?php endforeach; ?>
                </ul></div><div class="col-12 col-lg-6"><h3 class="h6">ยอดรับรองแยกคลัง</h3><ul>
                    <?php foreach ($plan['by_warehouse'] as $wid=>$value): ?><li><?= Html::encode($result['warehouses'][$wid]) ?>: <?= $money($value) ?> บาท</li><?php endforeach; ?>
                </ul></div></div>
                <div class="table-responsive"><table class="table"><thead><tr><th>คลัง / รหัส</th><th class="text-end">จำนวนเดิม → รับรอง</th><th class="text-end">มูลค่าเดิม → รับรอง</th></tr></thead><tbody>
                    <?php foreach ($plan['targets'] as $t): ?><?php if ($t['old_qty']==$t['closing_qty'] && $t['old_value']==$t['closing_value']) continue; ?><tr><td><?= Html::encode($result['warehouses'][$t['warehouse_id']].' · '.$t['item_code']) ?></td><td class="text-end"><?= $money($t['old_qty']) ?> → <?= $money($t['closing_qty']) ?></td><td class="text-end"><?= $money($t['old_value']) ?> → <?= $money($t['closing_value']) ?></td></tr><?php endforeach; ?>
                </tbody></table></div>
                <?= Html::beginForm(['commit','id'=>$draft['id']],'post') ?>
                <?= Html::hiddenInput('hash',$plan['hash']) ?>
                <label class="d-block mb-3"><?= Html::checkbox('confirmed',false,['value'=>'1','required'=>true]) ?> ตรวจการจับคู่และยอดแล้ว รับรองยอดยกไปตาม Excel และล็อกงวดนี้</label>
                <button class="btn btn-success" type="submit">คืนยอดและล็อกงวด</button>
                <?= Html::endForm() ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($events): ?><h2 class="h5 mt-4">ประวัติ</h2><ul><?php foreach ($events as $e): ?><li><?= Html::encode($e['created_at'].' · '.$e['action'].' · ผู้ใช้ '.$e['created_by'].' · '.$e['reason']) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($draft['status']==='committed'): ?>
        <?= Html::a('ดูรายงานงวดที่รับรอง',['/inventory-v2/report/material-summary','year'=>$draft['report_year'],'month'=>$draft['report_month']],['class'=>'btn btn-primary']) ?>
        <details class="mt-4"><summary>ย้อนคืนชุดข้อมูลก่อนนำเข้า</summary><p>ทำได้เมื่อไม่มีการเปลี่ยนแปลงหรือปิดงวดใหม่หลังการคืนยอด ระบบจะนำ snapshot เดิมและงวดถัดไปที่สำรองไว้กลับคืน</p>
            <?= Html::beginForm(['revert','id'=>$draft['id']],'post') ?>
            <label for="revert-reason">เหตุผลการย้อนคืน</label><?= Html::textarea('reason','',['id'=>'revert-reason','class'=>'form-control','required'=>true,'minlength'=>10,'rows'=>2]) ?>
            <button class="btn btn-outline-danger mt-2" type="submit">ย้อนคืนและบันทึกประวัติ</button><?= Html::endForm() ?>
        </details>
    <?php endif; ?>
</div>
<?php $this->registerJs(<<<'JS'
document.getElementById('show-resolved-rows')?.addEventListener('change', function () {
    document.querySelectorAll('[data-resolved="1"]').forEach(row => { row.hidden = !this.checked; });
});
JS, \yii\web\View::POS_END
); ?>
