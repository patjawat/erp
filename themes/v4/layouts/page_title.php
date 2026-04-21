<?php
use yii\bootstrap5\Breadcrumbs;
?>

<style>
   .text-primary-gradient {
      background: linear-gradient(90deg, #0866ad, #f1a57a);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
   }
   /* ปรับแต่งความสวยงามเพิ่มเติม */
   /* .page-title-box {
      background-color: #fff;
      border-bottom: 1px solid #e9ecef;
   } */
   .breadcrumb {
      margin-bottom: 5px;
      font-size: 13px;
   }
   /* จัดการปุ่มในมือถือให้ดูเรียบร้อย */
   @media (max-width: 991.98px) {
      .action-box {
         width: 100%;
         margin-top: 10px;
      }
      .action-box .d-flex {
         justify-content: flex-start !important;
         flex-wrap: wrap;
      }
   }
</style>

<div class="page-title-box">
   <div class="container-fluid" style="max-width: 1600px;">
      <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-center py-3 text-center text-lg-start">
         
         <div class="page-title-content w-100 w-lg-auto">
            <div class="page-title">
               <div class="d-flex justify-content-center justify-content-lg-start">
                  <?= Breadcrumbs::widget([
                     'encodeLabels' => false,
                     'homeLink' => [
                        'label' => '<i class="bi bi-house"></i> หน้าหลัก',
                        'url' => '/',
                     ],
                     'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                     'options' => ['class' => 'breadcrumb p-0 bg-transparent mb-1']
                  ]) ?>
               </div>
               
               <div class="title-text mt-2 mt-lg-0">
                  <?= isset($this->blocks['page-title']) ? $this->blocks['page-title'] : ''; ?>
               </div>
            </div>
         </div>

         <div class="action-box mt-3 mt-lg-0 w-100 w-lg-auto d-flex justify-content-center justify-content-lg-end">
            <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-lg-end">
               <?= isset($this->blocks['action']) ? $this->blocks['action'] : '' ?>
            </div>
         </div>

      </div>
   </div>
</div>