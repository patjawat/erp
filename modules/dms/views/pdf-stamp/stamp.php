
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 20px; 
            text-align: center; 
        }
        .controls { 
            padding: 20px; 
            background: #f8f9fa; 
            border-bottom: 1px solid #e9ecef; 
        }
        .control-group { 
            display: inline-block; 
            margin-right: 20px; 
            margin-bottom: 10px; 
        }
        .control-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #495057; 
        }
        .control-group input, .control-group select { 
            padding: 8px 12px; 
            border: 1px solid #ced4da; 
            border-radius: 6px; 
            font-size: 14px; 
        }
        .btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 600; 
            transition: all 0.3s ease; 
            margin-right: 10px; 
        }
        .btn-primary { 
            background: #007bff; 
            color: white; 
        }
        .btn-primary:hover { 
            background: #0056b3; 
            transform: translateY(-1px); 
        }
        .btn-success { 
            background: #28a745; 
            color: white; 
        }
        .btn-success:hover { 
            background: #1e7e34; 
            transform: translateY(-1px); 
        }
        .btn-danger { 
            background: #dc3545; 
            color: white; 
        }
        .btn-danger:hover { 
            background: #c82333; 
            transform: translateY(-1px); 
        }
        .workspace { 
            padding: 20px; 
            text-align: center; 
        }
        #pdfCanvas { 
            border: 2px solid #dee2e6; 
            border-radius: 8px; 
            margin: 20px 0; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .file-input-wrapper { 
            position: relative; 
            display: inline-block; 
            overflow: hidden; 
            background: #6c757d; 
            color: white; 
            border-radius: 6px; 
            padding: 10px 20px; 
            cursor: pointer; 
            transition: background 0.3s ease; 
        }
        .file-input-wrapper:hover { 
            background: #5a6268; 
        }
        .file-input-wrapper input[type=file] { 
            position: absolute; 
            left: -9999px; 
        }
        .stamp-preview { 
            background: #e9ecef; 
            border: 2px dashed #adb5bd; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 20px 0; 
            text-align: center; 
            min-height: 100px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #6c757d; 
        }
        .instructions { 
            background: #d1ecf1; 
            border: 1px solid #bee5eb; 
            border-radius: 6px; 
            padding: 15px; 
            margin: 20px 0; 
            color: #0c5460; 
        }
        .zoom-controls { 
            margin: 10px 0; 
        }
        .page-controls { 
            margin: 15px 0; 
        }
        .page-info { 
            font-weight: 600; 
            color: #495057; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PDF Text Stamp Tool</h1>
            <p>เครื่องมือประทับตราข้อความบน PDF แบบลากวาง</p>
        </div>
        
        <div class="controls">
            <div class="control-group">
                <label>เลือกไฟล์ PDF:</label>
                <div class="file-input-wrapper">
                    <input type="file" id="pdfFile" accept=".pdf">
                    เลือกไฟล์ PDF
                </div>
            </div>
            
            <div class="control-group">
                <label>ข้อความที่ต้องการประทับ:</label>
                <input type="text" id="stampText" placeholder="ป้อนข้อความที่ต้องการ" value="ตัวอย่าง">
            </div>
            
            <div class="control-group">
                <label>ขนาดตัวอักษร:</label>
                <input type="number" id="fontSize" min="10" max="100" value="20">
            </div>
            
            <div class="control-group">
                <label>สีข้อความ:</label>
                <input type="color" id="textColor" value="#ff0000">
            </div>
            
            <div class="control-group">
                <label>ความโปร่งใส:</label>
                <select id="opacity">
                    <option value="1">100%</option>
                    <option value="0.8">80%</option>
                    <option value="0.6" selected>60%</option>
                    <option value="0.4">40%</option>
                    <option value="0.2">20%</option>
                </select>
            </div>
            
            <div class="control-group">
                <button class="btn btn-primary" onclick="addStamp()">เพิ่มตรายาง</button>
                <button class="btn btn-danger" onclick="clearStamps()">ล้างตรา</button>
                <button class="btn btn-success" onclick="downloadPDF()">ดาวน์โหลด PDF</button>
            </div>
        </div>
        
        <div class="workspace">
            <div class="instructions">
                <strong>วิธีใช้:</strong> 
                1. เลือกไฟล์ PDF 
                2. ป้อนข้อความที่ต้องการประทับ 
                3. คลิก "เพิ่มตรายาง" 
                4. ลากวางตรายางไปยังตำแหน่งที่ต้องการ 
                5. คลิก "ดาวน์โหลด PDF" เพื่อบันทึก
            </div>
            
            <div class="zoom-controls">
                <button class="btn btn-primary" onclick="zoomIn()">ขยาย</button>
                <button class="btn btn-primary" onclick="zoomOut()">ย่อ</button>
                <button class="btn btn-primary" onclick="resetZoom()">รีเซ็ตการขยาย</button>
            </div>
            
            <div class="page-controls">
                <button class="btn btn-primary" onclick="prevPage()">หน้าก่อนหน้า</button>
                <span class="page-info">หน้า <span id="pageNum">1</span> จาก <span id="pageCount">1</span></span>
                <button class="btn btn-primary" onclick="nextPage()">หน้าถัดไป</button>
            </div>
            
            <canvas id="pdfCanvas" width="800" height="600"></canvas>
        </div>
    </div>

<?php
use yii\web\View;
$js = <<< JS
    // PDF.js setup
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let scale = 1.0;
    let canvas, ctx, fabricCanvas;
    let stamps = [];

    // Initialize
    $(function() {
        canvas = document.getElementById('pdfCanvas');
        ctx = canvas.getContext('2d');

        // Initialize Fabric.js canvas
        fabricCanvas = new fabric.Canvas('pdfCanvas');
        fabricCanvas.setWidth(800);
        fabricCanvas.setHeight(600);

        // PDF file input handler
        $('#pdfFile').on('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/pdf') {
                const fileReader = new FileReader();
                fileReader.onload = function() {
                    loadPDF(new Uint8Array(this.result));
                };
                fileReader.readAsArrayBuffer(file);
            }
        });
    });

    function loadPDF(data) {
        pdfjsLib.getDocument(data).promise.then(function(pdf) {
            pdfDoc = pdf;
            $('#pageCount').text(pdf.numPages);
            renderPage(pageNum);
        });
    }

    function renderPage(num) {
        pageRendering = true;

        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });

            // Update canvas size
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            fabricCanvas.setWidth(viewport.width);
            fabricCanvas.setHeight(viewport.height);

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            const renderTask = page.render(renderContext);
            renderTask.promise.then(function() {
                pageRendering = false;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }

                // Re-render fabric canvas on top
                fabricCanvas.renderAll();
            });
        });

        $('#pageNum').text(num);
    }

    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    function prevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function nextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    function zoomIn() {
        scale *= 1.2;
        renderPage(pageNum);
    }

    function zoomOut() {
        scale /= 1.2;
        renderPage(pageNum);
    }

    function resetZoom() {
        scale = 1.0;
        renderPage(pageNum);
    }

    function addStamp() {
        const text = $('#stampText').val();
        const fontSize = parseInt($('#fontSize').val());
        const color = $('#textColor').val();
        const opacity = parseFloat($('#opacity').val());

        if (!$.trim(text)) {
            alert('กรุณาป้อนข้อความที่ต้องการประทับ');
            return;
        }

        // Create text object
        const textObj = new fabric.Text(text, {
            left: 100,
            top: 100,
            fontFamily: 'Arial, sans-serif',
            fontSize: fontSize,
            fill: color,
            opacity: opacity,
            angle: 0,
            selectable: true,
            moveable: true,
            hasControls: true,
            hasBorders: true,
            borderColor: '#007bff',
            cornerColor: '#007bff',
            cornerSize: 8
        });

        // Add to canvas
        fabricCanvas.add(textObj);
        fabricCanvas.setActiveObject(textObj);
        fabricCanvas.renderAll();

        // Store stamp info
        stamps.push({
            page: pageNum,
            object: textObj,
            text: text,
            fontSize: fontSize,
            color: color,
            opacity: opacity
        });
    }

    function clearStamps() {
        fabricCanvas.clear();
        stamps = [];
        if (pdfDoc) {
            renderPage(pageNum);
        }
    }

    function downloadPDF() {
        if (!pdfDoc) {
            alert('กรุณาเลือกไฟล์ PDF ก่อน');
            return;
        }

        // Get canvas as image
        const dataURL = fabricCanvas.toDataURL({
            format: 'png',
            quality: 1
        });

        // Create download link
        const link = document.createElement('a');
        link.download = 'stamped-document.png';
        link.href = dataURL;
        link.click();

        alert('ดาวน์โหลดเสร็จสิ้น!หมายเหตุ: ในการใช้งานจริงกับ Yii2 คุณจะต้องส่งข้อมูลตำแหน่งและข้อความไปยัง PHP เพื่อประมวลผลด้วย library เช่น TCPDF หรือ FPDF');
    }

    // Handle canvas resize
    $(window).on('resize', function() {
        if (pdfDoc) {
            renderPage(pageNum);
        }
    });

JS;
$this->registerJS($js,View::POS_END);
?>
