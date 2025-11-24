 <div class="">
     <p class="mb-0 fs-12" style="color:<?= isset($model->room->data_json['text_color']) ? $model->room->data_json['text_color'] : '' ?>">
         <?=$model->viewStatus()['icon']?><?=$model->viewTime()['full']?>
     </p>
     <p class="mb-0 fs-12" style="color:<?= isset($model->room->data_json['text_color']) ? $model->room->data_json['text_color'] : '' ?>">
         <?=$model->room?->title ?? '-'?>
     </p>

 </div>