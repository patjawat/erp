<?php
use app\models\Categorise;
$model = Categorise::findOne(['name' => 'site']);
$layout = isset($model->data_json['layout']) ? $model->data_json['layout'] : '';

// if($layout == 1){
//    echo $this->render('./theme-h/main',['content' => $content]);
// }else {
      echo $this->render('./theme-v/main',['content' => $content]);
// }
?>