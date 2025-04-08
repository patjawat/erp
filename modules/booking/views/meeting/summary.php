 <!-- Summary Cards -->
 <div class="row mb-4">
     <div class="col-lg-3 col-md-6 mb-3">

         <div class="card hover-card-under">
             <div class="card-body">
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="h2 fw-semibold text-primary"><?=$searchModel->countStatus()?></span>
                     <div class="relative">
                         <i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>
                     </div>
                 </div>
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="text-primary fs-6 px-2"><i class="bi bi-exclamation-circle-fill"></i>
                         การจองทั้งหมด</span>
                 </div>
             </div>
         </div>

     </div>
     <div class="col-lg-3 col-md-6 mb-3">

         <div class="card hover-card-under">
             <div class="card-body">
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="h2 fw-semibold text-primary"><?=$searchModel->upComing()?></span>
                     <div class="relative">
                         <i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>
                     </div>
                 </div>
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="text-primary fs-6 px-2"><i class="bi bi-exclamation-circle-fill"></i>
                         การจองที่กำลังจะถึง</span>
                 </div>
             </div>
         </div>

     </div>
     <div class="col-lg-3 col-md-6 mb-3">

     <div class="card hover-card-under">
             <div class="card-body">
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="h2 fw-semibold text-primary"><?=$searchModel->countStatus('Pass')?></span>
                     <div class="relative">
                         <i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>
                     </div>
                 </div>
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="text-primary fs-6 px-2"><i class="bi bi-exclamation-circle-fill"></i>
                     การจองที่อนุมัติแล้ว</span>
                 </div>
             </div>
         </div>

     </div>
     <div class="col-lg-3 col-md-6 mb-3">

     <div class="card hover-card-under">
             <div class="card-body">
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="h2 fw-semibold text-primary"><?=$searchModel->countStatus('Pending')?></span>
                     <div class="relative">
                         <i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>
                     </div>
                 </div>
                 <div class="d-flex justify-content-between gap-1 mb-0">
                     <span class="text-primary fs-6 px-2"><i class="bi bi-exclamation-circle-fill"></i>
                     การจองที่รอการอนุมัติ</span>
                 </div>
             </div>
         </div>

     </div>
 </div>