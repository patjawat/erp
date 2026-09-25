<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var string $version
 * @var array $dockerConfig ['image' => string, 'composePath' => string|null, 'serviceName' => string]
 * @var string $appImage APP_IMAGE จาก .env (ว่าง = ไม่ทราบ)
 * @var string $appBuild เลข build จาก Jenkins (ว่าง = build นอก Jenkins)
 */

// ช่องทางอัปเดตจาก tag ของ image: stable = เวอร์ชันที่ปล่อยแล้ว, latest = รุ่นทดสอบทุก build, vX.Y.Z = ตรึงเวอร์ชัน
$imageTag = $appImage === '' ? '' : (strpos($appImage, ':') !== false ? substr($appImage, strrpos($appImage, ':') + 1) : 'latest');
$channel = match (true) {
    $imageTag === '' => null,
    $imageTag === 'stable' => ['ช่องทาง stable', 'เวอร์ชันที่ปล่อยแล้วเท่านั้น', 'bg-success-subtle text-success-emphasis'],
    $imageTag === 'latest' => ['ช่องทางทดสอบ (latest)', 'ได้ทุก build ก่อนปล่อยเวอร์ชัน', 'bg-warning-subtle text-warning-emphasis'],
    default => ['ตรึงเวอร์ชัน ' . $imageTag, 'จะไม่ได้เวอร์ชันใหม่จนกว่าจะแก้ APP_IMAGE', 'bg-secondary-subtle text-secondary-emphasis'],
};
$canRunDocker = !empty($dockerConfig['composePath']) && is_dir($dockerConfig['composePath']);
$dockerService = $dockerConfig['serviceName'] ?? 'app';
$isAdmin = Yii::$app->user->can('admin');

// ดูจาก DB_DSN ว่าใช้ฐานในชุด Docker (host=mysqlDB) หรือฐานของโรงพยาบาลเอง เพื่อเปิดแท็บขั้นตอนให้ตรง
$dsn = (string) Yii::$app->db->dsn;
$dbHost = preg_match('/host=([^;]+)/i', $dsn, $m) ? $m[1] : '';
$dbName = preg_match('/dbname=([^;]+)/i', $dsn, $m) ? $m[1] : '';
$isBundledDb = strcasecmp($dbHost, 'mysqlDB') === 0;
$dbNameCmd = $dbName !== '' ? $dbName : 'ชื่อฐาน';

$this->title = 'อัปเดตระบบ';
$this->params['breadcrumbs'][] = $this->title;

/** กล่องคำสั่งพร้อมปุ่มคัดลอก */
$cmd = static function (string $text): string {
    return '<div class="update-cmd position-relative mt-1">'
        . '<pre class="bg-body-tertiary border rounded small mb-0 py-2 ps-3 pe-5" style="white-space: pre-wrap; word-break: break-all;"><code>' . Html::encode($text) . '</code></pre>'
        . '<button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-1 py-0 px-2 btn-copy-cmd" title="คัดลอกคำสั่ง"><i class="bi bi-clipboard"></i></button>'
        . '</div>';
};
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-arrow-repeat fs-4 me-2"></i><?= $this->title ?>
<?php $this->endBlock(); ?>

<div class="container">
    <div class="row g-3">

        <!-- เวอร์ชัน + ปุ่ม -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div>
                            <div class="text-muted small">เวอร์ชันที่ติดตั้งอยู่ขณะนี้</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill fs-6 fw-semibold px-3 py-2"><?= Html::encode($version) ?></span>
                                <?php if ($appBuild !== ''): ?>
                                    <span class="small text-muted">build #<?= Html::encode($appBuild) ?></span>
                                <?php endif; ?>
                                <?php if ($channel): ?>
                                    <span class="badge <?= $channel[2] ?> rounded-pill fw-medium" title="<?= Html::encode($channel[1]) ?>"><?= Html::encode($channel[0]) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="small mt-2" id="latest-release-status"></div>
                        </div>
                        <?php if ($isAdmin): ?>
                            <button type="button" class="btn btn-primary ms-auto" id="btn-update-version">
                                <?php if ($canRunDocker): ?>
                                    <i class="bi bi-cloud-download me-1"></i> อัปเดตเป็นเวอร์ชันล่าสุด
                                <?php else: ?>
                                    <i class="bi bi-database-gear me-1"></i> ปรับฐานข้อมูล (Migration)
                                <?php endif; ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!$canRunDocker): ?>
                        <div class="alert alert-warning small mt-3 mb-0">
                            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>ปุ่มบนหน้านี้ไม่ได้ดาวน์โหลดเวอร์ชันใหม่</div>
                            เว็บรันอยู่ใน container ของเวอร์ชันปัจจุบัน จึงเปลี่ยนตัวเองเป็นเวอร์ชันใหม่ไม่ได้
                            ให้อัปเดต <strong>บนเครื่องเซิร์ฟเวอร์</strong> ด้วยคำสั่ง <code>./update.sh</code> ด้านล่าง (สำรองฐาน + ดึงเวอร์ชันใหม่ + migration ให้ครบในคำสั่งเดียว)
                            ปุ่มด้านบนใช้รัน migration ซ้ำเท่านั้น
                            <div class="mt-1 text-body-secondary">เช็คผลง่าย ๆ: หลังอัปเดตแล้วเลขเวอร์ชันด้านบนต้องเปลี่ยน ถ้ายังเป็นเลขเดิมแปลว่ายังไม่ได้ของใหม่</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- อัปเดตด้วยคำสั่งเดียว -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3 px-3 d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-terminal me-2"></i>อัปเดตด้วยคำสั่งเดียว (แนะนำ)</h6>
                    <?php if ($isAdmin): ?>
                        <?= Html::a('<i class="bi bi-download me-1"></i> ดาวน์โหลด update.sh', ['download-script'], ['class' => 'btn btn-sm btn-outline-secondary ms-auto', 'data-pjax' => 0]) ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        เปิด Terminal/SSH บนเครื่องเซิร์ฟเวอร์ แล้ว <code>cd</code> เข้าโฟลเดอร์ที่มีไฟล์ <code>docker-compose.yml</code> และ <code>.env</code> ของระบบ ERP
                        สคริปต์ใช้ได้ทั้งฐานข้อมูลในชุด Docker และฐานข้อมูลของโรงพยาบาลเอง
                    </p>
                    <ol class="small ps-3 mb-3">
                        <li class="mb-3">
                            <strong>ครั้งแรกครั้งเดียว: ดึงสคริปต์ออกจาก image</strong>
                            <?= $cmd('docker run --rm --entrypoint cat patjawat/erp:stable /app/scripts/update.sh > update.sh && chmod +x update.sh') ?>
                        </li>
                        <li class="mb-3">
                            <strong>ทุกครั้งที่จะอัปเดต</strong>
                            <?= $cmd('./update.sh') ?>
                            <div class="text-muted mt-1">
                                สคริปต์จะ สำรองฐานข้อมูลลงโฟลเดอร์ <code>backups/</code> (เก็บ 7 ชุดล่าสุด) → ดึงเวอร์ชันใหม่ตาม <code>APP_IMAGE</code> ใน <code>.env</code> → เปลี่ยน container → migration → แสดงเลขเวอร์ชันก่อน/หลัง
                                ถ้าใช้ไฟล์ compose ชื่ออื่นให้สั่ง <code>./update.sh -f docker-compose-nginx.yml</code>
                            </div>
                        </li>
                        <li>
                            <strong>ไม่บังคับ: ให้อัปเดตเองทุกคืนตอนตี 3</strong> (<code>crontab -e</code> แล้ววางบรรทัดนี้ แก้ path ให้ตรงเครื่อง)
                            <?= $cmd('0 3 * * * cd /path/to/run-production && ./update.sh -y >> update.log 2>&1') ?>
                        </li>
                    </ol>
                    <div class="border rounded p-3 bg-body-tertiary small">
                        <div class="fw-semibold mb-2"><i class="bi bi-signpost-split me-1"></i>ช่องทางอัปเดต (ตั้งที่ <code>APP_IMAGE</code> ในไฟล์ <code>.env</code>)</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-2 align-middle">
                                <tbody>
                                    <tr>
                                        <td class="text-nowrap"><code>APP_IMAGE=patjawat/erp:stable</code></td>
                                        <td><span class="badge bg-success-subtle text-success-emphasis rounded-pill">แนะนำ</span> ได้เฉพาะเวอร์ชันที่ทดสอบและปล่อยแล้ว</td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap"><code>APP_IMAGE=patjawat/erp:v1.28.0</code></td>
                                        <td>ตรึงไว้ที่เวอร์ชันนั้น ใช้ถอยกลับเมื่อเวอร์ชันใหม่มีปัญหา</td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap"><code>APP_IMAGE=patjawat/erp:latest</code></td>
                                        <td>รุ่นทดสอบ ได้ทุก build ก่อนปล่อยเวอร์ชัน (เฉพาะโรงพยาบาลที่ร่วมทดสอบ)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-muted">แก้ <code>.env</code> แล้วรัน <code>./update.sh</code> อีกครั้งเพื่อสลับช่องทาง ถ้ายังใช้ <code>:latest</code> อยู่ สคริปต์จะถามให้เปลี่ยนเป็น <code>:stable</code> ให้</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ขั้นตอนอัปเดตทีละขั้น -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3 px-3">
                    <h6 class="mb-2 fw-semibold"><i class="bi bi-list-ol me-2"></i>หรือทำทีละขั้นเอง</h6>
                    <?php if ($isAdmin && $dbHost !== ''): ?>
                        <div class="small text-muted mb-2">
                            เครื่องนี้ต่อฐานข้อมูลที่ <code><?= Html::encode($dbHost) ?></code>
                            ฐาน <code><?= Html::encode($dbName ?: '-') ?></code>
                            <span class="badge <?= $isBundledDb ? 'bg-success-subtle text-success-emphasis' : 'bg-info-subtle text-info-emphasis' ?> rounded-pill ms-1">
                                <?= $isBundledDb ? 'ฐานข้อมูลในชุด Docker' : 'ฐานข้อมูลของโรงพยาบาลเอง' ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <ul class="nav nav-pills gap-2 update-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill <?= $isBundledDb ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tab-bundled" type="button" role="tab">
                                ฐานข้อมูลในชุด Docker
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill <?= $isBundledDb ? '' : 'active' ?>" data-bs-toggle="pill" data-bs-target="#tab-owndb" type="button" role="tab">
                                ฐานข้อมูลของโรงพยาบาลเอง
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        เปิด Terminal/SSH บนเครื่องเซิร์ฟเวอร์ แล้ว <code>cd</code> เข้าโฟลเดอร์ที่มีไฟล์ <code>docker-compose.yml</code> ของระบบ ERP (เช่น <code>run-production</code>) ก่อนรันทุกคำสั่ง
                        ถ้าใช้ไฟล์ compose ชื่ออื่น เช่น <code>docker-compose-nginx.yml</code> ให้เติม <code>-f docker-compose-nginx.yml</code> หลังคำว่า <code>docker compose</code>
                    </p>

                    <div class="tab-content">
                        <!-- แบบ A: ฐานข้อมูลในชุด -->
                        <div class="tab-pane fade <?= $isBundledDb ? 'show active' : '' ?>" id="tab-bundled" role="tabpanel">
                            <p class="small mb-3">ใช้กรณีฐานข้อมูล MySQL รันใน container <code>mysqlDB</code> ที่มากับชุดติดตั้ง (ใน <code>.env</code> เป็น <code>DB_DSN=mysql:host=mysqlDB;...</code>)</p>
                            <ol class="small ps-3 mb-0">
                                <li class="mb-3">
                                    <strong>สำรองฐานข้อมูล</strong> (แนะนำทุกครั้ง)
                                    <?= $cmd('docker compose exec -T mysqlDB mysqldump -uroot -p<รหัส root> --single-transaction --routines --triggers ' . $dbNameCmd . ' > erp_backup_$(date +%Y%m%d).sql') ?>
                                </li>
                                <li class="mb-3">
                                    <strong>ดึงเวอร์ชันล่าสุด</strong>
                                    <?= $cmd('docker compose pull ' . $dockerService) ?>
                                </li>
                                <li class="mb-3">
                                    <strong>เปลี่ยน container แอปเป็นเวอร์ชันใหม่</strong> (ไม่แตะฐานข้อมูล ข้อมูลไม่หาย)
                                    <?= $cmd('docker compose up -d --no-deps --force-recreate ' . $dockerService) ?>
                                </li>
                                <li class="mb-3">
                                    <strong>ปรับโครงสร้างฐานข้อมูล (migration)</strong> ห้ามข้าม หรือจะกดปุ่ม "ปรับฐานข้อมูล" บนหน้านี้แทนก็ได้
                                    <?= $cmd('docker compose exec ' . $dockerService . ' php yii migrate --interactive=0') ?>
                                </li>
                                <li>
                                    <strong>ตรวจผล</strong> โหลดหน้านี้ใหม่ เลขเวอร์ชันต้องเปลี่ยน และคำสั่งนี้ต้องตอบว่า <em>No new migrations found</em>
                                    <?= $cmd('docker compose exec ' . $dockerService . ' php yii migrate/new') ?>
                                </li>
                            </ol>
                        </div>

                        <!-- แบบ B: ฐานข้อมูลของโรงพยาบาลเอง -->
                        <div class="tab-pane fade <?= $isBundledDb ? '' : 'show active' ?>" id="tab-owndb" role="tabpanel">
                            <p class="small mb-2">
                                ใช้กรณีโรงพยาบาลมี MySQL/MariaDB ของตัวเอง (เครื่องอื่น หรือติดตั้งบนเครื่องเดียวกันแต่ไม่ได้อยู่ใน Docker)
                                ขั้นตอนคล้ายแบบแรก <strong>ต่างกันที่ต้องสำรองฐานจากเครื่องฐานข้อมูลเอง และ user ฐานข้อมูลต้องมีสิทธิ์แก้โครงสร้างตาราง</strong>
                            </p>
                            <div class="border rounded p-3 mb-3 bg-body-tertiary small">
                                <div class="fw-semibold mb-2"><i class="bi bi-check2-square me-1"></i>ตรวจครั้งแรกครั้งเดียว</div>
                                <ol class="ps-3 mb-0">
                                    <li class="mb-2">
                                        ไฟล์ <code>.env</code> ต้องชี้ไปฐานของโรงพยาบาล (ไม่ใช่ <code>mysqlDB</code>)
                                        <?= $cmd("DB_DSN=mysql:host=<IP เครื่องฐานข้อมูล>;port=3306;dbname=" . $dbNameCmd . "\nDB_USERNAME=<user>\nDB_PASS=<รหัสผ่าน>") ?>
                                        <div class="text-muted mt-1">ถ้าฐานอยู่บนเครื่องเดียวกับ Docker <strong>ห้ามใช้ <code>localhost</code> หรือ <code>127.0.0.1</code></strong> (ใน container จะหมายถึงตัว container เอง) ให้ใช้ IP ของเครื่อง หรือ <code>host.docker.internal</code> และ MySQL ต้องเปิดรับการเชื่อมต่อจาก IP นั้น (<code>bind-address</code>)</div>
                                    </li>
                                    <li class="mb-2">
                                        user ในฐานต้องมีสิทธิ์สร้าง/แก้ตาราง ไม่อย่างนั้น migration จะขึ้น <em>Access denied</em> หรือ <em>command denied</em> (รันบน MySQL ด้วยบัญชี root)
                                        <?= $cmd("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES,\n      CREATE VIEW, SHOW VIEW, TRIGGER, LOCK TABLES\n  ON " . $dbNameCmd . ".* TO '<user>'@'%';\nFLUSH PRIVILEGES;") ?>
                                    </li>
                                    <li>
                                        ฐานควรเป็น <code>utf8mb4</code> (ระบบเชื่อมต่อด้วย utf8mb4) และถ้าไม่ได้ใช้ฐานในชุด ลบ service <code>mysqlDB</code> กับ <code>phpmyadmin_mysql</code> ออกจาก <code>docker-compose.yml</code> ได้
                                    </li>
                                </ol>
                            </div>
                            <div class="fw-semibold small mb-2"><i class="bi bi-arrow-repeat me-1"></i>ทุกครั้งที่อัปเดต</div>
                            <ol class="small ps-3 mb-0">
                                <li class="mb-3">
                                    <strong>สำรองฐานข้อมูล</strong> (สำคัญมาก เพราะ migration แก้ตารางในฐานจริงของโรงพยาบาล) รันบนเครื่องที่มีคำสั่ง <code>mysqldump</code>
                                    <?= $cmd('mysqldump -h <IP เครื่องฐานข้อมูล> -u <user> -p --single-transaction --routines --triggers ' . $dbNameCmd . ' > erp_backup_$(date +%Y%m%d).sql') ?>
                                </li>
                                <li class="mb-3">
                                    <strong>ดึงเวอร์ชันล่าสุด</strong>
                                    <?= $cmd('docker compose pull ' . $dockerService) ?>
                                </li>
                                <li class="mb-3">
                                    <strong>เปลี่ยน container แอปเป็นเวอร์ชันใหม่</strong>
                                    <?= $cmd('docker compose up -d --no-deps --force-recreate ' . $dockerService) ?>
                                </li>
                                <li class="mb-3">
                                    <strong>ปรับโครงสร้างฐานข้อมูล (migration)</strong> คำสั่งนี้รันใน container แอป แต่แก้ฐานที่ <code>DB_DSN</code> ชี้อยู่ (ฐานของโรงพยาบาล)
                                    <?= $cmd('docker compose exec ' . $dockerService . ' php yii migrate --interactive=0') ?>
                                </li>
                                <li>
                                    <strong>ตรวจผล</strong> โหลดหน้านี้ใหม่ เลขเวอร์ชันต้องเปลี่ยน และคำสั่งนี้ต้องตอบว่า <em>No new migrations found</em>
                                    <?= $cmd('docker compose exec ' . $dockerService . ' php yii migrate/new') ?>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ปัญหาที่พบบ่อย -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3 px-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-question-circle me-2"></i>ปัญหาที่พบบ่อย</h6>
                </div>
                <div class="card-body small">
                    <dl class="mb-0">
                        <dt>กดปุ่มแล้วเลขเวอร์ชันไม่เปลี่ยน</dt>
                        <dd class="text-muted">ยังไม่ได้รัน <code>./update.sh</code> บนเซิร์ฟเวอร์ (ปุ่มบนเว็บทำได้แค่ migration) หรือช่องทางที่ใช้อยู่ยังไม่มีเวอร์ชันใหม่ ตรวจ <code>APP_IMAGE</code> ใน <code>.env</code> ว่าไม่ได้ตรึงเลขเวอร์ชันเก่าไว้</dd>

                        <dt>migration ขึ้น Access denied / command denied</dt>
                        <dd class="text-muted">user ใน <code>.env</code> ไม่มีสิทธิ์ CREATE/ALTER/DROP ให้ GRANT สิทธิ์ตามแท็บ "ฐานข้อมูลของโรงพยาบาลเอง"</dd>

                        <dt>migration ขึ้น SQLSTATE[HY000] [2002] Connection refused / timed out</dt>
                        <dd class="text-muted">container แอปต่อฐานไม่ได้ ตรวจ <code>DB_DSN</code> (ห้ามใช้ localhost), firewall พอร์ต 3306 และ <code>bind-address</code> ของ MySQL</dd>

                        <dt>ขึ้น "No such service: app"</dt>
                        <dd class="text-muted">ไม่ได้อยู่ในโฟลเดอร์ที่มี <code>docker-compose.yml</code> หรือใช้ไฟล์ชื่ออื่น ให้เติม <code>-f &lt;ชื่อไฟล์&gt;</code> ถ้าเครื่องเก่าใช้คำสั่ง <code>docker-compose</code> (มีขีด) แทน <code>docker compose</code> ได้</dd>

                        <dt>ข้อมูลหายไหม</dt>
                        <dd class="text-muted mb-0">ไม่หาย ฐานข้อมูลและไฟล์แนบ (volume <code>fileupload</code>) ไม่ถูกลบเมื่อเปลี่ยน container แต่ควรสำรองฐานก่อน migration ทุกครั้ง</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปการตั้งค่าระบบ', ['/settings/default/index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Modal แสดงสถานะการอัปเดต -->
<div class="modal fade" id="updateVersionModal" tabindex="-1" aria-labelledby="updateVersionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateVersionModalLabel">
                    <i class="bi bi-arrow-repeat me-2"></i><?= $canRunDocker ? 'กำลังอัปเดตระบบ' : 'กำลังปรับฐานข้อมูล' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด" id="updateModalCloseBtn" style="display: none;"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush" id="update-steps">
                    <?php if ($canRunDocker): ?>
                        <li class="list-group-item step-item" data-step="docker">
                            <div class="d-flex align-items-center gap-2">
                                <span class="step-icon text-muted"><i class="bi bi-box-seam"></i></span>
                                <span class="step-label flex-grow-1">ดึงเวอร์ชันใหม่และเปลี่ยน container</span>
                                <span class="step-status badge rounded-pill"></span>
                            </div>
                            <pre class="step-output small bg-body-tertiary rounded p-2 mt-2 mb-0" style="display: none; max-height: 160px; overflow: auto; white-space: pre-wrap;"></pre>
                        </li>
                    <?php endif; ?>
                    <li class="list-group-item step-item" data-step="migrate">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-icon text-muted"><i class="bi bi-database"></i></span>
                            <span class="step-label flex-grow-1">ปรับโครงสร้างฐานข้อมูล (Migration)</span>
                            <span class="step-status badge rounded-pill"></span>
                        </div>
                        <pre class="step-output small bg-body-tertiary rounded p-2 mt-2 mb-0" style="display: none; max-height: 160px; overflow: auto; white-space: pre-wrap;"></pre>
                    </li>
                </ul>
                <div class="alert alert-danger small mt-3 mb-0" id="update-error-hint" style="display: none;">
                    <i class="bi bi-exclamation-octagon me-1"></i>
                    ไม่สำเร็จ ดูข้อความด้านบนเทียบกับหัวข้อ <strong>ปัญหาที่พบบ่อย</strong> บนหน้านี้
                    (เช่น Access denied = user ฐานข้อมูลไม่มีสิทธิ์, Connection refused = ต่อฐานไม่ได้)
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="updateModalDoneBtn" style="display: none;">ปิด</button>
                <button type="button" class="btn btn-primary" id="updateModalReloadBtn" style="display: none;" onclick="window.location.reload();">โหลดหน้าใหม่</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// แท็บตามมาตรฐาน page-nav: active = primary, ที่เหลือ = outline-secondary
$this->registerCss(<<<CSS
.update-tabs .nav-link { border: 1px solid var(--bs-border-color); color: var(--bs-secondary-color); background: transparent; font-weight: 500; }
.update-tabs .nav-link:hover { color: var(--bs-body-color); }
.update-tabs .nav-link.active { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }
CSS);

$urlLatest = json_encode(Url::to(['/settings/update/ajax-latest-release']));
$this->registerJs(<<<JS
(function() {
    var box = document.getElementById('latest-release-status');
    if (!box) return;
    fetch({$urlLatest}, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) {
                box.innerHTML = '<span class="text-muted"><i class="bi bi-cloud-slash me-1"></i>' + d.message + '</span>';
            } else if (!d.latest) {
                box.innerHTML = '<span class="text-muted"><i class="bi bi-info-circle me-1"></i>ยังไม่มีเวอร์ชันที่ปล่อยบนช่องทาง stable</span>';
            } else if (d.hasUpdate) {
                box.innerHTML = '<span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1"><i class="bi bi-arrow-up-circle me-1"></i>มีเวอร์ชันใหม่ ' + d.latest + '</span> <span class="text-muted">อัปเดตด้วย ./update.sh ตามขั้นตอนด้านล่าง</span>';
            } else {
                box.innerHTML = '<span class="text-success-emphasis"><i class="bi bi-check-circle me-1"></i>ใหม่กว่าหรือเท่ากับเวอร์ชันล่าสุดที่ปล่อยแล้ว (' + d.latest + ')</span>';
            }
        })
        .catch(function() {});
})();
JS
, \yii\web\View::POS_READY);

$csrfParam = json_encode(Yii::$app->request->csrfParam);
$csrfToken = json_encode(Yii::$app->request->csrfToken);
$urlDocker = json_encode(Url::to(['/settings/update/ajax-docker-pull']));
$urlMigrate = json_encode(Url::to(['/settings/update/ajax-migrate']));
$canRunDockerJs = json_encode($canRunDocker);
$this->registerJs(<<<JS
(function() {
    // ปุ่มคัดลอกคำสั่ง (fallback execCommand สำหรับเว็บที่เปิดผ่าน http ซึ่งไม่มี navigator.clipboard)
    function copyText(text, done) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done, function() { legacyCopy(text); done(); });
        } else {
            legacyCopy(text);
            done();
        }
    }
    function legacyCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    }
    document.querySelectorAll('.btn-copy-cmd').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var code = btn.closest('.update-cmd').querySelector('code');
            copyText(code.textContent, function() {
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(function() { btn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1500);
            });
        });
    });

    var modal = document.getElementById('updateVersionModal');
    if (!modal) return;
    var csrfParam = {$csrfParam};
    var csrfToken = {$csrfToken};
    var urlDocker = {$urlDocker};
    var urlMigrate = {$urlMigrate};
    var canRunDocker = {$canRunDockerJs};

    var badgeBase = 'step-status badge rounded-pill fw-medium px-2 py-1';
    var badges = {
        running: [badgeBase + ' bg-primary-subtle text-primary-emphasis', 'กำลังดำเนินการ...', '<span class="spinner-border spinner-border-sm"></span>'],
        success: [badgeBase + ' bg-success-subtle text-success-emphasis', 'สำเร็จ', '<i class="bi bi-check-circle-fill text-success"></i>'],
        error: [badgeBase + ' bg-danger-subtle text-danger-emphasis', 'ไม่สำเร็จ', '<i class="bi bi-x-circle-fill text-danger"></i>']
    };

    function setStepState(step, state, output) {
        var el = modal.querySelector('[data-step="' + step + '"]');
        if (!el) return;
        var b = badges[state];
        var status = el.querySelector('.step-status');
        status.className = b[0];
        status.textContent = b[1];
        el.querySelector('.step-icon').innerHTML = b[2];
        var outPre = el.querySelector('.step-output');
        outPre.style.display = (output && output.length) ? 'block' : 'none';
        outPre.textContent = output || '';
    }

    function postJson(url, done) {
        var form = new FormData();
        form.append(csrfParam, csrfToken);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
            var data = { success: false, message: 'เซิร์ฟเวอร์ตอบกลับผิดพลาด (HTTP ' + xhr.status + ')', output: '' };
            try {
                data = JSON.parse(xhr.responseText || '{}');
            } catch (e) {}
            done(data);
        };
        xhr.send(form);
    }

    function finish(hasError) {
        document.getElementById('update-error-hint').style.display = hasError ? 'block' : 'none';
        document.getElementById('updateModalCloseBtn').style.display = 'block';
        document.getElementById('updateModalDoneBtn').style.display = 'inline-block';
        document.getElementById('updateModalReloadBtn').style.display = 'inline-block';
    }

    function runSteps() {
        var steps = [];
        if (canRunDocker) steps.push({ key: 'docker', url: urlDocker });
        steps.push({ key: 'migrate', url: urlMigrate });
        var idx = 0;

        function next() {
            if (idx >= steps.length) {
                finish(false);
                return;
            }
            var s = steps[idx];
            setStepState(s.key, 'running');
            postJson(s.url, function(data) {
                setStepState(s.key, data.success ? 'success' : 'error', data.output || data.message);
                if (!data.success) {
                    finish(true);
                    return;
                }
                idx++;
                next();
            });
        }
        next();
    }

    modal.addEventListener('show.bs.modal', function() {
        modal.querySelectorAll('.step-status').forEach(function(b) {
            b.textContent = '';
            b.className = 'step-status badge rounded-pill';
        });
        modal.querySelectorAll('.step-output').forEach(function(p) { p.style.display = 'none'; p.textContent = ''; });
        ['updateModalCloseBtn', 'updateModalDoneBtn', 'updateModalReloadBtn', 'update-error-hint'].forEach(function(id) {
            document.getElementById(id).style.display = 'none';
        });
    });
    modal.addEventListener('shown.bs.modal', runSteps);

    function openModal() {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    var btn = document.getElementById('btn-update-version');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var html = canRunDocker
            ? 'ระบบจะดึงเวอร์ชันใหม่ เปลี่ยน container แล้วปรับฐานข้อมูล'
            : 'ระบบจะปรับโครงสร้างฐานข้อมูลให้ตรงกับเวอร์ชัน <b>ที่ติดตั้งอยู่ตอนนี้</b><br><small class="text-muted">ปุ่มนี้ไม่ได้ดาวน์โหลดเวอร์ชันใหม่ ถ้ายังไม่ได้ทำ docker pull บนเซิร์ฟเวอร์ ให้ทำตามขั้นตอนบนหน้านี้ก่อน</small>';
        html += '<br><br><small class="text-danger">ควรสำรองฐานข้อมูลก่อนทุกครั้ง</small>';
        if (typeof Swal === 'undefined') {
            if (confirm('ยืนยันดำเนินการ?')) openModal();
            return;
        }
        Swal.fire({
            title: 'ยืนยันดำเนินการ',
            html: html,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ดำเนินการ',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed) openModal();
        });
    });
})();
JS
, \yii\web\View::POS_READY);
?>
