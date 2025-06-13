<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\StampTemplate;

/* @var $this yii\web\View */
/* @var $pdfFile string */

$this->title = 'แก้ไขตราประทับ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบประทับตรา PDF', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$presetTemplates = StampTemplate::getPresetTemplates();

$this->registerCss('
.editor-container {
    height: 100vh;
    overflow: hidden;
}

.toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 15px;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.pdf-viewer {
    height: calc(100vh - 200px);
    overflow: auto;
    background: #e9ecef;
    position: relative;
}

.pdf-page {
    background: white;
    margin: 20px auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    position: relative;
    min-height: 800px;
    width: 595px;
}

.stamp-item {
    position: absolute;
    cursor: move;
    user-select: none;
    padding: 4px 8px;
    border: 2px dashed transparent;
    min-width: 50px;
    text-align: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.stamp-item:hover,
.stamp-item.selected {
    border-color: #007bff;
    background: rgba(0,123,255,0.1);
}

.stamp-item.dragging {
    z-index: 1000;
    transform: scale(1.05);
}

.properties-panel {
    background: white;
    border-left: 1px solid #dee2e6;
    height: calc(100vh - 200px);
    overflow-y: auto;
    padding: 20px;
}

.preset-templates {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.template-item {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    padding: 10px 5px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
    font-weight: bold;
}

.template-item:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.template-item.approved { color: #00AA00; }
.template-item.rejected { color: #FF0000; }
.template-item.pending { color: #FF8800; }
.template-item.confidential { color: #FF0000; background: #FFEEEE; }
.template-item.draft { color: #888888; }
.template-item.copy { color: #0066CC; }

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.btn-action {
    margin: 0 5px;
}

.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-preview {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.zoom-controls {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.9);
    border-radius: 6px;
    padding: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    display: none;
}

.loading-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
}

@media (max-width: 768px) {
    .pdf-page {
        width: 90%;
        transform: scale(0.8);
    }
    
    .properties-panel {
        position: fixed;
        right: -300px;
        top: 0;
        width: 300px;
        z-index: 1001;
        transition: right 0.3s ease;
    }
    
    .properties-panel.show {
        right: 0;
    }
}
');

?>

<div class="editor-container">
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="btn-group">
                    <?= Html::button('<i class="fas fa-plus"></i> เพิ่มตรา', [
                        'id' => 'add-stamp',
                        'class' => 'btn btn-primary btn-sm'
                    ]) ?>
                    <?= Html::button('<i class="fas fa-trash"></i> ลบ', [
                        'id' => 'delete-stamp',
                        'class' => 'btn btn-danger btn-sm'
                    ]) ?>
                    <?= Html::button('<i class="fas fa-copy"></i> คัดลอก', [
                        'id' => 'copy-stamp',
                        'class' => 'btn btn-secondary btn-sm'
                    ]) ?>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <div class="btn-group">
                    <?= Html::button('<i class="fas fa-download"></i> ดาวน์โหลด PDF', [
                        'id' => 'download-pdf',
                        'class' => 'btn btn-success'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> กลับ', ['index'], [
                        'class' => 'btn btn-outline-secondary'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row no-gutters">
        <!-- PDF Viewer -->
        <div class="col-lg-8">
            <div class="pdf-viewer">
                <!-- Zoom Controls -->
                <div class="zoom-controls">
                    <div class="btn-group-vertical btn-group-sm">
                        <button type="button" id="zoom-in" class="btn btn-outline-secondary">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" id="zoom-out" class="btn btn-outline-secondary">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" id="zoom-fit" class="btn btn-outline-secondary">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                    </div>
                    <div class="text-center mt-2">
                        <small id="zoom-level">100%</small>
                    </div>
                </div>

                <!-- PDF Page -->
                <div class="pdf-page" id="pdf-page-1">
                    <!-- PDF content will be loaded here -->
                    <div style="padding: 40px; color: #999; text-align: center; font-size: 18px;">
                        <i class="fas fa-file-pdf fa-3x mb-3"></i><br>
                        กำลังโหลด PDF...
                    </div>
                </div>

                <!-- Page Navigation -->
                <div class="text-center mt-3 mb-3">
                    <div class="btn-group">
                        <button type="button" id="prev-page" class="btn btn-outline-secondary">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="btn btn-outline-secondary">
                            หน้า <span id="current-page">1</span> จาก <span id="total-pages">1</span>
                        </span>
                        <button type="button" id="next-page" class="btn btn-outline-secondary">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties Panel -->
        <div class="col-lg-4">
            <div class="properties-panel">
                <h5><i class="fas fa-cog"></i> คุณสมบัติตราประทับ</h5>

                <!-- Preset Templates -->
                <div class="mb-4">
                    <h6>เทมเพลตสำเร็จรูป</h6>
                    <div class="preset-templates">
                        <?php foreach ($presetTemplates as $key => $template): ?>
                            <div class="template-item <?= $key ?>" data-template="<?= $key ?>">
                                <?= Html::encode($template['text']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Stamp Properties Form -->
                <form id="stamp-properties-form">
                    <div class="form-group">
                        <label for="stamp-text">ข้อความ</label>
                        <input type="text" id="stamp-text" class="form-control" placeholder="ใส่ข้อความ">
                    </div>

                    <div class="form-group">
                        <label for="stamp-color">สีตัวอักษร</label>
                        <div class="color-picker-wrapper">
                            <input type="color" id="stamp-color" class="form-control" value="#FF0000">
                            <div id="color-preview" class="color-preview" style="background-color: #FF0000;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="stamp-fontsize">ขนาดตัวอักษร</label>
                        <div class="d-flex align-items-center">
                            <input type="range" id="stamp-fontsize" class="form-control-range flex-grow-1" 
                                   min="8" max="72" value="14">
                            <span id="fontsize-value" class="ml-2 badge badge-secondary">14</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="stamp-rotation">การหมุน</label>
                        <div class="d-flex align-items-center">
                            <input type="range" id="stamp-rotation" class="form-control-range flex-grow-1" 
                                   min="-180" max="180" value="0">
                            <span id="rotation-value" class="ml-2 badge badge-secondary">0°</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="stamp-opacity">ความทึบแสง</label>
                        <div class="d-flex align-items-center">
                            <input type="range" id="stamp-opacity" class="form-control-range flex-grow-1" 
                                   min="10" max="100" value="100">
                            <span id="opacity-value" class="ml-2 badge badge-secondary">100%</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="stamp-bold" class="form-check-input">
                                <label class="form-check-label" for="stamp-bold">
                                    <strong>ตัวหนา</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="stamp-italic" class="form-check-input">
                                <label class="form-check-label" for="stamp-italic">
                                    <em>ตัวเอียง</em>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="stamp-border" class="form-check-input" checked>
                                <label class="form-check-label" for="stamp-border">
                                    กรอบ
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="stamp-background" class="form-check-input">
                                <label class="form-check-label" for="stamp-background">
                                    พื้นหลัง
                                </label>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Tips -->
                <div class="alert alert-info mt-3">
                    <small>
                        <strong>เคล็ดลับ:</strong><br>
                        • คลิกที่ตราประทับเพื่อเลือก<br>
                        • ลากเพื่อเคลื่อนย้ายตำแหน่ง<br>
                        • ใช้ Ctrl+C เพื่อคัดลอกด่วน
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay">
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">กำลังโหลด...</span>
        </div>
        <div class="mt-3">กำลังสร้าง PDF...</div>
    </div>
</div>

<script>
<?php
// เพิ่ม CSRF token
echo 'var csrfToken = "' . Yii::$app->request->csrfToken . '";';
echo '$("meta[name=csrf-token]").attr("content", csrfToken);';
?>

$this->registerJs('
var PdfStampEditor = {
    stamps: [],
    selectedStamp: null,
    currentPage: 1,
    totalPages: 1,
    zoomLevel: 1,
    
    init: function() {
        this.bindEvents();
        this.loadPresetTemplates();
        this.setupDragAndDrop();
    },
    
    bindEvents: function() {
        var self = this;
        
        // Add new stamp
        $("#add-stamp").click(function() {
            self.addNewStamp();
        });
        
        // Delete selected stamp
        $("#delete-stamp").click(function() {
            self.deleteSelectedStamp();
        });
        
        // Copy selected stamp
        $("#copy-stamp").click(function() {
            self.copySelectedStamp();
        });
        
        // Download PDF
        $("#download-pdf").click(function() {
            self.downloadPdf();
        });
        
        // Property changes
        $("#stamp-text").on("input", function() {
            self.updateSelectedStamp("text", $(this).val());
        });
        
        $("#stamp-color").on("change", function() {
            self.updateSelectedStamp("color", $(this).val());
            $("#color-preview").css("background-color", $(this).val());
        });
        
        $("#stamp-fontsize").on("input", function() {
            self.updateSelectedStamp("fontSize", $(this).val());
            $("#fontsize-value").text($(this).val());
        });
        
        $("#stamp-rotation").on("input", function() {
            self.updateSelectedStamp("rotation", $(this).val());
            $("#rotation-value").text($(this).val() + "°");
        });
        
        $("#stamp-opacity").on("input", function() {
            self.updateSelectedStamp("opacity", $(this).val());
            $("#opacity-value").text($(this).val() + "%");
        });
        
        $("#stamp-bold").change(function() {
            self.updateSelectedStamp("isBold", $(this).is(":checked"));
        });
        
        $("#stamp-italic").change(function() {
            self.updateSelectedStamp("isItalic", $(this).is(":checked"));
        });
        
        $("#stamp-border").change(function() {
            self.updateSelectedStamp("hasBorder", $(this).is(":checked"));
        });
        
        $("#stamp-background").change(function() {
            self.updateSelectedStamp("hasBackground", $(this).is(":checked"));
        });
        
        // Zoom controls
        $("#zoom-in").click(function() {
            self.zoomIn();
        });
        
        $("#zoom-out").click(function() {
            self.zoomOut();
        });
        
        $("#zoom-fit").click(function() {
            self.zoomFit();
        });
        
        // Page navigation
        $("#prev-page").click(function() {
            self.prevPage();
        });
        
        $("#next-page").click(function() {
            self.nextPage();
        });
    },
    
    loadPresetTemplates: function() {
        var self = this;
        var templates = ' . json_encode($presetTemplates) . ';
        
        $(".template-item").click(function() {
            var templateKey = $(this).data("template");
            if (templates[templateKey]) {
                self.createStampFromTemplate(templates[templateKey]);
            }
        });
    },
    
    setupDragAndDrop: function() {
        var self = this;
        var isDragging = false;
        var currentElement = null;
        var offset = { x: 0, y: 0 };
        
        $(document).on("mousedown", ".stamp-item", function(e) {
            e.preventDefault();
            isDragging = true;
            currentElement = $(this);
            
            var pos = currentElement.position();
            offset.x = e.pageX - pos.left;
            offset.y = e.pageY - pos.top;
            
            currentElement.addClass("dragging");
            self.selectStamp(currentElement.data("stamp-id"));
        });
        
        $(document).on("mousemove", function(e) {
            if (isDragging && currentElement) {
                var container = $(".pdf-page");
                var containerOffset = container.offset();
                
                var x = e.pageX - containerOffset.left - offset.x;
                var y = e.pageY - containerOffset.top - offset.y;
                
                // Boundaries
                x = Math.max(0, Math.min(x, container.width() - currentElement.outerWidth()));
                y = Math.max(0, Math.min(y, container.height() - currentElement.outerHeight()));
                
                currentElement.css({
                    left: x + "px",
                    top: y + "px"
                });
                
                // Update stamp data
                var stampId = currentElement.data("stamp-id");
                var stamp = self.findStampById(stampId);
                if (stamp) {
                    stamp.x = x;
                    stamp.y = y;
                }
            }
        });
        
        $(document).on("mouseup", function() {
            if (isDragging) {
                isDragging = false;
                if (currentElement) {
                    currentElement.removeClass("dragging");
                    currentElement = null;
                }
            }
        });
    },
    
    addNewStamp: function() {
        var stamp = {
            id: "stamp_" + Date.now(),
            text: "NEW STAMP",
            color: "#FF0000",
            fontSize: 14,
            isBold: true,
            isItalic: false,
            hasBorder: true,
            hasBackground: false,
            rotation: 0,
            opacity: 100,
            x: 100,
            y: 100,
            page: this.currentPage
        };
        
        this.stamps.push(stamp);
        this.renderStamp(stamp);
        this.selectStamp(stamp.id);
    },
    
    createStampFromTemplate: function(template) {
        var stamp = $.extend({}, template, {
            id: "stamp_" + Date.now(),
            x: 100,
            y: 100,
            page: this.currentPage
        });
        
        this.stamps.push(stamp);
        this.renderStamp(stamp);
        this.selectStamp(stamp.id);
    },
    
    renderStamp: function(stamp) {
        var style = this.getStampStyle(stamp);
        var html = "<div class=\"stamp-item\" data-stamp-id=\"" + stamp.id + "\" style=\"" + style + "\">" + 
                   stamp.text + "</div>";
        
        $(".pdf-page").append(html);
    },
    
    getStampStyle: function(stamp) {
        var styles = [];
        
        styles.push("color: " + stamp.color);
        styles.push("font-size: " + stamp.fontSize + "px");
        styles.push("font-weight: " + (stamp.isBold ? "bold" : "normal"));
        styles.push("font-style: " + (stamp.isItalic ? "italic" : "normal"));
        styles.push("left: " + stamp.x + "px");
        styles.push("top: " + stamp.y + "px");
        styles.push("opacity: " + (stamp.opacity / 100));
        
        if (stamp.rotation != 0) {
            styles.push("transform: rotate(" + stamp.rotation + "deg)");
        }
        
        if (stamp.hasBorder) {
            styles.push("border: 2px solid " + stamp.color);
        }
        
        if (stamp.hasBackground) {
            styles.push("background-color: rgba(255,255,255,0.8)");
        }
        
        return styles.join("; ");
    },
    
    selectStamp: function(stampId) {
        $(".stamp-item").removeClass("selected");
        var element = $("[data-stamp-id=\"" + stampId + "\"]");
        element.addClass("selected");
        
        this.selectedStamp = this.findStampById(stampId);
        this.updatePropertiesPanel();
    },
    
    updatePropertiesPanel: function() {
        if (!this.selectedStamp) return;
        
        $("#stamp-text").val(this.selectedStamp.text);
        $("#stamp-color").val(this.selectedStamp.color);
        $("#color-preview").css("background-color", this.selectedStamp.color);
        $("#stamp-fontsize").val(this.selectedStamp.fontSize);
        $("#fontsize-value").text(this.selectedStamp.fontSize);
        $("#stamp-rotation").val(this.selectedStamp.rotation);
        $("#rotation-value").text(this.selectedStamp.rotation + "°");
        $("#stamp-opacity").val(this.selectedStamp.opacity);
        $("#opacity-value").text(this.selectedStamp.opacity + "%");
        $("#stamp-bold").prop("checked", this.selectedStamp.isBold);
        $("#stamp-italic").prop("checked", this.selectedStamp.isItalic);
        $("#stamp-border").prop("checked", this.selectedStamp.hasBorder);
        $("#stamp-background").prop("checked", this.selectedStamp.hasBackground);
    },
    
    updateSelectedStamp: function(property, value) {
        if (!this.selectedStamp) return;
        
        this.selectedStamp[property] = value;
        this.refreshStampDisplay(this.selectedStamp.id);
    },
    
    refreshStampDisplay: function(stampId) {
        var stamp = this.findStampById(stampId);
        if (!stamp) return;
        
        var element = $("[data-stamp-id=\"" + stampId + "\"]");
        element.attr("style", this.getStampStyle(stamp));
        element.text(stamp.text);
    },
    
    deleteSelectedStamp: function() {
        if (!this.selectedStamp) return;
        
        if (confirm("คุณต้องการลบตราประทับนี้หรือไม่?")) {
            $("[data-stamp-id=\"" + this.selectedStamp.id + "\"]").remove();
            this.stamps = this.stamps.filter(s => s.id !== this.selectedStamp.id);
            this.selectedStamp = null;
            this.updatePropertiesPanel();
        }
    },
    
    copySelectedStamp: function() {
        if (!this.selectedStamp) return;
        
        var newStamp = $.extend({}, this.selectedStamp, {
            id: "stamp_" + Date.now(),
            x: this.selectedStamp.x + 20,
            y: this.selectedStamp.y + 20
        });
        
        this.stamps.push(newStamp);
        this.renderStamp(newStamp);
        this.selectStamp(newStamp.id);
    },
    
    downloadPdf: function() {
        if (this.stamps.length === 0) {
            alert("กรุณาเพิ่มตราประทับก่อนดาวน์โหลด");
            return;
        }
        
        $(".loading-overlay").show();
        
        $.ajax({
            url: "' . Url::to(['download']) . '",
            method: "POST",
            data: {
                stamps: this.stamps,
                _csrf: $("meta[name=csrf-token]").attr("content")
            },
            success: function(response) {
                $(".loading-overlay").hide();
                
                if (response.success) {
                    var link = document.createElement("a");
                    link.href = response.downloadUrl;
                    link.download = "stamped_document.pdf";
                    link.click();
                } else {
                    alert("เกิดข้อผิดพลาด: " + response.message);
                }
            },
            error: function() {
                $(".loading-overlay").hide();
                alert("เกิดข้อผิดพลาดในการสร้าง PDF");
            }
        });
    },
    
    findStampById: function(id) {
        return this.stamps.find(s => s.id === id);
    },
    
    zoomIn: function() {
        this.zoomLevel = Math.min(this.zoomLevel * 1.2, 3);
        this.applyZoom();
    },
    
    zoomOut: function() {
        this.zoomLevel = Math.max(this.zoomLevel / 1.2, 0.3);
        this.applyZoom();
    },
    
    zoomFit: function() {
        this.zoomLevel = 1;
        this.applyZoom();
    },
    
    applyZoom: function() {
        $(".pdf-page").css("transform", "scale(" + this.zoomLevel + ")");
        $("#zoom-level").text(Math.round(this.zoomLevel * 100) + "%");
    }
};

$(document).ready(function() {
    PdfStampEditor.init();
});
');