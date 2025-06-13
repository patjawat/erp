    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Fabric.js for canvas manipulation -->
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
    <!-- PDF.js for PDF rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<style>
            
        .thai-gov-gradient {
            background: linear-gradient(135deg, var(--navy) 0%, #1A4B8C 100%);
        }
        
        .gold-accent {
            color: var(--gold);
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--navy) 0%, #1A4B8C 100%);
            min-height: calc(100vh - 70px);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            color: #fff;
            border-left: 4px solid var(--gold);
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .upload-zone {
            border: 2px dashed #ced4da;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            border-color: var(--navy);
            background-color: rgba(10, 49, 97, 0.05);
        }
        
        .stamp {
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .stamp:hover {
            transform: scale(1.05);
        }
        
        .stamp.selected {
            border: 2px solid var(--gold) !important;
        }
        
        #canvas-container {
            position: relative;
            overflow: auto;
            background-color: #f8f9fa;
        }
        
        .color-option {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s;
            display: inline-block;
        }
        
        .color-option:hover {
            transform: scale(1.1);
        }
        
        .color-option.selected {
            border: 2px solid #000;
        }
        
        .tooltip-custom {
            position: relative;
        }
        
        .tooltip-custom:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .btn-primary {
            background-color: #10B981;
            border-color: #10B981;
        }
        
        .btn-primary:hover {
            background-color: #059669;
            border-color: #059669;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-danger {
            background-color: #EF4444;
            border-color: #EF4444;
        }
        
        .btn-danger:hover {
            background-color: #DC2626;
            border-color: #DC2626;
        }
</style>
<div class="container-fluid">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active" aria-current="page">จัดการเอกสาร</li>
                        </ol>
                    </nav>

                    <!-- Page Title -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 fw-bold">จัดการเอกสารราชการ</h1>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary d-flex align-items-center">
                                <i class="bi bi-plus-lg me-2"></i>
                                สร้างใหม่
                            </button>
                            <button class="btn btn-outline-secondary d-flex align-items-center">
                                <i class="bi bi-funnel me-2"></i>
                                ตัวกรอง
                            </button>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="row g-4">
                        <!-- Left Column - Upload and Stamp Controls -->
                        <div class="col-lg-4">
                            <!-- Upload Section -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">อัปโหลดเอกสาร</h5>
                                    
                                    <div id="upload-zone" class="upload-zone rounded p-4 text-center">
                                        <i class="bi bi-cloud-arrow-up text-secondary fs-1 mb-3"></i>
                                        <p class="text-secondary mb-2">ลากและวางไฟล์ PDF หรือคลิกเพื่อเลือกไฟล์</p>
                                        <p class="text-secondary small">รองรับเฉพาะไฟล์ PDF</p>
                                        <input type="file" id="file-input" class="d-none" accept="application/pdf">
                                    </div>
                                    
                                    <div id="upload-progress" class="mt-3 d-none">
                                        <div class="d-flex justify-content-between small text-secondary mb-1">
                                            <span>กำลังอัปโหลด...</span>
                                            <span id="progress-percent">100%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 100%;"></div>
                                        </div>
                                    </div>
                                    
                                    <div id="upload-success" class="mt-3 d-none">
                                        <div class="alert alert-success d-flex align-items-center py-2 mb-0" role="alert">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <span>อัปโหลดสำเร็จ</span>
                                        </div>
                                    </div>
                                    
                                    <div id="upload-error" class="mt-3 d-none">
                                        <div class="alert alert-danger d-flex align-items-center py-2 mb-0" role="alert">
                                            <i class="bi bi-exclamation-circle me-2"></i>
                                            <span>เกิดข้อผิดพลาด โปรดลองอีกครั้ง</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stamp Library Section -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">ตราประทับ</h5>
                                    
                                    <div class="row row-cols-2 g-3 mb-4">
                                        <div class="col">
                                            <div class="stamp border rounded p-3 text-center" data-stamp="received">
                                                <svg class="text-danger" style="width: 64px; height: 64px;" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                                    <text x="50" y="40" text-anchor="middle" fill="currentColor" font-size="12">ลงรับเลขที่</text>
                                                    <text x="50" y="55" text-anchor="middle" fill="currentColor" font-size="10">วันที่</text>
                                                    <text x="50" y="70" text-anchor="middle" fill="currentColor" font-size="10">เวลา</text>
                                                </svg>
                                                <span class="small d-block mt-2">ลงรับหนังสือ</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col">
                                            <div class="stamp border rounded p-3 text-center selected" data-stamp="urgent">
                                                <svg class="text-danger" style="width: 64px; height: 64px;" viewBox="0 0 100 100">
                                                    <rect x="5" y="5" width="90" height="90" rx="5" fill="none" stroke="currentColor" stroke-width="2"></rect>
                                                    <text x="50" y="40" text-anchor="middle" fill="currentColor" font-size="16" font-weight="bold">ด่วนที่สุด</text>
                                                    <path d="M20,60 L80,60" stroke="currentColor" stroke-width="2"></path>
                                                    <path d="M20,70 L80,70" stroke="currentColor" stroke-width="2"></path>
                                                </svg>
                                                <span class="small d-block mt-2">ด่วนที่สุด</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col">
                                            <div class="stamp border rounded p-3 text-center" data-stamp="approved">
                                                <svg class="text-primary" style="width: 64px; height: 64px;" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                                    <text x="50" y="45" text-anchor="middle" fill="currentColor" font-size="14">อนุมัติ</text>
                                                    <text x="50" y="65" text-anchor="middle" fill="currentColor" font-size="10">ลงชื่อ...............</text>
                                                </svg>
                                                <span class="small d-block mt-2">อนุมัติ</span>
                                            </div>
                                        </div>
                                        
                                        <div class="col">
                                            <div class="stamp border rounded p-3 text-center" data-stamp="confidential">
                                                <svg class="text-danger" style="width: 64px; height: 64px;" viewBox="0 0 100 100">
                                                    <rect x="5" y="5" width="90" height="90" rx="5" fill="none" stroke="currentColor" stroke-width="2"></rect>
                                                    <text x="50" y="40" text-anchor="middle" fill="currentColor" font-size="14" font-weight="bold">ลับ</text>
                                                    <text x="50" y="60" text-anchor="middle" fill="currentColor" font-size="14" font-weight="bold">เฉพาะ</text>
                                                </svg>
                                                <span class="small d-block mt-2">ลับเฉพาะ</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Stamp Customization -->
                                    <div class="mb-4">
                                        <h6 class="fw-medium mb-3">ปรับแต่งตราประทับ</h6>
                                        
                                        <!-- Color Selection -->
                                        <div class="mb-3">
                                            <label class="form-label small fw-medium">สี</label>
                                            <div class="d-flex gap-2">
                                                <div class="color-option selected" style="background-color: #B22234;" data-color="#B22234"></div>
                                                <div class="color-option" style="background-color: #0A3161;" data-color="#0A3161"></div>
                                                <div class="color-option" style="background-color: #000000;" data-color="#000000"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Size Adjustment -->
                                        <div class="mb-3">
                                            <label class="form-label small fw-medium">ขนาด</label>
                                            <input type="range" class="form-range" min="50" max="150" value="100" id="stamp-size">
                                            <div class="d-flex justify-content-between small text-secondary">
                                                <span>เล็ก</span>
                                                <span>ใหญ่</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Rotation -->
                                        <div class="mb-3">
                                            <label class="form-label small fw-medium">การหมุน</label>
                                            <input type="range" class="form-range" min="-180" max="180" value="0" id="stamp-rotation">
                                            <div class="d-flex justify-content-between small text-secondary">
                                                <span>-180°</span>
                                                <span>+180°</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Opacity -->
                                        <div class="mb-3">
                                            <label class="form-label small fw-medium">ความโปร่งใส</label>
                                            <input type="range" class="form-range" min="20" max="100" value="100" id="stamp-opacity">
                                            <div class="d-flex justify-content-between small text-secondary">
                                                <span>โปร่งใส</span>
                                                <span>ทึบ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Document Preview and Controls -->
                        <div class="col-lg-8">
                            <!-- Document Preview -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">ตัวอย่างเอกสาร</h5>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light tooltip-custom" data-tooltip="ย่อ">
                                                <i class="bi bi-zoom-out"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light tooltip-custom" data-tooltip="ขยาย">
                                                <i class="bi bi-zoom-in"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light tooltip-custom" data-tooltip="แสดงตาราง">
                                                <i class="bi bi-list"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light tooltip-custom" data-tooltip="เต็มหน้าจอ">
                                                <i class="bi bi-arrows-fullscreen"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="canvas-container" class="border rounded mb-3" style="height: 600px;">
                                        <div id="document-placeholder" class="text-center p-5 h-100 d-flex flex-column justify-content-center d-none">
                                            <i class="bi bi-file-earmark-text text-secondary mb-3" style="font-size: 4rem;"></i>
                                            <h5 class="text-secondary">ไม่มีเอกสาร</h5>
                                            <p class="text-secondary small">อัปโหลดเอกสาร PDF เพื่อแสดงตัวอย่าง</p>
                                        </div>
                                        <div class="canvas-container" style="width: 415.487px; height: 598px; position: relative; user-select: none;"><canvas id="pdf-canvas" class="lower-canvas" width="830.9739714525609" height="1196" style="position: absolute; width: 415.487px; height: 598px; left: 0px; top: 0px; touch-action: none; user-select: none;"></canvas><canvas class="upper-canvas " width="830.9739714525609" height="1196" style="position: absolute; width: 415.487px; height: 598px; left: 0px; top: 0px; touch-action: none; user-select: none; cursor: move;"></canvas></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light" disabled="" id="prev-page">
                                                <i class="bi bi-chevron-left"></i>
                                            </button>
                                            <span class="btn btn-sm btn-light disabled" id="page-info">หน้า 1 จาก 1</span>
                                            <button class="btn btn-sm btn-light" disabled="" id="next-page">
                                                <i class="bi bi-chevron-right"></i>
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light tooltip-custom" data-tooltip="ยกเลิกการทำ" id="undo-btn">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light tooltip-custom" data-tooltip="ทำซ้ำ" id="redo-btn" disabled="">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                        <div class="d-flex gap-2 mb-3 mb-sm-0">
                                            <button class="btn btn-danger d-flex align-items-center">
                                                <i class="bi bi-x-lg me-2"></i>
                                                ยกเลิก
                                            </button>
                                            <button class="btn btn-secondary d-flex align-items-center">
                                                <i class="bi bi-arrow-repeat me-2"></i>
                                                รีเซ็ต
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-secondary d-flex align-items-center">
                                                <i class="bi bi-download me-2"></i>
                                                บันทึกเป็นไฟล์
                                            </button>
                                            <button class="btn btn-primary d-flex align-items-center">
                                                <i class="bi bi-check-lg me-2"></i>
                                                บันทึกและส่ง
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Document Information -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">ข้อมูลเอกสาร</h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="doc-number" class="form-label small fw-medium">เลขที่หนังสือ</label>
                                            <input type="text" class="form-control" id="doc-number" placeholder="ระบุเลขที่หนังสือ">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="doc-date" class="form-label small fw-medium">วันที่</label>
                                            <input type="date" class="form-control" id="doc-date">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="doc-type" class="form-label small fw-medium">ประเภทเอกสาร</label>
                                            <select class="form-select" id="doc-type">
                                                <option>หนังสือภายนอก</option>
                                                <option>หนังสือภายใน</option>
                                                <option>หนังสือประทับตรา</option>
                                                <option>หนังสือสั่งการ</option>
                                                <option>หนังสือประชาสัมพันธ์</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="doc-urgency" class="form-label small fw-medium">ความเร่งด่วน</label>
                                            <select class="form-select" id="doc-urgency">
                                                <option>ปกติ</option>
                                                <option>ด่วน</option>
                                                <option>ด่วนมาก</option>
                                                <option>ด่วนที่สุด</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="doc-subject" class="form-label small fw-medium">เรื่อง</label>
                                            <input type="text" class="form-control" id="doc-subject" placeholder="ระบุชื่อเรื่อง">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="doc-notes" class="form-label small fw-medium">หมายเหตุ</label>
                                            <textarea class="form-control" id="doc-notes" rows="3" placeholder="ระบุหมายเหตุ (ถ้ามี)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php
$js = <<< JS

  // Global variables
        let currentPdf = null;
        let currentPage = 1;
        let totalPages = 0;
        let canvas = null;
        let selectedStamp = null;
        let stampColor = "#B22234";
        let stampSize = 100;
        let stampRotation = 0;
        let stampOpacity = 1;
        let fabricCanvas = null;
        let pdfHistory = [];
        let historyIndex = -1;
        
        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeUploadZone();
            initializeStampLibrary();
            initializeStampCustomization();
            initializeCanvasControls();
            initializeActionButtons();
        });
        
        // Initialize the upload zone
        function initializeUploadZone() {
            const uploadZone = document.getElementById('upload-zone');
            const fileInput = document.getElementById('file-input');
            
            uploadZone.addEventListener('click', function() {
                fileInput.click();
            });
            
            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadZone.classList.add('border-primary', 'bg-light');
            });
            
            uploadZone.addEventListener('dragleave', function() {
                uploadZone.classList.remove('border-primary', 'bg-light');
            });
            
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadZone.classList.remove('border-primary', 'bg-light');
                
                if (e.dataTransfer.files.length) {
                    handleFileUpload(e.dataTransfer.files[0]);
                }
            });
            
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length) {
                    handleFileUpload(fileInput.files[0]);
                }
            });
        }
        
        // Handle file upload
        function handleFileUpload(file) {
            if (file.type !== 'application/pdf') {
                document.getElementById('upload-error').classList.remove('d-none');
                setTimeout(() => {
                    document.getElementById('upload-error').classList.add('d-none');
                }, 3000);
                return;
            }
            
            // Show progress
            const progressBar = document.getElementById('progress-bar');
            const progressPercent = document.getElementById('progress-percent');
            document.getElementById('upload-progress').classList.remove('d-none');
            
            // Simulate upload progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                progressBar.style.width = `\${progress}%`;
                progressPercent.textContent = `\${progress}%`;
                
                if (progress >= 100) {
                    clearInterval(interval);
                    document.getElementById('upload-progress').classList.add('d-none');
                    document.getElementById('upload-success').classList.remove('d-none');
                    
                    setTimeout(() => {
                        document.getElementById('upload-success').classList.add('d-none');
                    }, 3000);
                    
                    // Load the PDF
                    loadPDF(file);
                }
            }, 200);
        }
        
        // Load PDF file
        function loadPDF(file) {
            const fileReader = new FileReader();
            
            fileReader.onload = function() {
                const typedarray = new Uint8Array(this.result);
                
                // Using PDF.js to render the PDF
                pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                    currentPdf = pdf;
                    totalPages = pdf.numPages;
                    currentPage = 1;
                    
                    // Update page info
                    document.getElementById('page-info').textContent = `หน้า \${currentPage} จาก \${totalPages}`;
                    
                    // Enable navigation buttons if there are multiple pages
                    document.getElementById('prev-page').disabled = currentPage <= 1;
                    document.getElementById('next-page').disabled = currentPage >= totalPages;
                    
                    // Render the first page
                    renderPage(currentPage);
                    
                    // Hide placeholder
                    document.getElementById('document-placeholder').classList.add('d-none');
                    document.getElementById('pdf-canvas').classList.remove('d-none');
                });
            };
            
            fileReader.readAsArrayBuffer(file);
        }
        
        // Render a specific page of the PDF
        function renderPage(pageNumber) {
            currentPdf.getPage(pageNumber).then(function(page) {
                const viewport = page.getViewport({ scale: 1.5 });
                
                // Initialize fabric.js canvas if not already done
                if (!fabricCanvas) {
                    const canvasContainer = document.getElementById('canvas-container');
                    const pdfCanvas = document.getElementById('pdf-canvas');
                    
                    pdfCanvas.width = viewport.width;
                    pdfCanvas.height = viewport.height;
                    
                    fabricCanvas = new fabric.Canvas('pdf-canvas', {
                        width: viewport.width,
                        height: viewport.height
                    });
                    
                    // Make canvas responsive
                    window.addEventListener('resize', function() {
                        resizeCanvas();
                    });
                    
                    resizeCanvas();
                } else {
                    fabricCanvas.setWidth(viewport.width);
                    fabricCanvas.setHeight(viewport.height);
                    fabricCanvas.clear();
                }
                
                // Render the PDF page on a temporary canvas
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = viewport.width;
                tempCanvas.height = viewport.height;
                
                const renderContext = {
                    canvasContext: tempCanvas.getContext('2d'),
                    viewport: viewport
                };
                
                page.render(renderContext).promise.then(function() {
                    // Convert the rendered page to a fabric.js image
                    fabric.Image.fromURL(tempCanvas.toDataURL(), function(img) {
                        img.selectable = false;
                        fabricCanvas.add(img);
                        fabricCanvas.renderAll();
                        
                        // Save initial state to history
                        saveToHistory();
                    });
                });
            });
        }
        
        // Resize canvas to fit container
        function resizeCanvas() {
            if (!fabricCanvas) return;
            
            const canvasContainer = document.getElementById('canvas-container');
            const containerWidth = canvasContainer.clientWidth;
            const containerHeight = canvasContainer.clientHeight;
            
            const canvasWidth = fabricCanvas.getWidth();
            const canvasHeight = fabricCanvas.getHeight();
            
            let scale = Math.min(containerWidth / canvasWidth, containerHeight / canvasHeight);
            
            fabricCanvas.setZoom(scale);
            fabricCanvas.setDimensions({
                width: canvasWidth * scale,
                height: canvasHeight * scale
            });
        }
        
        // Initialize stamp library
        function initializeStampLibrary() {
            const stamps = document.querySelectorAll('.stamp');
            
            stamps.forEach(stamp => {
                stamp.addEventListener('click', function() {
                    // Remove selected class from all stamps
                    stamps.forEach(s => s.classList.remove('selected'));
                    
                    // Add selected class to clicked stamp
                    this.classList.add('selected');
                    
                    // Set selected stamp
                    selectedStamp = this.getAttribute('data-stamp');
                    
                    // Add stamp to canvas if a PDF is loaded
                    if (fabricCanvas && currentPdf) {
                        addStampToCanvas(selectedStamp);
                    }
                });
            });
        }
        
        // Initialize stamp customization controls
        function initializeStampCustomization() {
            // Color selection
            const colorOptions = document.querySelectorAll('.color-option');
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    colorOptions.forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    stampColor = this.getAttribute('data-color');
                    updateSelectedStamp();
                });
            });
            
            // Size adjustment
            document.getElementById('stamp-size').addEventListener('input', function() {
                stampSize = parseInt(this.value);
                updateSelectedStamp();
            });
            
            // Rotation adjustment
            document.getElementById('stamp-rotation').addEventListener('input', function() {
                stampRotation = parseInt(this.value);
                updateSelectedStamp();
            });
            
            // Opacity adjustment
            document.getElementById('stamp-opacity').addEventListener('input', function() {
                stampOpacity = parseInt(this.value) / 100;
                updateSelectedStamp();
            });
        }
        
        // Update the selected stamp on canvas with new settings
        function updateSelectedStamp() {
            if (!fabricCanvas) return;
            
            const activeObject = fabricCanvas.getActiveObject();
            if (activeObject && activeObject.type === 'group' && activeObject.stampType) {
                activeObject.getObjects().forEach(obj => {
                    if (obj.type === 'path' || obj.type === 'text') {
                        obj.set('fill', stampColor);
                    } else if (obj.type === 'circle' || obj.type === 'rect') {
                        obj.set('stroke', stampColor);
                    }
                });
                
                const scale = stampSize / 100;
                activeObject.set({
                    scaleX: scale,
                    scaleY: scale,
                    angle: stampRotation,
                    opacity: stampOpacity
                });
                
                fabricCanvas.renderAll();
                saveToHistory();
            }
        }
        
        // Add stamp to canvas
        function addStampToCanvas(stampType) {
            if (!fabricCanvas) return;
            
            let stampObjects = [];
            const scale = stampSize / 100;
            
            switch (stampType) {
                case 'received':
                    // Create circle
                    const circle = new fabric.Circle({
                        radius: 45,
                        fill: 'transparent',
                        stroke: stampColor,
                        strokeWidth: 2
                    });
                    
                    // Create texts
                    const text1 = new fabric.Text('ลงรับเลขที่', {
                        fontSize: 12,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        top: -10,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    const text2 = new fabric.Text('วันที่', {
                        fontSize: 10,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        top: 5,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    const text3 = new fabric.Text('เวลา', {
                        fontSize: 10,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        top: 20,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    stampObjects = [circle, text1, text2, text3];
                    break;
                    
                case 'urgent':
                    // Create rectangle
                    const rect = new fabric.Rect({
                        width: 90,
                        height: 90,
                        fill: 'transparent',
                        stroke: stampColor,
                        strokeWidth: 2,
                        rx: 5,
                        ry: 5
                    });
                    
                    // Create text
                    const urgentText = new fabric.Text('ด่วนที่สุด', {
                        fontSize: 16,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        fontWeight: 'bold',
                        top: -10,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    // Create lines
                    const line1 = new fabric.Line([-40, 10, 40, 10], {
                        stroke: stampColor,
                        strokeWidth: 2
                    });
                    
                    const line2 = new fabric.Line([-40, 20, 40, 20], {
                        stroke: stampColor,
                        strokeWidth: 2
                    });
                    
                    stampObjects = [rect, urgentText, line1, line2];
                    break;
                    
                case 'approved':
                    // Create circle
                    const approvedCircle = new fabric.Circle({
                        radius: 45,
                        fill: 'transparent',
                        stroke: stampColor,
                        strokeWidth: 2
                    });
                    
                    // Create texts
                    const approvedText = new fabric.Text('อนุมัติ', {
                        fontSize: 14,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        top: -5,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    const signatureText = new fabric.Text('ลงชื่อ...............', {
                        fontSize: 10,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        top: 15,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    stampObjects = [approvedCircle, approvedText, signatureText];
                    break;
                    
                case 'confidential':
                    // Create rectangle
                    const confRect = new fabric.Rect({
                        width: 90,
                        height: 90,
                        fill: 'transparent',
                        stroke: stampColor,
                        strokeWidth: 2,
                        rx: 5,
                        ry: 5
                    });
                    
                    // Create texts
                    const confText1 = new fabric.Text('ลับ', {
                        fontSize: 14,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        fontWeight: 'bold',
                        top: -10,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    const confText2 = new fabric.Text('เฉพาะ', {
                        fontSize: 14,
                        fill: stampColor,
                        fontFamily: 'Sarabun',
                        fontWeight: 'bold',
                        top: 10,
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    stampObjects = [confRect, confText1, confText2];
                    break;
            }
            
            // Create a group with all stamp objects
            const group = new fabric.Group(stampObjects, {
                left: fabricCanvas.getWidth() / 2,
                top: fabricCanvas.getHeight() / 2,
                scaleX: scale,
                scaleY: scale,
                angle: stampRotation,
                opacity: stampOpacity,
                stampType: stampType
            });
            
            fabricCanvas.add(group);
            fabricCanvas.setActiveObject(group);
            fabricCanvas.renderAll();
            
            // Save to history
            saveToHistory();
        }
        
        // Initialize canvas controls
        function initializeCanvasControls() {
            // Page navigation
            document.getElementById('prev-page').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage(currentPage);
                    
                    // Update page info
                    document.getElementById('page-info').textContent = `หน้า \${currentPage} จาก \${totalPages}`;
                    
                    // Update button states
                    document.getElementById('prev-page').disabled = currentPage <= 1;
                    document.getElementById('next-page').disabled = currentPage >= totalPages;
                }
            });
            
            document.getElementById('next-page').addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage(currentPage);
                    
                    // Update page info
                    document.getElementById('page-info').textContent = `หน้า \${currentPage} จาก \${totalPages}`;
                    
                    // Update button states
                    document.getElementById('prev-page').disabled = currentPage <= 1;
                    document.getElementById('next-page').disabled = currentPage >= totalPages;
                }
            });
            
            // Undo/Redo
            document.getElementById('undo-btn').addEventListener('click', function() {
                undo();
            });
            
            document.getElementById('redo-btn').addEventListener('click', function() {
                redo();
            });
        }
        
        // Save current canvas state to history
        function saveToHistory() {
            if (!fabricCanvas) return;
            
            // If we're not at the end of the history, remove everything after current index
            if (historyIndex < pdfHistory.length - 1) {
                pdfHistory = pdfHistory.slice(0, historyIndex + 1);
            }
            
            // Save current state
            pdfHistory.push(JSON.stringify(fabricCanvas));
            historyIndex = pdfHistory.length - 1;
            
            // Update undo/redo buttons
            updateUndoRedoButtons();
        }
        
        // Undo last action
        function undo() {
            if (historyIndex > 0) {
                historyIndex--;
                loadFromHistory();
            }
        }
        
        // Redo last undone action
        function redo() {
            if (historyIndex < pdfHistory.length - 1) {
                historyIndex++;
                loadFromHistory();
            }
        }
        
        // Load canvas state from history
        function loadFromHistory() {
            if (!fabricCanvas) return;
            
            fabricCanvas.loadFromJSON(pdfHistory[historyIndex], function() {
                fabricCanvas.renderAll();
                updateUndoRedoButtons();
            });
        }
        
        // Update undo/redo button states
        function updateUndoRedoButtons() {
            document.getElementById('undo-btn').disabled = historyIndex <= 0;
            document.getElementById('redo-btn').disabled = historyIndex >= pdfHistory.length - 1;
        }
        
        // Initialize action buttons
        function initializeActionButtons() {
            // Reset button
            document.querySelector('.btn-secondary').addEventListener('click', function() {
                if (fabricCanvas && currentPdf) {
                    // Remove all objects except the background PDF
                    const objects = fabricCanvas.getObjects();
                    if (objects.length > 1) {
                        for (let i = objects.length - 1; i > 0; i--) {
                            fabricCanvas.remove(objects[i]);
                        }
                        fabricCanvas.renderAll();
                        saveToHistory();
                    }
                }
            });
            
            // Cancel button
            document.querySelector('.btn-danger').addEventListener('click', function() {
                if (confirm('คุณต้องการยกเลิกการแก้ไขเอกสารนี้ใช่หรือไม่?')) {
                    // Reset everything
                    if (fabricCanvas) {
                        fabricCanvas.dispose();
                        fabricCanvas = null;
                    }
                    
                    currentPdf = null;
                    currentPage = 1;
                    totalPages = 0;
                    pdfHistory = [];
                    historyIndex = -1;
                    
                    document.getElementById('document-placeholder').classList.remove('d-none');
                    document.getElementById('pdf-canvas').classList.add('d-none');
                    document.getElementById('page-info').textContent = 'หน้า 0 จาก 0';
                    document.getElementById('prev-page').disabled = true;
                    document.getElementById('next-page').disabled = true;
                    document.getElementById('undo-btn').disabled = true;
                    document.getElementById('redo-btn').disabled = true;
                    
                    // Reset file input
                    document.getElementById('file-input').value = '';
                }
            });
            
            // Save button
            document.querySelector('.btn-primary').addEventListener('click', function() {
                if (fabricCanvas && currentPdf) {
                    // Show success message using Bootstrap toast
                    const toastContainer = document.createElement('div');
                    toastContainer.className = 'position-fixed top-0 end-0 p-3';
                    toastContainer.style.zIndex = '1050';
                    
                    const toastContent = `
                        <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="bi bi-check-circle me-2"></i>
                                    บันทึกและส่งเอกสารสำเร็จ
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    `;
                    
                    toastContainer.innerHTML = toastContent;
                    document.body.appendChild(toastContainer);
                    
                    const toastElement = toastContainer.querySelector('.toast');
                    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                    toast.show();
                    
                    // Remove toast container after it's hidden
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastContainer.remove();
                    });
                }
            });
        }

JS;
$this->registerJS($js)
?>