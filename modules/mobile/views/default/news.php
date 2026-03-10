<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'news';
$this->params['mobileTitle']    = 'ข่าวสาร';
$this->params['mobileSubtitle'] = 'ประกาศและอัปเดต';

$newsItems = [
    ['id' => 1, 'badge' => 'ข่าวประชาสัมพันธ์', 'badgeClass' => 'primary', 'date' => '10 มี.ค. 2568', 'title' => 'ประชุมใหญ่ประจำปี 2568', 'desc' => 'ขอเชิญพนักงานทุกท่านเข้าร่วมประชุมใหญ่ประจำปี ในวันศุกร์ที่ 15 มีนาคม 2568 ณ ห้องประชุมใหญ่ ชั้น 3'],
    ['id' => 2, 'badge' => 'ประกาศ', 'badgeClass' => 'info', 'date' => '8 มี.ค. 2568', 'title' => 'ระบบขอลาออนไลน์เปิดให้บริการแล้ว', 'desc' => 'สามารถส่งคำขอลาผ่านแอปได้ทันที และติดตามสถานะการอนุมัติได้แบบเรียลไทม์'],
    ['id' => 3, 'badge' => 'อัปเดต', 'badgeClass' => 'success', 'date' => '5 มี.ค. 2568', 'title' => 'ปรับปรุงหน้าจองห้องประชุม', 'desc' => 'เพิ่มการแสดงผลปฏิทินและห้องว่างแบบรายชั่วโมง ให้จองได้สะดวกขึ้น'],
    ['id' => 4, 'badge' => 'แจ้งซ่อม', 'badgeClass' => 'warning', 'date' => '3 มี.ค. 2568', 'title' => 'เปิดบริการแจ้งซ่อมผ่านแอปมือถือ', 'desc' => 'สามารถแจ้งซ่อมอุปกรณ์หรือสถานที่ พร้อมแนบรูปและสแกน QR ครุภัณฑ์ได้จากมือถือ'],
    ['id' => 5, 'badge' => 'นโยบาย', 'badgeClass' => 'secondary', 'date' => '1 มี.ค. 2568', 'title' => 'มาตรการประหยัดพลังงาน เดือน มี.ค. – เม.ย. 2568', 'desc' => 'ขอความร่วมมือปิดเครื่องปรับอากาศและไฟเมื่อไม่ใช้งาน ในช่วง 12.00–13.00 น.'],
];
?>
<div class="d-flex flex-column gap-3">
    <p class="small text-body-secondary mb-0">รายการข่าวสารและประกาศ — ตัวอย่างการแสดงผล</p>
    <?php foreach ($newsItems as $n): ?>
        <a href="<?= Html::encode(Url::to(['/mobile/default/news-view', 'id' => $n['id']])) ?>" class="card mobile-card text-decoration-none text-dark">
            <div class="card-body">
                <div class="d-flex gap-2 mb-2">
                    <span class="badge bg-<?= $n['badgeClass'] ?> bg-opacity-10 text-<?= $n['badgeClass'] ?> border border-<?= $n['badgeClass'] ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($n['badge']) ?></span>
                    <span class="small text-body-secondary"><?= Html::encode($n['date']) ?></span>
                </div>
                <h6 class="card-title fw-semibold mb-2"><?= Html::encode($n['title']) ?></h6>
                <p class="card-text small text-body-secondary mb-0"><?= Html::encode($n['desc']) ?></p>
            </div>
        </a>
    <?php endforeach; ?>
    <div class="card mobile-card mb-2">
        <div class="card-body text-center py-4">
            <i data-lucide="inbox" class="text-body-secondary mb-2" style="width: 2.5rem; height: 2.5rem;"></i>
            <p class="small text-body-secondary mb-0">ไม่มีข่าวสารเพิ่มเติมในขณะนี้</p>
        </div>
    </div>
</div>
