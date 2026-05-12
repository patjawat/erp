<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;

// 1. ข้อมูลจำลอง (Dummy Data) - เมื่อต่อ DB แล้ว ให้เปลี่ยนไปใช้ $tasks ที่ส่งมาจาก Controller แทน
$tasks = [
    ['id' => 'MT-2604-001', 'assetName' => 'เครื่องปรับอากาศ 24000 BTU', 'assetId' => '7910-002-0045/65.02', 'issue' => 'แอร์ไม่เย็น มีน้ำหยด', 'location' => 'ห้องตรวจ 1', 'reporter' => 'นายแพทย์สมชาย ใจดี', 'priority' => 'high', 'status' => 'pending', 'date' => '28 เม.ย. 2569'],
    ['id' => 'MT-2604-002', 'assetName' => 'เครื่องดูดฝุ่น', 'assetId' => '7910-003-0003/66.01', 'issue' => 'เปิดไม่ติด มอเตอร์มีกลิ่นไหม้', 'location' => 'งานรักษาความสะอาด', 'reporter' => 'นางสุธาสินี สายบุญตั้ง', 'priority' => 'medium', 'status' => 'in-progress', 'date' => '27 เม.ย. 2569'],
    ['id' => 'MT-2604-003', 'assetName' => 'คอมพิวเตอร์ตั้งโต๊ะ', 'assetId' => '7910-004-0012/64.01', 'issue' => 'จอฟ้า (Blue Screen) เปิดไม่ขึ้นภาพ', 'location' => 'ห้อง IT', 'reporter' => 'นายสมศักดิ์ ไอที', 'priority' => 'high', 'status' => 'pending', 'date' => '28 เม.ย. 2569'],
    ['id' => 'MT-2604-004', 'assetName' => 'เครื่องพิมพ์เลเซอร์', 'assetId' => '7910-004-0015/66.03', 'issue' => 'กระดาษติดบ่อยครั้ง พิมพ์ออกเป็นเส้นดำ', 'location' => 'ห้องการเงิน', 'reporter' => 'นางสาวการเงิน ร่ำรวย', 'priority' => 'low', 'status' => 'waiting-part', 'date' => '25 เม.ย. 2569'],
    ['id' => 'MT-2604-005', 'assetName' => 'เครื่องขัดพื้นขนาด 18 นิ้ว', 'assetId' => '7910-001-0001/66.01', 'issue' => 'ด้ามจับหลวม ล้อฝืด', 'location' => 'งานบริหารทั่วไป', 'reporter' => 'นางสมภาร สุทธิ', 'priority' => 'medium', 'status' => 'completed', 'date' => '20 เม.ย. 2569'],
];

// โครงสร้างคอลัมน์ Kanban
$kanbanColumns = [
    ['id' => 'pending', 'title' => 'แจ้งซ่อมใหม่', 'icon' => 'alert-circle', 'color' => '#ef4444', 'bg' => '#fef2f2'],
    ['id' => 'in-progress', 'title' => 'กำลังดำเนินการ', 'icon' => 'wrench', 'color' => '#1E4E91', 'bg' => '#eff6ff'],
    ['id' => 'waiting-part', 'title' => 'รออะไหล่ / รอดำเนินการ', 'icon' => 'clock-3', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
    ['id' => 'completed', 'title' => 'เสร็จสิ้น', 'icon' => 'check-circle-2', 'color' => '#10b981', 'bg' => '#ecfdf5'],
];

// ฟังก์ชันแปลง Priority เป็นสี
function getPriorityStyle($priority) {
    switch($priority) {
        case 'high': return ['bg' => '#fee2e2', 'text' => '#dc2626', 'label' => 'ด่วนมาก'];
        case 'medium': return ['bg' => '#fef3c7', 'text' => '#d97706', 'label' => 'ปานกลาง'];
        case 'low': return ['bg' => '#f1f5f9', 'text' => '#64748b', 'label' => 'ทั่วไป'];
        default: return ['bg' => '#f1f5f9', 'text' => '#64748b', 'label' => 'ทั่วไป'];
    }
}
?>

<!-- โหลด Bootstrap 5 และ Lucide Icons -->

<style>
    /* Inline CSS เฉพาะการปรับแต่งพิเศษเล็กน้อย */
    .lucide { width: 18px; height: 18px; }
    .kanban-task { cursor: grab; }
    .kanban-task:active { cursor: grabbing; }
    .kanban-task.dragging { opacity: 0.5; transform: scale(0.98); }
    .hover-border:hover { border-color: #cbd5e1 !important; }
    .scroll-hide::-webkit-scrollbar { display: none; }
    .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="d-flex flex-column min-vh-100 position-relative">
    
    <!-- Main Content Wrapper -->
    <main class="w-100 flex-grow-1 d-flex flex-column p-4 mx-auto gap-4 position-relative">
        
        <!-- Breadcrumb & Tabs Header -->
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-2">
            <div class="d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-2 fw-medium" style="font-size: 12px; color: #64748b;">
                    <i data-lucide="layout-dashboard" style="color: #94a3b8; width: 14px; height: 14px;"></i>
                    <span>หน้าหลัก</span>
                    <i data-lucide="chevron-right" style="color: #cbd5e1; width: 12px; height: 12px;"></i>
                    <span>ระบบงานซ่อมบำรุง</span>
                    <i data-lucide="chevron-right" style="color: #cbd5e1; width: 12px; height: 12px;"></i>
                    <span class="fw-bold" style="color: #1E4E91;">กระดานงาน (Kanban)</span>
                </div>
                <h2 class="m-0 fw-bold d-flex align-items-center gap-2" style="font-size: 24px; color: #1e293b;">
                    <i data-lucide="wrench" style="color: #1E4E91; width: 24px; height: 24px;"></i>
                    จัดการงานซ่อมบำรุง
                </h2>
            </div>

            <div class="d-flex gap-2">
                <div class="position-relative">
                    <i data-lucide="search" class="position-absolute top-50 translate-middle-y" style="color: #94a3b8; left: 12px; width: 16px; height: 16px;"></i>
                    <input type="text" placeholder="ค้นหางานซ่อม..." class="form-control rounded-3 border" style="padding-left: 36px; font-size: 13px; width: 220px; background-color: white; border-color: #e2e8f0;">
                </div>
                <button class="btn bg-white fw-medium d-flex align-items-center justify-content-center gap-2 rounded-3 border shadow-sm" style="border-color: #e2e8f0; color: #475569; font-size: 13px;">
                    <i data-lucide="filter" style="color: #94a3b8; width: 16px; height: 16px;"></i> ตัวกรอง
                </button>
                <button class="btn text-white fw-semibold d-flex align-items-center gap-2 rounded-3 shadow-sm" style="background-color: #1E4E91; font-size: 13px;">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i> สร้างใบแจ้งซ่อม
                </button>
            </div>
        </div>

        <!-- KANBAN BOARD -->
        <div class="row g-4 flex-grow-1 h-100 flex-nowrap overflow-x-auto pb-4 scroll-hide" style="min-height: 600px;">
            
            <?php foreach($kanbanColumns as $column): ?>
                <?php
                    // Filter tasks for this column
                    $colTasks = array_filter($tasks, function($t) use ($column) {
                        return $t['status'] === $column['id'];
                    });
                ?>
                <div class="col-12 col-md-6 col-lg-3 kanban-col" data-status="<?= $column['id'] ?>" style="min-width: 320px;">
                    <div class="d-flex flex-column h-100 rounded-4 border" style="background-color: #f8fafc; border-color: #e2e8f0;">
                        
                        <!-- Column Header -->
                        <div class="p-3 d-flex align-items-center justify-content-between border-bottom" style="border-color: #e2e8f0 !important;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-1 rounded-2 d-flex align-items-center justify-content-center" style="background-color: <?= $column['bg'] ?>; color: <?= $column['color'] ?>;">
                                    <i data-lucide="<?= $column['icon'] ?>"></i>
                                </div>
                                <span class="fw-bold" style="color: #1e293b; font-size: 15px;"><?= $column['title'] ?></span>
                            </div>
                            <span class="badge rounded-pill fw-bold task-counter" style="background-color: #e2e8f0; color: #475569; font-size: 12px;">
                                <?= count($colTasks) ?>
                            </span>
                        </div>

                        <!-- Task List Dropzone -->
                        <div class="p-3 d-flex flex-column gap-3 flex-grow-1 task-list-container" style="overflow-y: auto;">
                            <?php foreach($colTasks as $task): ?>
                                <?php $priorityStyle = getPriorityStyle($task['priority']); ?>
                                
                                <!-- Individual Task Card -->
                                <div class="kanban-task bg-white p-3 rounded-4 shadow-sm border position-relative transition-all hover-border" 
                                     draggable="true" 
                                     data-id="<?= $task['id'] ?>"
                                     style="border-color: #e2e8f0;">
                                    
                                    <!-- Priority & ID -->
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge rounded-2 fw-bold" style="background-color: <?= $priorityStyle['bg'] ?>; color: <?= $priorityStyle['text'] ?>; font-size: 10px;">
                                            <?= $priorityStyle['label'] ?>
                                        </span>
                                        <span class="font-monospace fw-medium" style="font-size: 11px; color: #94a3b8;"><?= $task['id'] ?></span>
                                    </div>

                                    <!-- Content -->
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-1 lh-base" style="font-size: 14px; color: #1e293b;"><?= $task['issue'] ?></h6>
                                        <p class="m-0 text-truncate" style="font-size: 12px; color: #1E4E91;"><?= $task['assetName'] ?></p>
                                    </div>

                                    <!-- Meta data -->
                                    <div class="d-flex flex-column gap-1 pt-2 border-top" style="border-color: #f1f5f9 !important; font-size: 11px; color: #64748b;">
                                        <div class="d-flex align-items-center gap-1">
                                            <i data-lucide="building-2" style="width: 12px; height: 12px; color: #94a3b8;"></i> <?= $task['location'] ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i data-lucide="users" style="width: 12px; height: 12px; color: #94a3b8;"></i> <?= $task['reporter'] ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <i data-lucide="clock" style="width: 12px; height: 12px; color: #94a3b8;"></i> <?= $task['date'] ?>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex align-items-center justify-content-end gap-1 mt-2 pt-2">
                                        <?php if(in_array($column['id'], ['pending', 'in-progress'])): ?>
                                            <button onclick="openAiModal('<?= $task['assetName'] ?>', '<?= $task['issue'] ?>')" class="btn btn-sm d-flex align-items-center gap-1 border-0 p-1 px-2 rounded-2 fw-medium" style="background-color: #fdf4ff; color: #c026d3; font-size: 11px;">
                                                <i data-lucide="sparkles" style="width: 12px; height: 12px;"></i> AI วิเคราะห์
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm border-0 p-1 ms-auto" style="color: #94a3b8;">
                                            <i data-lucide="more-vertical" style="width: 16px; height: 16px;"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Placeholder when empty -->
                            <div class="empty-placeholder text-center p-4 border rounded-4 border-dashed <?= count($colTasks) > 0 ? 'd-none' : '' ?>" style="border-color: #e2e8f0; color: #94a3b8;">
                                <p class="m-0 fw-medium" style="font-size: 12px;">ลากงานมาวางที่นี่</p>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <!-- AI Modal (Bootstrap 5) -->
    <div class="modal fade" id="aiRepairModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header px-4 py-3 border-bottom" style="background-color: #f8fafc; border-color: #f1f5f9 !important;">
                    <h5 class="modal-title m-0 fw-bold d-flex align-items-center gap-2" style="font-size: 16px; color: #c026d3;">
                        <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i> AI แนะนำการซ่อมบำรุง
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                        <p class="m-0 fw-bold" style="font-size: 13px; color: #334155;">ครุภัณฑ์: <span id="aiAssetName" class="fw-medium" style="color: #1E4E91;"></span></p>
                        <p class="m-0 fw-bold mt-1" style="font-size: 13px; color: #334155;">อาการเสีย: <span id="aiIssue" class="fw-medium" style="color: #dc2626;"></span></p>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="aiLoading" class="d-none flex-column align-items-center justify-content-center py-4 gap-3">
                        <div class="spinner-border" style="color: #c026d3;" role="status"></div>
                        <p class="m-0 fw-medium" style="color: #64748b; font-size: 14px;">AI กำลังวิเคราะห์อาการและหาวิธีซ่อม...</p>
                    </div>

                    <!-- Result Area -->
                    <div id="aiResult" class="p-3 rounded-3 border d-none" style="background-color: #fdf4ff; border-color: #f0abfc; font-size: 14px; color: #334155; line-height: 1.6; white-space: pre-wrap;">
                        <!-- AI content will be appended here via JS -->
                    </div>
                    
                    <button type="button" class="btn w-100 fw-bold mt-3" data-bs-dismiss="modal" style="background-color: #f1f5f9; color: #334155;">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ใช้ Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // ----------------------------------------------------
    // Drag & Drop Kanban Logic (Vanilla JS)
    // ----------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        const tasks = document.querySelectorAll('.kanban-task');
        const columns = document.querySelectorAll('.kanban-col');
        let draggedTask = null;

        // Add event listeners to all tasks
        tasks.forEach(task => {
            task.addEventListener('dragstart', function(e) {
                draggedTask = this;
                setTimeout(() => this.classList.add('dragging'), 0);
                e.dataTransfer.effectAllowed = 'move';
            });

            task.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedTask = null;
            });
        });

        // Add event listeners to all columns
        columns.forEach(column => {
            const taskContainer = column.querySelector('.task-list-container');
            
            column.addEventListener('dragover', function(e) {
                e.preventDefault(); // allow drop
                taskContainer.style.backgroundColor = '#f1f5f9'; // hover effect
            });

            column.addEventListener('dragleave', function() {
                taskContainer.style.backgroundColor = 'transparent';
            });

            column.addEventListener('drop', function(e) {
                e.preventDefault();
                taskContainer.style.backgroundColor = 'transparent';
                
                if (draggedTask) {
                    // Append task to new column
                    taskContainer.appendChild(draggedTask);
                    
                    // Update counters
                    updateCounters();

                    // Hide/Show placeholder empty text
                    updatePlaceholders();

                    // --- AJAX CALL ไปยัง YII2 Controller ---
                    const taskId = draggedTask.getAttribute('data-id');
                    const newStatus = this.getAttribute('data-status');
                    
                    console.log(`[AJAX MOCK] อัปเดต Task ID: ${taskId} -> สถานะ: ${newStatus}`);
                    /* ตัวอย่างการยิง AJAX ใน Yii2:
                    $.post('<?= Url::to(['/helpdesk/general/update-status']) ?>', {
                        id: taskId,
                        status: newStatus,
                        _csrf: yii.getCsrfToken()
                    }).done(function(res) {
                        if(res.success) alert('ย้ายสำเร็จ');
                    });
                    */
                }
            });
        });

        function updateCounters() {
            columns.forEach(col => {
                const count = col.querySelectorAll('.kanban-task').length;
                col.querySelector('.task-counter').innerText = count;
            });
        }

        function updatePlaceholders() {
            columns.forEach(col => {
                const count = col.querySelectorAll('.kanban-task').length;
                const placeholder = col.querySelector('.empty-placeholder');
                if(placeholder) {
                    if(count === 0) placeholder.classList.remove('d-none');
                    else placeholder.classList.add('d-none');
                }
            });
        }
    });

    // ----------------------------------------------------
    // AI Modal Logic
    // ----------------------------------------------------
    function openAiModal(assetName, issue) {
        // Set text
        document.getElementById('aiAssetName').innerText = assetName;
        document.getElementById('aiIssue').innerText = issue;
        
        // Setup UI state
        const loadingDiv = document.getElementById('aiLoading');
        const resultDiv = document.getElementById('aiResult');
        loadingDiv.classList.remove('d-none');
        loadingDiv.classList.add('d-flex');
        resultDiv.classList.add('d-none');
        resultDiv.innerText = '';

        // Open Modal
        const myModal = new bootstrap.Modal(document.getElementById('aiRepairModal'));
        myModal.show();

        // Simulate AJAX / AI API Call (จำลองดีเลย์ 2 วิ)
        setTimeout(() => {
            loadingDiv.classList.remove('d-flex');
            loadingDiv.classList.add('d-none');
            
            resultDiv.innerText = "ข้อเสนอแนะจาก AI (จำลอง):\n1. ตรวจสอบการเสียบปลั๊กและเบรกเกอร์ว่ามีการตัดไฟหรือไม่\n2. สังเกตและดมกลิ่นบริเวณมอเตอร์ว่ามีรอยไหม้หรือความร้อนสูงผิดปกติหรือไม่\n3. หากพบความร้อน ให้งดใช้งานและเปิดเครื่องเพื่อตรวจสอบทุ่นและขดลวด";
            resultDiv.classList.remove('d-none');
        }, 2000);
    }
</script>