<?php
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Operate: turn the existing filtered workforce snapshot into HR follow-up.
 * Preserve ERP Bootstrap components and all existing query/permission boundaries.
 * These are decision aids, not HA scores or new clinical/HR classifications.
 * @var int|null $newHires
 * @var int|null $exits
 * @var array $exitReasons
 * @var string $periodText
 */
$hasMovement = isset($newHires, $exits);
$netMovement = $hasMovement ? (int) $newHires - (int) $exits : null;
$netText = $netMovement === null ? 'ยังไม่มีข้อมูล' : ($netMovement > 0 ? '+' : '') . number_format($netMovement) . ' คน';
$reasonLabels = [];
$reasonValues = [];
foreach ($exitReasons as $reason) {
    $value = max(0, (int) ($reason['count'] ?? 0));
    if ($value > 0) {
        $reasonLabels[] = (string) ($reason['reason'] ?? 'ไม่ระบุ');
        $reasonValues[] = $value;
    }
}
$chartData = [
    'movement' => $hasMovement ? [(int) $newHires, (int) $exits] : null,
    'reasonLabels' => $reasonLabels,
    'reasonValues' => $reasonValues,
];
$followUp = $netMovement === null
    ? 'ตรวจสอบข้อมูลวันบรรจุและวันพ้นจากหน่วยงานก่อนวิเคราะห์การเคลื่อนไหว'
    : ($netMovement < 0
        ? 'ทบทวนหน่วยงานที่รับเข้าไม่ทันการพ้นสภาพ แล้วเทียบภาระงานและกรอบอัตรากำลังก่อนจัดแผนทดแทน'
        : 'เทียบกำลังคนกับภาระงานและกรอบอัตรากำลังรายหน่วยงาน ก่อนสรุปว่าบุคลากรเพียงพอ');
$domains = [
    [
        'title' => 'ความเพียงพอและความต่อเนื่องของกำลังคน', 'ha' => 'I-5.1',
        'question' => 'หน่วยงานใดต้องเสริมคนหรือเตรียมผู้สืบทอด?',
        'available' => 'ดูจำนวนคน ตำแหน่ง อายุงาน และการเคลื่อนไหวตามตัวกรองได้ในหน้านี้',
        'missing' => 'ยังไม่เชื่อมกรอบอัตรากำลัง FTE ภาระงานรายเวร และวันเกษียณที่ยืนยันแล้ว',
        'action' => 'เทียบคนปฏิบัติงานกับกรอบที่อนุมัติและภาระงาน จัดลำดับแผนทดแทนตามความเสี่ยงของบริการ',
        'link' => ['/hr/organization/diagram'], 'linkLabel' => 'ดูผังองค์กร',
        'metric' => 'ช่องว่าง FTE = FTE ที่ต้องการ − FTE ที่ปฏิบัติงานจริง แยกหน่วยงานและวิชาชีพ; แสดงวันที่และรุ่นของกรอบที่ใช้',
    ],
    [
        'title' => 'สมรรถนะและการพัฒนาบุคลากร', 'ha' => 'I-5.1 / I-5.2',
        'question' => 'ใครต้องพัฒนาทักษะเพื่อให้บริการได้ตามบทบาท?',
        'available' => 'มีทะเบียนพัฒนาบุคลากรให้ตรวจสอบต่อ',
        'missing' => 'ยังไม่เชื่อมผลประเมินสมรรถนะตามบทบาทและความครบถ้วนของใบอนุญาตวิชาชีพ',
        'action' => 'จัดทำแผนพัฒนารายบุคคลจากช่องว่างสมรรถนะ และติดตามผลหลังนำความรู้ไปใช้',
        'link' => ['/hr/development/dashboard'], 'linkLabel' => 'ดูการพัฒนาบุคลากร',
        'metric' => 'ความครอบคลุมการประเมิน = ผู้ประเมินครบ ÷ ผู้ต้องประเมิน × 100; แยกอัตราผ่านเกณฑ์ออกจากจำนวนผู้เข้าอบรม',
    ],
    [
        'title' => 'สุขภาพ ความปลอดภัย และสุขภาวะ', 'ha' => 'I-5.1',
        'question' => 'มีความเสี่ยงจากงานที่ต้องติดตามหรือปรับระบบงานหรือไม่?',
        'available' => 'มี dashboard สุขภาพบุคลากรให้ตรวจสอบต่อ',
        'missing' => 'ยังไม่เชื่อมการตรวจสุขภาพตามความเสี่ยง เหตุสัมผัสอันตราย และชั่วโมงทำงานสะสม',
        'action' => 'ทบทวนความครอบคลุมการดูแลและการแก้ไขความเสี่ยงร่วมกับทีมอาชีวอนามัย',
        'link' => ['/hr/health/dashboard'], 'linkLabel' => 'ดูสุขภาพบุคลากร',
        'metric' => 'ความครอบคลุมตรวจสุขภาพ = ผู้ได้รับการตรวจตามความเสี่ยงครบ ÷ ผู้เข้าเกณฑ์ × 100; ติดตามเหตุจากงานและการปิดแผนแก้ไขแยกกัน',
    ],
    [
        'title' => 'ความผูกพันและการรักษาบุคลากร', 'ha' => 'I-5.2',
        'question' => 'เหตุใดคนจึงออก และองค์กรควรปรับปรุงเรื่องใด?',
        'available' => 'มีระบบเก็บแบบสำรวจความผูกพันและผลรวมตามรอบ พร้อมดูเหตุผลการพ้นจากหน่วยงานประกอบ',
        'missing' => 'ต้องสร้างแบบและเปิดรอบสำรวจก่อนมีคะแนน ส่วนอัตราลาออกยังต้องเตรียมจำนวนคนเฉลี่ยและตรวจเหตุผลให้ครบ',
        'action' => 'แยกลาออกโดยสมัครใจจากเกษียณหรือย้าย และติดตามแผนปรับปรุงจากผลสำรวจแบบภาพรวม',
        'link' => ['/hr/engagement/mine'], 'linkLabel' => 'เปิดระบบความผูกพัน',
        'metric' => 'อัตราลาออกโดยสมัครใจ = ผู้ลาออกโดยสมัครใจในช่วง ÷ จำนวนบุคลากรเฉลี่ยในช่วงเดียวกัน × 100; แสดงวิธีหาค่าเฉลี่ยและอัตราตอบกลับของผลสำรวจ',
    ],
];
?>
<section class="mb-4" aria-labelledby="hr-action-title" data-hr-workforce-charts="<?= Html::encode(Json::encode($chartData)) ?>">
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
        <h2 id="hr-action-title" class="h5 fw-semibold mb-0">ประเด็นเพื่อการตัดสินใจของ HR</h2>
        <a href="#hr-ha-follow-up" class="btn btn-outline-primary text-primary-emphasis btn-sm">ดูประเด็นติดตาม HA</a>
    </div>
    <div class="card border-0 shadow-sm rounded-4 bg-body mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <h3 class="h6 fw-semibold">เปรียบเทียบบรรจุใหม่และพ้นจากหน่วยงาน</h3>
                    <p class="small text-body-secondary mb-2"><?= Html::encode($periodText) ?> · ตามตัวกรองที่เลือก</p>
                    <?php if ($hasMovement): ?>
                        <div data-hr-chart="movement" role="img" aria-label="<?= Html::encode('บรรจุใหม่ ' . (int) $newHires . ' คน พ้นจากหน่วยงาน ' . (int) $exits . ' คน') ?>" hidden></div>
                        <p class="small text-body-secondary" data-hr-chart-fallback>บรรจุใหม่ <?= number_format((int) $newHires) ?> คน · พ้นจากหน่วยงาน <?= number_format((int) $exits) ?> คน</p>
                    <?php endif; ?>
                    <p class="mb-2">ส่วนต่างรับเข้า − พ้นสภาพ <strong class="text-primary-emphasis"><?= Html::encode($netText) ?></strong></p>
                    <p class="mb-2"><?= Html::encode($followUp) ?></p>
                    <details class="small text-body-secondary">
                        <summary class="py-2">วิธีอ่านตัวเลขและข้อจำกัด</summary>
                        <p class="mb-0">บรรจุใหม่ลบจำนวนพ้นจากหน่วยงานในช่วงปีงบประมาณที่เลือก ตามวันที่ในทะเบียน ไม่ใช่การเปลี่ยนแปลงยอดคงเหลือต้นปี–ปลายปี หรืออัตราลาออก และไม่บอกความเพียงพอของอัตรากำลัง ข้อมูลที่ยังไม่บันทึกอาจทำให้ตัวเลขต่ำกว่าความจริง</p>
                    </details>
                </div>
                <div class="col-12 col-lg-6">
                    <h3 class="h6 fw-semibold">สัดส่วนเหตุผลการพ้นจากหน่วยงาน</h3>
                    <p class="small text-body-secondary mb-2">เทียบกับรายการแยกเหตุผล <?= number_format(array_sum($reasonValues)) ?> คนในช่วงและตัวกรองเดียวกัน</p>
                    <?php if ($reasonValues): ?>
                        <div data-hr-chart="reasons" role="img" aria-label="สัดส่วนเหตุผลการพ้นจากหน่วยงาน ดูจำนวนรายเหตุผลในตารางด้านล่าง" hidden></div>
                    <?php endif; ?>
                    <?php if ($exitReasons): ?>
                        <details data-hr-reason-table open>
                            <summary class="small py-2 text-primary-emphasis">ดูจำนวนแยกตามเหตุผล</summary>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <caption class="small">จำนวนตามเหตุผลในทะเบียน รวมการพ้นสภาพทุกประเภท ไม่ใช่เฉพาะลาออก</caption>
                                <thead><tr><th scope="col">เหตุผล</th><th scope="col" class="text-end">จำนวน (คน)</th></tr></thead>
                                <tbody>
                                <?php foreach ($exitReasons as $reason): ?>
                                    <tr><th scope="row" class="fw-normal"><?= Html::encode($reason['reason'] ?? 'ไม่ระบุ') ?></th><td class="text-end"><?= number_format((int) ($reason['count'] ?? 0)) ?></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        </details>
                    <?php else: ?>
                        <p class="text-body-secondary mb-0">ยังไม่มีรายการแยกเหตุผลในช่วงและตัวกรองนี้ ตรวจสอบทะเบียนและวันพ้นสภาพก่อนสรุปว่าไม่มีการพ้นจากหน่วยงาน</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div id="hr-ha-follow-up" class="card border-0 shadow-sm rounded-4 bg-body">
        <div class="card-body p-3 p-md-4">
            <h3 class="h6 fw-semibold">ประเด็นติดตามตาม HA ฉบับที่ 6</h3>
            <p class="small text-body-secondary">เชื่อมงานบุคลากร I-5 กับผลลัพธ์ IV-3 · เป็นแนวทางเลือกตัวชี้วัด ไม่ใช่คะแนนรับรอง HA หรือข้อกำหนดค่าเป้าหมาย</p>
            <?php foreach ($domains as $domain): ?>
                <details class="border-top py-2">
                    <summary class="py-2 fw-semibold">
                        <?= Html::encode($domain['title']) ?>
                        <span class="badge bg-primary-subtle text-primary-emphasis ms-1"><?= Html::encode($domain['ha']) ?></span>
                    </summary>
                    <div class="row g-3 pt-2 pb-3">
                        <div class="col-12 col-lg-5">
                            <p class="fw-semibold mb-2"><?= Html::encode($domain['question']) ?></p>
                            <p class="small mb-2"><?= Html::encode($domain['available']) ?></p>
                            <?php if ($domain['link']): ?>
                                <?= Html::a(Html::encode($domain['linkLabel']), $domain['link'], ['class' => 'btn btn-outline-primary text-primary-emphasis btn-sm', 'data-pjax' => '0']) ?>
                                <p class="small text-body-secondary mt-2 mb-0">หน้าแหล่งข้อมูลใช้สิทธิ์และตัวกรองของหน้านั้น</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-7">
                            <p class="small text-body-secondary mb-2"><strong>ข้อมูลที่ต้องเชื่อมเพิ่ม:</strong> <?= Html::encode($domain['missing']) ?></p>
                            <p class="small mb-2"><strong>งานที่ควรทำต่อ:</strong> <?= Html::encode($domain['action']) ?></p>
                            <p class="small mb-0"><strong>ตัวชี้วัดเสนอใช้:</strong> <?= Html::encode($domain['metric']) ?></p>
                        </div>
                    </div>
                </details>
            <?php endforeach; ?>
            <p class="small text-body-secondary border-top pt-3 mb-0">HR และทีมคุณภาพควรกำหนดเจ้าของข้อมูล รอบทบทวน และเป้าหมายตามบริบทโรงพยาบาล พร้อมบันทึกผลการปรับปรุง แสดง “ยังไม่มีข้อมูล” เมื่อข้อมูลไม่ครบหรือตัวหารเป็นศูนย์
                <?= Html::a('อ้างอิงแบบประเมิน SAR 2026 ของ สรพ.', 'https://backend.ha.or.th/fileupload/DOCUMENT/00183/280fa7c4-8100-433d-b114-c78372802bf2.pdf', ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
            </p>
        </div>
    </div>
</section>
