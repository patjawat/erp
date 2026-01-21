<?php
use app\modules\hr\models\Employees;

$listsMemberTeam = Employees::find()->where(['department' => $me->department])->all();
?>

<section class="mt-5">
            <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center"
                        style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-inbox-icon lucide-inbox">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                            <path
                                d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;"><?= $me->departmentName() ?></h3>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">
                            จำนวนสมาชิก <?=count($listsMemberTeam)?> คน
                        </p>
                    </div>
                </div>
            </div>
<div class="d-flex flex-column g-3 gap-3 ms-2">

    <?php foreach($listsMemberTeam as $item):?>
        
        <div class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
              <?=$item->getAvatar(false)?>
        </div>
        <?php endforeach;?>
    </div>
      </section>
