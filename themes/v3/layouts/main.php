<?php
use app\models\Categorise;
use app\components\SiteHelper;

$layout = app\components\SiteHelper::getInfo()['layout'];

if($layout == 'horizontal'){
   echo $this->render('./theme-h/main',['content' => $content]);
}else {
echo $this->render('./theme-v/main',['content' => $content]);
}
?>