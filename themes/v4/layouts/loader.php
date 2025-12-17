
<style>

    /* Table Loading Overlay */
    .table-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        /* background-color: rgba(255, 255, 255, 0.8); */
        background-color: rgba(255, 255, 255, 0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 100;
        border-radius: 8px;
        backdrop-filter: blur(2px);
        transition: all 0.3s ease;
    }

    .table-loading-content {
        text-align: center;
        padding: 1rem;
    }

    .table-loading-spinner {
        width: 50px;
        height: 50px;
        margin-bottom: 1rem;
        margin-left: auto;
        margin-right: auto;
    }

    .table-loading-spinner .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.25rem;
        color: var(--primary-color);
    }

    .table-loading-message {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .table-progress {
        height: 6px;
        border-radius: 3px;
        margin-bottom: 0.5rem;
        width: 150px;
        background-color: #e9ecef;
    }

    .table-progress-bar {
        background-color: var(--primary-color);
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    /* Row loading styles */
    tr.loading-row {
        position: relative;
        background-color: rgba(236, 240, 241, 0.5);
    }

    tr.loading-row td {
        position: relative;
        color: transparent;
    }

    tr.loading-row td:after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
    }

    @keyframes loading-shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    /* Cell loading indicator */
    .cell-loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(52, 152, 219, 0.2);
        border-radius: 50%;
        border-top-color: var(--primary-color);
        animation: spin 1s infinite linear;
        vertical-align: middle;
        margin-right: 5px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

</style>
<!-- Table Loading Overlay -->
        <div class="table-loading-overlay" id="tableLoading1">
            <div class="table-loading-content">
                <div class="table-loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>
                <p class="table-loading-message">กำลังโหลดข้อมูล...</p>
                <div class="progress" style="height:8px">
              <div id="tableProgress1" class="progress-bar" role="progressbar"
                style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>

                <div class="table-loading-status" id="tableStatus1">45%</div>
            </div>
        </div>
<?php

use yii\web\View;

$js = <<< JS

//ส่วนการ load overlay
        tableLoading1.style.display = 'none';
                function showTableLoading() {
                    let progress = 0;
                    const tableLoading1 = document.getElementById('tableLoading1');
                    const tableProgress1 = document.getElementById('tableProgress1');
                    const tableStatus1 = document.getElementById('tableStatus1');

                    tableProgress1.style.width = '0%';
                    tableProgress1.setAttribute('aria-valuenow', '0');
                    tableStatus1.textContent = '0%';
                    updateProgressColorBar(0);

                    tableLoading1.style.display = 'flex';

                    const interval = setInterval(function () {
                        progress += Math.floor(Math.random() * 15) + 5;
                        if (progress > 100) progress = 100;

                        tableProgress1.style.width = progress + '%';
                        tableProgress1.setAttribute('aria-valuenow', progress);
                        tableStatus1.textContent = progress + '%';

                        updateProgressColorBar(progress);

                        if (progress === 100) {
                            clearInterval(interval);
                            // setTimeout(hideTableLoading, 300);
                        }
                    }, 300);
                }
                            
            function updateProgressColorBar(progress) {
                const el = document.getElementById('tableProgress1');
                
                // ลบคลาสเดิมก่อน
                el.classList.remove('bg-danger', 'bg-warning', 'bg-primary', 'bg-success');
                el.classList.add('bg-primary');  // น้ำเงิน

                // เพิ่มคลาสใหม่ตามช่วง progress
                // if (progress < 30) {
                //     el.classList.add('bg-danger');   // แดง
                // } else if (progress < 60) {
                //     el.classList.add('bg-warning');  // เหลือง
                // } else if (progress < 90) {
                //     el.classList.add('bg-primary');  // น้ำเงิน
                // } else {
                //     el.classList.add('bg-success');  // เขียว
                // }
            }

            function hideTableLoading() {
                tableLoading1.style.opacity = '0';
                setTimeout(function() {
                    tableLoading1.style.display = 'none';
                    tableLoading1.style.opacity = '1';
                }, 300);
            }
            // จบส่วนการ load overlay
JS;
$this->registerJs($js,View::POS_END);
?>