<?php
use yii\bootstrap5\Breadcrumbs;
?>


    <div class="page-title-box">
               <div class="container-fluid mt-4">
                  <div class="d-flex justify-content-between">
                     <div>
                        <div class="page-title"  data-aos="fade-left">
                           <h3 class="mb-1 fw-light text-primary"><?=isset($this->blocks['page-title']) ? $this->blocks['page-title'] : 'ERP';?></h3>
                          
                        </div>
                     </div>
                     <div>
                       <?= isset($this->blocks['action']) ? $this->blocks['action'] : Breadcrumbs::widget([
                                 'encodeLabels' => false,
                                 'homeLink' => [
                                    'label' => '<i class="bi bi-house"></i> หน้าหลัก',
                                    'url' => '/',
                                ],
								'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
								]) ?>
                     </div>
                  </div>
               </div>
            </div>

