<?php
/** โครงรอโหลด KPI ทาง Ajax — โครงสอดคล้อง kpi_card มาตรฐาน (Bootstrap 5) */
?>
<div class="row g-3 mt-1" role="status" aria-live="polite" aria-busy="true">
    <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="col-6 col-xl-3">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 placeholder-glow">
                        <div class="d-flex flex-column gap-3 flex-grow-1">
                            <span class="placeholder col-5 placeholder-lg"></span>
                            <span class="placeholder col-10 col-md-9"></span>
                        </div>
                        <div class="rounded-pill p-3 bg-secondary bg-opacity-10 flex-shrink-0 opacity-50" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>
