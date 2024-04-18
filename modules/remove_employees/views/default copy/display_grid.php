
<?php

use common\components\HrHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;
$assets = app\themes\assets\AppAsset::register($this);
?>


<div class="row staff-grid-row">
    <!-- Single Advisor-->
    <?php foreach($dataProvider->getModels() as $model):?>

        <div class="col-md-4 col-sm-6 col-12 col-lg-4 col-xl-3">
							<div class="profile-widget">
								<div class="profile-img">
									<a href="<?=Url::to(['view', 'id' => $model->id])?>" class="avatar"><img src="<?=$assets->baseUrl;?>/img/profiles/avatar-02.jpg" alt=""></a>
								</div>
								<div class="dropdown profile-action">
									<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
									<div class="dropdown-menu dropdown-menu-right">
										<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_employee"><i class="fa fa-pencil m-r-5"></i> Edit</a>
										<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_employee"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
									</div>
								</div>
								<h4 class="user-name m-t-10 mb-0 text-ellipsis">
								<?= Html::a($model->fullname(), ['view', 'id' => $model->id]) ?></h4>
								<div class="small text-muted">Web Designer</div>
							</div>
						</div>
    <?php endforeach;?>


</div>