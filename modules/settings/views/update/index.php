<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var string $version
 * @var array $dockerConfig ['image' => string, 'composePath' => string|null, 'serviceName' => string]
 */
$canRunDocker = !empty($dockerConfig['composePath']) && is_dir($dockerConfig['composePath']);
$dockerImage = $dockerConfig['image'] ?? 'patjawat/erp:latest';
$dockerService = $dockerConfig['serviceName'] ?? 'app';

$this->title = 'อัปเดตระบบ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings fs-4 me-2">
    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
    <circle cx="12" cy="12" r="3"></circle>
</svg>
<?= $this->title ?>
<?php $this->endBlock(); ?>

<div class="container">
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal text-white">
                        <i class="fa-solid fa-code-branch me-2"></i>เวอร์ชันแอปพลิเคชัน
                    </h6>
                </div>
                <div class="card-body py-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($version) ?></span>
                        <span class="text-muted small">เวอร์ชันที่ติดตั้งอยู่ขณะนี้</span>
                        <button type="button" class="btn btn-success ms-auto" id="btn-update-version">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Update version (<?= Html::encode($version) ?>)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal แสดงสถานะการอัปเดต -->
        <div class="modal fade" id="updateVersionModal" tabindex="-1" aria-labelledby="updateVersionModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white" id="updateVersionModalLabel">
                            <i class="fa-solid fa-arrows-rotate me-2"></i>กำลังอัปเดตระบบ
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด" id="updateModalCloseBtn" style="display: none;"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group list-group-flush" id="update-steps">
                            <li class="list-group-item step-item flex-wrap" data-step="docker">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="step-icon text-muted"><i class="fa-solid fa-docker fa-fw"></i></span>
                                    <span class="step-label flex-grow-1">ดึง Image และรีสตาร์ท Container</span>
                                    <span class="step-status badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1"></span>
                                </div>
                                <pre class="step-output small bg-light rounded p-2 mt-2 mb-0 w-100" style="display: none; max-height: 120px; overflow: auto; white-space: pre-wrap;"></pre>
                            </li>
                            <li class="list-group-item step-item flex-wrap" data-step="migrate">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="step-icon text-muted"><i class="fa-solid fa-database fa-fw"></i></span>
                                    <span class="step-label flex-grow-1">รัน Migration</span>
                                    <span class="step-status badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1"></span>
                                </div>
                                <pre class="step-output small bg-light rounded p-2 mt-2 mb-0 w-100" style="display: none; max-height: 120px; overflow: auto; white-space: pre-wrap;"></pre>
                            </li>
                            <li class="list-group-item step-item flex-wrap" data-step="update-table">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="step-icon text-muted"><i class="fa-solid fa-route fa-fw"></i></span>
                                    <span class="step-label flex-grow-1">อัปเดต Route (update-table)</span>
                                    <span class="step-status badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1"></span>
                                </div>
                                <pre class="step-output small bg-light rounded p-2 mt-2 mb-0 w-100" style="display: none; max-height: 120px; overflow: auto; white-space: pre-wrap;"></pre>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="updateModalDoneBtn" style="display: none;">ปิด</button>
                        <button type="button" class="btn btn-primary" id="updateModalReloadBtn" style="display: none;" onclick="window.location.reload();">โหลดหน้าใหม่</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal text-white">
                        <i class="fa-solid fa-docker me-2"></i>วิธีอัปเดตเมื่อรันด้วย Docker (Docker Hub)
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        เมื่อมี image ใหม่บน Docker Hub ผู้ดูแลระบบสามารถอัปเดตบนเครื่องที่รัน production ได้ดังนี้
                    </p>
                    <ol class="mb-0 ps-3 small">
                        <li class="mb-2">
                            <strong>ดึง image ล่าสุด</strong><br>
                            <code class="bg-light px-2 py-1 rounded">docker pull patjawat/erp:latest</code>
                        </li>
                        <li class="mb-2">
                            <strong>รีสตาร์ทเฉพาะ service (ไม่กระทบ DB)</strong><br>
                            <code class="bg-light px-2 py-1 rounded">docker-compose up -d --no-deps --force-recreate app</code>
                        </li>
                        <li class="mb-2">
                            <strong>รัน migration (สำคัญ)</strong><br>
                            <code class="bg-light px-2 py-1 rounded">docker compose exec app php yii migrate --interactive=0</code>
                        </li>
                    </ol>
                    <p class="text-muted small mt-3 mb-0">
                        ถ้าใช้ไฟล์ compose อื่น เช่น <code>docker-compose-nginx.yml</code> ให้ใส่ <code>-f docker-compose-nginx.yml</code> ในคำสั่ง
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal text-white">
                        <i class="fa-solid fa-play me-2"></i>อัปเดตจากเว็บ (เฉพาะผู้ดูแลระบบ)
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        กดปุ่ม <strong>Update version (<?= Html::encode($version) ?>)</strong> ด้านบนเพื่อรันขั้นตอนอัปเดตทั้งหมด และดูสถานะใน modal
                    </p>
                    <?php if (!$canRunDocker): ?>
                        <p class="text-warning small mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            ขั้น Docker ใช้ได้เมื่อตั้งค่า <code>params[dockerUpdate][composePath]</code> ใน config/params.php
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal text-white">
                        <i class="fa-solid fa-circle-info me-2"></i>ข้อควรระวัง
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3 small text-muted">
                        <li>ทุกครั้งที่อัปเดต image ควรรัน migration เพื่อให้ schema ฐานข้อมูลตรงกับโค้ด</li>
                        <li>ถ้ามีการเพิ่ม route ใหม่ อาจต้องรัน <code>yii update-table</code> หลังอัปเดต</li>
                        <li>ข้อมูลใน volume (เช่น fileupload) และฐานข้อมูลไม่ถูกลบเมื่อ pull image ใหม่</li>
                        <li><strong>Docker pull / restart</strong> ถ้าตั้งค่า <code>params[dockerUpdate][composePath]</code> และเซิร์ฟเวอร์มี docker (หรือ mount docker.sock) จะกดจากเว็บได้</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับไปการตั้งค่าระบบ', ['/settings/default/index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>

<?php
$csrfParam = json_encode(Yii::$app->request->csrfParam);
$csrfToken = json_encode(Yii::$app->request->csrfToken);
$urlDocker = json_encode(Url::to(['/settings/update/ajax-docker-pull']));
$urlMigrate = json_encode(Url::to(['/settings/update/ajax-migrate']));
$urlUpdateTable = json_encode(Url::to(['/settings/update/ajax-update-table']));
$canRunDockerJs = json_encode($canRunDocker);
$this->registerJs(<<<JS
(function() {
    var modal = document.getElementById('updateVersionModal');
    if (!modal) return;
    var csrfParam = {$csrfParam};
    var csrfToken = {$csrfToken};
    var urlDocker = {$urlDocker};
    var urlMigrate = {$urlMigrate};
    var urlUpdateTable = {$urlUpdateTable};
    var canRunDocker = {$canRunDockerJs};

    var badgeBase = 'step-status badge rounded-pill fw-medium px-2 py-1';
    var badgePrimary = badgeBase + ' bg-primary bg-opacity-10 text-primary border border-primary-subtle';
    var badgeSuccess = badgeBase + ' bg-success bg-opacity-10 text-success border border-success-subtle';
    var badgeSecondary = badgeBase + ' bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle';
    var badgeDanger = badgeBase + ' bg-danger bg-opacity-10 text-danger border border-danger-subtle';

    function setStepState(step, state, output) {
        var el = modal.querySelector('[data-step="' + step + '"]');
        if (!el) return;
        var icon = el.querySelector('.step-icon');
        var status = el.querySelector('.step-status');
        var outPre = el.querySelector('.step-output');
        if (state === 'running') {
            status.className = badgePrimary;
            status.textContent = 'กำลังดำเนินการ...';
            if (icon) icon.innerHTML = '<i class="fa-solid fa-spinner fa-spin fa-fw"></i>';
        } else if (state === 'success') {
            status.className = badgeSuccess;
            status.textContent = 'สำเร็จ';
            if (icon) icon.innerHTML = '<i class="fa-solid fa-check text-success fa-fw"></i>';
        } else if (state === 'skip') {
            status.className = badgeSecondary;
            status.textContent = 'ข้าม';
            if (icon) icon.innerHTML = '<i class="fa-solid fa-minus text-muted fa-fw"></i>';
        } else {
            status.className = badgeDanger;
            status.textContent = 'ไม่สำเร็จ';
            if (icon) icon.innerHTML = '<i class="fa-solid fa-times text-danger fa-fw"></i>';
        }
        if (outPre) {
            outPre.style.display = (output && output.length) ? 'block' : 'none';
            outPre.textContent = output || '';
        }
    }

    function postJson(url, done) {
        var form = new FormData();
        form.append(csrfParam, csrfToken);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
            var data = { success: false, message: '', output: '' };
            try {
                data = JSON.parse(xhr.responseText || '{}');
            } catch (e) {}
            done(data);
        };
        xhr.send(form);
    }

    function runSteps() {
        var steps = canRunDocker
            ? [ { key: 'docker', url: urlDocker }, { key: 'migrate', url: urlMigrate }, { key: 'update-table', url: urlUpdateTable } ]
            : [ { key: 'docker', url: urlDocker, skip: true }, { key: 'migrate', url: urlMigrate }, { key: 'update-table', url: urlUpdateTable } ];
        var idx = 0;

        function next() {
            if (idx >= steps.length) {
                document.getElementById('updateModalCloseBtn').style.display = 'block';
                document.getElementById('updateModalDoneBtn').style.display = 'inline-block';
                document.getElementById('updateModalReloadBtn').style.display = 'inline-block';
                return;
            }
            var s = steps[idx];
            setStepState(s.key, s.skip ? 'skip' : 'running');
            if (s.skip) {
                idx++;
                next();
                return;
            }
            postJson(s.url, function(data) {
                setStepState(s.key, data.success ? 'success' : 'error', data.output || data.message);
                idx++;
                next();
            });
        }
        next();
    }

    modal.addEventListener('show.bs.modal', function() {
        document.querySelectorAll('#update-steps .step-status').forEach(function(b) {
            b.textContent = '';
            b.className = 'step-status badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1';
        });
        document.querySelectorAll('#update-steps .step-output').forEach(function(p) { p.style.display = 'none'; p.textContent = ''; });
        document.getElementById('updateModalCloseBtn').style.display = 'none';
        document.getElementById('updateModalDoneBtn').style.display = 'none';
        document.getElementById('updateModalReloadBtn').style.display = 'none';
    });
    modal.addEventListener('shown.bs.modal', function() {
        runSteps();
    });

    var btn = document.getElementById('btn-update-version');
    if (btn) {
        btn.addEventListener('click', function() {
            if (typeof Swal === 'undefined') {
                if (confirm('ต้องการอัปเดตระบบจริงหรือไม่?')) {
                    var m = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(modal) : null;
                    if (m) m.show();
                }
                return;
            }
            Swal.fire({
                title: 'ยืนยันอัปเดตระบบ',
                html: 'ต้องการอัปเดตระบบจริงหรือไม่?<br><br><small class="text-muted">จะดำเนินการ: ดึง Image / รีสตาร์ท Container, รัน Migration และอัปเดต Route</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'อัปเดต',
                cancelButtonText: 'ยกเลิก'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var m = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(modal) : null;
                    if (m) m.show();
                }
            });
        });
    }
})();
JS
, \yii\web\View::POS_READY);
?>
