<?php
/** โครงรอโหลด KPI ทาง Ajax — Bootstrap 5 เท่านั้น */
?>
<div class="row g-3 mt-1" role="status" aria-live="polite" aria-busy="true">
    <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-5 mb-2 d-block placeholder-lg"></span>
                        <span class="placeholder col-10 col-md-8 d-block"></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>
