<?php
/** @var yii\web\View $this */
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\models\Province;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\depdrop\DepDrop;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;

$this->title = 'ขายทอดตลาด';
$this->params['breadcrumbs'][] = ['label' => 'บริหารพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<body>
<div class="row">
            <div class="col-12">

                <div class="card rounded-10">
                   
                    <div class="card-body">
                        <div class="d-flex justify-content-between">

                            <div class="card-title">รายการขายทอดตลาด</div>
                            
                        </div>
                   
                        <div class="table-responsive">
                            <table class="table text-nowrap">
                                <thead>
                                    <tr>
									<th>ลำดับ</th>
											<th>วันที่</th>
											<th>โครงการจำหน่าย</th>
                                            <th>มูลค่า</th>
                                            <th>สถานะ</th>
                                        <th class="text-center">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>

								<tr>
											<td>1</td>
							
											<td>11 ก.ย. 2566</td>
											<td>โครงการจำหน่ายครุภัณฑ์คอมพิวเตอร์ ครั้งที่ 1</td>
									
											<td>11,500</td>
											<td>
                                            
                                            <div class="dropdown"> <a href="javascript:void(0);"
                                                    class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown"
                                                    aria-expanded="false"> เรียบร้อย<i
                                                        class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
            
                                                    <li><a class="dropdown-item" href="javascript:void(0);">ดำเนินการ</a>
                                                    </li>
													<li><a class="dropdown-item" href="javascript:void(0);">เรียบร้อย</a>
                                                    </li>
                                                </ul>
                                            	</div>
                                        	</td>
                                         
										
											<td class="text-center align-middle">
													<div class="dropdown">
														<button type="button" class="btn p-0 dropdown-toggle hide-arrow"
															data-bs-toggle="dropdown" aria-expanded="false"><i
																class="bx bx-dots-vertical-rounded fw-bold"></i></button>
														<div class="dropdown-menu" style="">
															<??>
															<?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>รายละเอียด', ['#'], ['class' => 'dropdown-item'])?>

															<?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['#'], ['class' => 'dropdown-item'])?>
														</div>
													</div>
												</td>
										</tr>
										<tr>
											<td>2</td>
							
											<td>30 ก.ย. 2566</td>
											<td>โครงการจำหน่ายครุภัณฑ์คอมพิวเตอร์ ครั้งที่ 2</td>
									
											<td>65,500</td>
											<td>
                                            
                                            <div class="dropdown"> <a href="javascript:void(0);"
                                                    class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown"
                                                    aria-expanded="false"> ร้องขอ<i
                                                        class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
            
                                                    <li><a class="dropdown-item" href="javascript:void(0);">ดำเนินการ</a>
                                                    </li>
													<li><a class="dropdown-item" href="javascript:void(0);">เรียบร้อย</a>
                                                    </li>
                                                </ul>
                                            	</div>
                                        	</td>
                                         
										
											<td class="text-center align-middle">
													<div class="dropdown">
														<button type="button" class="btn p-0 dropdown-toggle hide-arrow"
															data-bs-toggle="dropdown" aria-expanded="false"><i
																class="bx bx-dots-vertical-rounded fw-bold"></i></button>
														<div class="dropdown-menu" style="">
															<??>
															<?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>รายละเอียด', ['#'], ['class' => 'dropdown-item'])?>

															<?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['#'], ['class' => 'dropdown-item'])?>
														</div>
													</div>
												</td>
										</tr>




                                  
                                 
                                  
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>

</body>          
