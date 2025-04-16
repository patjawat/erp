<?php
$_title = $title ?? '';
$_count = $count ?? 0;
$_progress = $progress ?? 0;
$_icon = $icon ?? '<i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>';
?>
<div class="card hover-card-under ">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold"><?=$_count?></span>
                        <div class="relative">
                            <?=$_icon?>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0 align-items-center">
                            <span class="text-primary fs-6"><?=$_title?></span>
                        </div>
                        <div class="progress-bar bg-primary rounded-pill mt-2" style="height: 5px;width:<?=$_progress?>%"></div>
                </div>
            </div>