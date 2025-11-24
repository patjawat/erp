 <div class="d-flex align-items-center">
     <?php // $model->userRequest()['img'] ?? '';?>
            <div class="avatar-detail">
                <h6 class="mb-0 fs-11"><?=$model->viewStatus()['icon']?> <?=$model->viewTime()?> </h6> 
                <p class="text-muted mb-0 fs-11"><?=$model->locationOrg?->title ?? '-'?> </p>
            </div>
        </div>

    
