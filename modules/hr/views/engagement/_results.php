<?php
use yii\helpers\Html;
use yii\helpers\Json;
use app\modules\hr\services\EngagementService as E;
$this->registerJsFile('@web/js/hr-engagement.js',['depends'=>[\app\assets\AppAsset::class]]);
$dimensions=[]; $overall=null;
foreach($report['metrics'] as $m) { if($m['kind']==='overall') $overall=$m; else $dimensions[]=$m; }
$values=array_map(static fn($m)=>$m['status']==='ok'?(float)$m['mean_score']:null,$dimensions);
$graph=['labels'=>array_column($dimensions,'dimension_title'),'values'=>$values,'trendLabels'=>array_column($trend??[],'title'),'trendValues'=>array_map(static fn($r)=>$r['status']==='ok'?(float)$r['index_score']:null,$trend??[])];
$display=static fn($value)=>$value===null?'—':number_format((float)$value,1);
?>
<section class="card border-0 shadow-sm rounded-4" data-engagement-results="<?= Html::encode(Json::encode($graph)) ?>" aria-label="ผลสำรวจความผูกพัน">
<div class="card-body p-3 p-md-4"><h3 class="h5"><?= Html::encode($report['round']['title']) ?></h3>
<p class="small text-body-secondary"><?= E::localDate($report['round']['open_at']) ?> – <?= E::localDate($report['round']['close_at']) ?> · ปีงบประมาณ <?= (int)$report['round']['fiscal_year'] ?> · ภาพรวมองค์กร</p>
<?php if($overall && $overall['status']==='ok'): ?>
<div class="row g-3 mb-4"><div class="col-12 col-md-6"><span class="text-body-secondary">ดัชนีความผูกพัน (0–100)</span><p class="fs-3 fw-bold text-primary-emphasis mb-1"><?= $display($overall['index_score']) ?></p><p class="small text-body-secondary mb-0">คำนวณจากด้านผลความผูกพัน · ผู้ตอบที่ใช้คำนวณ <?= (int)$overall['valid_n'] ?> คน</p></div><div class="col-12 col-md-6"><span class="text-body-secondary">อัตราตอบกลับ</span><p class="fs-3 fw-bold mb-1"><?= $overall['eligible_n']?number_format($overall['respondent_n']*100/$overall['eligible_n'],1).'%' : '—' ?></p><p class="small text-body-secondary mb-0">ส่งแล้ว <?= (int)$overall['respondent_n'] ?> / ผู้มีสิทธิ์ <?= (int)$overall['eligible_n'] ?> คน</p></div></div>
<?php else: ?><p class="alert alert-secondary">ยังแสดงคะแนนรวมไม่ได้: <?= $overall?E::statusLabel($overall['status']):'ยังไม่มีผลที่เผยแพร่' ?> ไม่ใช้เลขศูนย์แทนข้อมูลที่แสดงไม่ได้</p><?php endif ?>
<?php if(array_filter($values,static fn($v)=>$v!==null)): ?><h4 class="h6">คะแนนรายด้าน (1–5)</h4><div data-engagement-chart="dimensions" role="img" aria-label="กราฟคะแนนเฉลี่ยรายด้าน มีรายละเอียดในตารางด้านล่าง" hidden></div><?php endif ?>
<details open data-engagement-table><summary class="py-2 text-primary-emphasis">ดูคะแนนและจำนวนผู้ตอบรายด้าน</summary><div class="table-responsive"><table class="table align-middle"><caption>ดัชนี = (ค่าเฉลี่ย − 1) ÷ 4 × 100 · ผู้ตอบต้องตอบอย่างน้อย 80% ของข้อในด้านนั้น · N/A ไม่ลงตัวหาร</caption><thead><tr><th>ด้าน</th><th>ใช้คำนวณ (คน)</th><th>เฉลี่ย 1–5</th><th>คำตอบเชิงบวก</th><th>สถานะ</th></tr></thead><tbody>
<?php foreach($dimensions as $m): ?><tr><th scope="row" class="fw-normal"><?= Html::encode($m['dimension_title']) ?><small class="d-block text-body-secondary"><?= $m['kind']==='outcome'?'ผลความผูกพัน':'ปัจจัยสนับสนุน' ?></small></th><td><?= $m['valid_n']===null?'—':(int)$m['valid_n'] ?></td><td><?= $display($m['mean_score']) ?></td><td><?= $m['favorable_percent']===null?'—':$display($m['favorable_percent']).'%' ?></td><td><?= E::statusLabel($m['status']) ?></td></tr><?php endforeach ?>
</tbody></table></div></details>
<?php if(count($trend??[])>1): ?><h4 class="h6 mt-4">ดัชนีตามรอบสำรวจที่ใช้แบบและสูตรเดียวกัน</h4><div data-engagement-chart="trend" role="img" aria-label="ดัชนีตามรอบสำรวจ รายละเอียดในตารางแนวโน้ม" hidden></div><details><summary class="py-2">ตารางแนวโน้ม</summary><div class="table-responsive"><table class="table"><thead><tr><th>รอบ</th><th>ดัชนี</th></tr></thead><tbody><?php foreach($trend as $r): ?><tr><td><?= Html::encode($r['title']) ?></td><td><?= $r['status']==='ok'?$display($r['index_score']):E::statusLabel($r['status']) ?></td></tr><?php endforeach ?></tbody></table></div></details><p class="small text-body-secondary">ประชากรและอัตราตอบแต่ละรอบอาจต่างกัน ผลก่อน–หลังไม่ยืนยันเหตุและผล ช่วงที่ปกปิดไม่เชื่อมเป็นแนวโน้ม</p><?php else: ?><p class="small text-body-secondary mt-3 mb-0">แนวโน้มจะแสดงเมื่อมีอย่างน้อย 2 รอบที่ใช้แบบและสูตรเดียวกัน</p><?php endif ?>
</div></section>
