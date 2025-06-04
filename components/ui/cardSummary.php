<?php
$_title = $title ?? '';
$_color = $color ?? 'ligth';
$_count = $count ?? 0;
$_progress = $progress ?? 0;
$_icon = $icon ?? '<i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>';
?>

                        <div class="card border border-5 border-<?=$_color?> border-end-0 border-top-0 border-bottom-0">
                            <div class="card-body stat-card <?=$_color?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1"><?=$_title?></h6>
                                        <h1 class="mb-0"><?=$_count?></h1>
                                        <!-- <small class="text-success"><i class="bi bi-arrow-up"></i> ลาแล้ว 3 วัน</small> -->
                                    </div>
                                    <div class="bg-<?=$_color?> bg-opacity-10 text-<?=$_color?> p-3 rounded-pill bg-<?=$_color?>">
                                       <?=$_icon?>
                                    </div>
                                </div>
                                <div class="progress-bar bg-<?=$_color?> rounded-pill mt-2" style="height: 5px;width:<?=$_progress?>%"></div>
                            </div>
                        </div>
