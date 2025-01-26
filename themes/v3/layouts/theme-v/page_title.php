<?php
use yii\bootstrap5\Breadcrumbs;
// $this->title = 'ทะเบียนประวัติ';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-6">
                <div class="page-title">
                    <h5 class="mb-1 text-white">
                        <?=isset($this->blocks['page-title']) ? $this->blocks['page-title'] : 'ERP';?> </h5>
                        <ol class="breadcrumb mb-3 mb-md-0">
                        <?php if(isset($this->blocks['sub-title'])):?>
                        <li class="breadcrumb-item mx-4 active"><?=$this->blocks['sub-title']?></li>
                        <?php endif;?>
                    </ol>
                </div>
            </div>
            <div class="col-sm-7 col-xl-6">
				<div class="d-flex justify-content-sm-end">

					<?php if(isset($this->blocks['page-action'])):?>
                        <?=$this->blocks['page-action']?>
						<?php else:?>
							<?= Breadcrumbs::widget([
                                 'encodeLabels' => false,
                                 'homeLink' => [
                                    'label' => '<i class="bi bi-house"></i> หน้าหลัก',
                                    'url' => '/',
                                ],
								'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
								]) ?>
                <?php endif;?>
			</div>
            </div>
        </div>
    </div>
</div>
