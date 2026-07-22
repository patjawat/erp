<?php
use yii\bootstrap5\Breadcrumbs;
// $this->title = 'ทะเบียนประวัติ';
// $this->params['breadcrumbs'][] = $this->title;
?>
<?php if(isset($size)):?>

<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-12 col-xl-12">
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

        </div>
    </div>
</div>
</div>

<?php else:?>
<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-4">
                <div class="page-title">
                    <div class="d-flex align-items-center">
                        <?php echo $this->blocks['icon'] ?? ''?>
                        <div class="avatar-detai ms-2">
                            <h5 class="mb-1 text-white">
                                <?=isset($this->blocks['page-title']) ? $this->blocks['page-title'] : 'ERP';?></a>
                            </h5>
                            <p class="text-white mb-0 fs-13"><?php echo $this->blocks['sub-title'] ?? ''?></p>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-7 col-xl-8">
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
<?php endif;?>